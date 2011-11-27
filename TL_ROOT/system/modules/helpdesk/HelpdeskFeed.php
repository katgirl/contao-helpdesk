<?php 
/**
 * TYPOlight Helpdesk :: Class HelpdeskFeed
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */
 
define('TL_MODE', 'FE');
require_once('../../initialize.php');
require_once('HelpdeskConstants.php');

class HelpdeskFeed extends Frontend
{
	protected $settings;
	private $cat;
	private $role;
	
	public function __construct()
	{
		// Load user object before calling the parent constructor
		$this->import('FrontendUser', 'User');
		parent::__construct();

		// Check whether a user is logged in
		define('BE_USER_LOGGED_IN', $this->getLoginStatus('BE_USER_AUTH'));
		define('FE_USER_LOGGED_IN', $this->getLoginStatus('FE_USER_AUTH'));

		// HOOK: trigger recall extension
		if (!FE_USER_LOGGED_IN && $this->Input->cookie('tl_recall_fe') && in_array('recall', $this->Config->getActiveModules()))
		{
			Recall::frontend($this);
		}
	} // __construct

	public function run()
	{
		$this->import('HelpdeskSettings', 'settings');
		if (!$this->settings->feeds || !$this->settings->feedmax) return;
		$this->backend = false;
		$this->User->authenticate();
			
		// get categories. null = all categories
		$catids = null;
		$single = false;
		$id = $this->Input->get('id');
		if ($id) {
			$catids = array();
			foreach (explode(',',$id) as $i) {
				$i = intval($i);
				if ($i && !in_array($i,$catids)) $catids[] = $i;
			} // foreach
			$single = count($catids)==1;
		} // if
		
		// load data
		$q = $this->Database->execute(
			"\n select" .
				HELPDESK_TCKCOLS.','.
				HELPDESK_MSGCOLS.
			"\n from `tl_helpdesk_messages` as `msg`" .
			"\n inner join `tl_helpdesk_tickets` as `tck`" .
				" on `tck`.`id`=`msg`.`pid`" .
			"\n order by `msg_id` desc" 
		);
		$msgcnt = 0;
		$parser = null;
		$tickets = array();
		$this->cat = $this->role = array();
		while ($q->next() && $msgcnt < $this->settings->feedmax) {

			// skip unwanted categories
			if (is_array($catids) && !in_array($q->tck_pid, $catids)) continue;
			
			if (!in_array($q->tck_id,$tickets) && $this->isAuthorized($q)) {
				$cat = $this->cat[$q->tck_pid];
				if (!$msgcnt++) {
					// send header
					$lng = $GLOBALS['TL_LANGUAGE'];
					if (!strlen($lng)) $lng = 'en';
					header("Content-Type: application/rss+xml");
					$title = $single ? $cat->cat_title : $this->settings->feedtitle;
					$descr = $single ? $cat->cat_description : $this->settings->feeddescription;
					if (strlen($descr)>200) $descr = substr($desr,0,179).'...';
					echo
						'<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
						'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n" .
						"<channel>\n" .
						"\t<title>" . $this->filter($title) . "</title>\n" .
						"\t<description>" . $this->filter($descr) . "</description>\n" .
						"\t<link>" . $this->Environment->base . "</link>\n".
						"\t<language>" . $lng . "</language>\n".
						"\t<pubDate>" . gmdate('r',$q->msg_tstamp) . "</pubDate>\n".
						"\t<generator>TYPOlight Helpdesk</generator>\n";
				} // if
				$fe_url = trim($cat->cat_notify_fe_url);
				$link = ($fe_url!='') ?	$fe_url.'/message/'.$q->msg_id.$GLOBALS['TL_CONFIG']['urlSuffix'] : $this->Environment->base;
				if (is_null($parser)) $parser = new HelpdeskBbcodeParser($this);
				$message = $this->filter($parser->parse(trim($q->msg_message)."\n"));
				echo
					"\t<item>\n" .
					"\t\t<title>" . $this->filter($q->tck_subject) . "</title>\n" .
					"\t\t<link>" . $link . "</link>\n" .
					"\t\t<description>" . $message . "</description>\n" .
					"\t\t<pubDate>" . gmdate('r',$q->msg_tstamp) . "</pubDate>\n" .
					"\t\t<guid>" . $link . "</guid>\n" .
					"\t\t<category>" . $this->filter(str_replace('/',',',$cat->cat_title)) . "</category>\n" .
					"\t</item>\n";
				$tickets[] = $q->tck_id;
			} // if
		} // while
		if ($msgcnt) {
			// send footer
			echo			
				"\t". '<atom:link href="' . $this->Environment->base . $this->Environment->request . '" rel="self" type="application/rss+xml" />'. "\n" .
				"</channel>\n".
				"</rss>\n";
		} // if
	} // run

	/**
	 * Create img html (dummy)
	 */
	public function createImage($file, $alt='', $attributes='')
	{
		return '';
	} // createImage

	/**
	 * Filter texts
	 */
	private function filter($text)
	{
		$t = trim(preg_replace('#\s+#', ' ', strip_tags($text)));
		if ($this->settings->feedlimit>3 && mb_strlen($t)>$this->settings->feedlimit)
			$t = mb_substr($t,0,$this->settings->feedlimit-3).'...';
		return htmlspecialchars($t, ENT_QUOTES);
	} // filter

	/**
	 * Check if authorized to see this message
	 */
	private function isAuthorized(&$q)
	{
		$user = &$this->User;
		
		// get role
		if (isset($this->role[$q->tck_pid])) {
			$role = $this->role[$q->tck_pid];
			$cat = $this->cat[$q->tck_pid];
		} else {
			// load category
			$qc = $this->Database
				->prepare(
					"\n select" .
						HELPDESK_CATCOLS .
					"\n from `tl_helpdesk_categories` as `cat`" .
					"\n where `cat`.`id`=?" 
				  )
				->execute($q->tck_pid);
			if (!$qc->next()) return false; //should never happen though
			$cat = $this->cat[$q->tck_pid] = (object)$qc->row();
			
			// get role
			$role = HELPDESK_GUEST;
			if (is_array($user->groups)) {
				if (Helpdesk::matchGroupsP($cat->cat_fe_supporters, $user->groups))
					$role = HELPDESK_SUPPORTER;
				else
					if (Helpdesk::matchGroupsP($cat->cat_fe_clients, $user->groups))
						$role = HELPDESK_CLIENT;
			} // if
			$this->role[$q->tck_pid] = $role;
		} // if
		if (!intval($cat->cat_feed)) return false;

		// evaluate role
		if ($role >= HELPDESK_SUPPORTER) return true;
		if (!intval($cat->cat_published) || !intval($q->tck_published) || !intval($q->msg_published)) return false;
		$access = intval($cat->cat_access);
		if ($role==HELPDESK_GUEST && 
			($access==HELPDESK_PRIVATE_SUPPORT || $access==HELPDESK_SHARED_SUPPORT || $access==HELPDESK_PROTECTED_FORUM))
				return false;
		if ($access==HELPDESK_PRIVATE_SUPPORT &&
			(intval($q->tck_client_be) || $q->tck_client!=$user->username))
			return false;
		return true;
	} // isAuthorized
	
} // class HelpdeskFrontendDownload

/**
 * Instantiate controller
 */
$objFeed = new HelpdeskFeed();
$objFeed->run();

?>
