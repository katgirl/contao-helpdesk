<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Class HelpdeskNotify
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

define('TL_MODE', 'BE');
require_once('../../initialize.php');
require_once('HelpdeskConstants.php');

class HelpdeskNotify extends Backend
{
	protected $settings;
	private $parser;
	private $loglevel;
	private $h2t_linkcount;
	private $h2t_linklist;
	private $h2t_wrap = 78;
	
	/**
	 * Initialize the controller
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('HelpdeskSettings', 'settings');
		$this->loglevel = 0;
	} // __construct

	/**
	 * Helpdesk logging
	 */
	private function hlog($verbose, $message, $inc=0)
	{
		if ($verbose <= $this->settings->logging) {
			if ($inc < 0) {
				$this->loglevel += $inc;
				if ($this->loglevel < 0) $this->loglevel = 0;
			} // if
			$lines = split("\n", str_replace("\r", "", $message));
			foreach ($lines as $line) 
				error_log(
					sprintf("[%s] %s%s\n", date('Y-m-d H:i:s'), str_repeat('  ', $this->loglevel), $line), 
					3, 
					TL_ROOT . '/system/logs/HelpdeskNotify.log'
				);
			if ($inc > 0) $this->loglevel += $inc;
		} // if
	} // hlog

	/**
	 * Run controller and parse the template
	 */
	public function run()
	{
		global  $cronJob;

		$this->hlog(HELPDESK_DEBUGLOG, '+run', 1);
		$this->hlog(HELPDESK_BRIEFLOG, 'Notification started.');
		
		$qrec = $this->Database->prepare(
			"\n select " .
				HELPDESK_NTFCOLS.','.
				HELPDESK_MSGCOLS.','.
				HELPDESK_TCKCOLS.','.
				HELPDESK_CATCOLS.
			"\n from `tl_helpdesk_notifys` as `ntf`" .
				"\n left join `tl_helpdesk_messages` as `msg` on `ntf`.`pid`=`msg`.`id`" .
				"\n left join `tl_helpdesk_tickets` as `tck` on `msg`.`pid`=`tck`.`id`" .
				"\n left join `tl_helpdesk_categories` as `cat` on `tck`.`pid`=`cat`.`id`" .
			"\n order by `ntf`.`id`"
		)->execute();
		while ($qrec->next()) {
			$this->hlog(HELPDESK_BRIEFLOG, "Category=$qrec->cat_id ticket=$qrec->tck_id message=$qrec->msg_id");
			if (time() >= $cronJob['endtime']) {
				$cronJob['completed'] = false;
				$this->hlog(HELPDESK_BRIEFLOG, 'Suspended because of time limit.');
				$this->hlog(HELPDESK_DEBUGLOG, '-run', -1);
				return;
			} // if
			$delete = false;
			$process = true;
			if (!intval($qrec->cat_notify)) {
				$this->hlog(HELPDESK_BRIEFLOG, 'Dont process because category notification disabled.');
				$process = false;
			} else
				if (!intval($qrec->cat_published)) {
					$this->hlog(HELPDESK_BRIEFLOG, 'Dont process because category is not published.');
					$process = false;
				} else
					if (!intval($qrec->tck_published)) {
						$this->hlog(HELPDESK_BRIEFLOG, 'Dont process because ticket is not published.');
						$process = false;
					} else
						if (!intval($qrec->msg_published)) {
							$this->hlog(HELPDESK_BRIEFLOG, 'Dont process because message is not published.');
							$process = false;
						} // if
			if ($process) {
				if (!$this->notifyMessage($qrec)) {
					$cronJob['completed'] = false;
					$this->hlog(HELPDESK_BRIEFLOG, 'Suspended because of time limit.');
					$this->hlog(HELPDESK_DEBUGLOG, '-run', -1);
					return;
				} // if
				$this->hlog(HELPDESK_DEBUGLOG, 'Delete because processed.');
				$delete = true;
			} else {
				if (!intval($qrec->cat_notify)) {
					$this->hlog(HELPDESK_DEBUGLOG, 'Delete because category notification disabled.');
					$delete = true;
				} else
					if (!$qrec->tck_id) {
						$this->hlog(HELPDESK_DEBUGLOG, 'Delete because ticket does not exist.');
						$delete = true;
					} else
						if (!$qrec->msg_id) {
							$this->hlog(HELPDESK_DEBUGLOG, 'Delete because message does not exist.');
							$delete = true;
						} // if
			} // if
			if ($delete) {
				$this->Database->prepare(
					"delete from `tl_helpdesk_notifys` where `id`=?"
				)->execute($qrec->ntf_id);
				$this->Database->prepare(
					"delete from `tl_helpdesk_notifieds` where `pid`=?"
				)->execute($qrec->ntf_id);
			} // if
		} // while
		
		$this->hlog(HELPDESK_DEBUGLOG, '-run', -1);
	} // run
	
	/**
	 * Send out notifications for a published message
	 */
	private function notifyMessage(&$qrec)
	{
		global  $cronJob;
		
		$this->hlog(HELPDESK_DEBUGLOG, '+notifyMessage', 1);
		
		$poster 	= $qrec->msg_poster;
		$poster_cd	= intval($qrec->msg_poster_cd);
		$poster_be	= $poster_cd==1 || $poster_cd==3;
		$reply		= intval($qrec->msg_reply) > 0;
		$by_email	= intval($qrec->msg_by_email) > 0;
		$be_url		= trim($qrec->cat_notify_be_url);
		$fe_url		= trim($qrec->cat_notify_fe_url);

		// get all ticket repliers
		$q = $this->Database->prepare(
			"select `reply`, `poster`, `poster_cd` from `tl_helpdesk_messages` where `pid`=?"
		)->execute($qrec->tck_id);
		$post_be = array();
		$post_fe = array();
		$owner = $owner_be = null;
		while ($q->next()) {
			$be = intval($q->poster_cd)==1 || intval($q->poster_cd)==3;
			if (!intval($q->reply)) {
				$owner = $q->poster;
				$owner_be = $be;
				if ($be)
					$this->hlog(HELPDESK_DEBUGLOG, "Owner: Backend user $owner");
				else
					$this->hlog(HELPDESK_DEBUGLOG, "Owner: Frontend user $owner");
			} // if
			if ($be) {
				if (!in_array($q->poster, $post_be)) {
					$post_be[] = $q->poster;
					$this->hlog(HELPDESK_DEBUGLOG, "Backend contributor: $q->poster");
				} // if
			} else {
				if (!in_array($q->poster, $post_fe)) {
					$post_fe[] = $q->poster;
					$this->hlog(HELPDESK_DEBUGLOG, "Frontend contributor: $q->poster");
				} // if
			} // if
		} // while

		// get allready notified addresses
		$notifieds = array();
		$q = $this->Database->prepare(
			"select `email` from `tl_helpdesk_notifieds` where `pid`=?"
		)->execute($qrec->ntf_id);
		while ($q->next()) {
			$notifieds[] = trim($q->email);
			$this->hlog(HELPDESK_DEBUGLOG, "Notified allready earlier: $q->email");
		} // while	
		$q = null;

		// get backend users
		$be_users = array();
		$be_sups = Helpdesk::unpackArray($qrec->cat_be_supporters);
		$be_clis = Helpdesk::unpackArray($qrec->cat_be_clients);
		if (strlen($be_url)) {
			$q = $this->Database->prepare(
				"\n select `username`, `email`, `admin`, `groups`, `helpdesk_subscriptions`" .
				"\n from `tl_user`" .
				"\n where `disable`!='1'" .
				"\n order by `id`"
			)->execute();
			while ($q->next()) {
				$logName = "Backend user $q->username ($q->email):";
				$email = strtolower(trim($q->email));
				
				if ($email=='') {
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Has no email address.");
					continue;
				} // if
				if (in_array($email, $notifieds)) {
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Allready notified earlier.");
					continue;
				} // if
				if (in_array($email, $be_users)) {
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Duplicate email address.");
					continue;
				} // if
				if ($poster_be && $poster==$q->username && !$by_email) { 
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Is poster himself, and not posted by email.");
					continue;
				} // if
				
				// check category authorization
				if (!intval($q->admin)) {
					$g = Helpdesk::unpackArray($q->groups);
					if (intval($qrec->cat_access)!=HELPDESK_PUBLIC_FORUM &&
						intval($qrec->cat_access)!=HELPDESK_PUBLIC_SUPPORT &&
						!Helpdesk::matchGroups($g, $be_sups) && 
						!Helpdesk::matchGroups($g, $be_clis)) {
						$this->hlog(HELPDESK_DEBUGLOG, "$logName Is not authorized for this category.");
						continue;
					} // if
				} // if

				// check subscription
				$subs = Helpdesk::unpackArray($q->helpdesk_subscriptions);
				$take = false;
				if (in_array($qrec->cat_id, $subs)) {
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Subscribed to the category.");
					$take = true;
				} else
					if (in_array(999998, $subs) && $owner_be && $q->username==$owner) {
						$this->hlog(HELPDESK_DEBUGLOG, "$logName Subscribed to all own tickets/threads.");
						$take = true;
					} else
						if (in_array(999999, $subs) && in_array($q->username,$post_be)) {
							$this->hlog(HELPDESK_DEBUGLOG, "$logName Subscribed to all contributed tickets/threads.");
							$take = true;
						} // if
				if ($take)
					$be_users[] = $email;
				else
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Is not subscribed.");
			} // while
		} // if
		
		// get frontend members
		$fe_members = array();
		$fe_sups = Helpdesk::unpackArray($qrec->cat_fe_supporters);
		$fe_clis = Helpdesk::unpackArray($qrec->cat_fe_clients);
		if (strlen($fe_url)) {
			$q = $this->Database->prepare(
				"\n select `username`, `email`, `groups`, `helpdesk_subscriptions`" .
				"\n from `tl_member`" .
				"\n where `login`='1' and `disable`!='1'" .
				"\n order by `id`"
			)->execute();
			while ($q->next()) {
				$logName = "Frontend member $q->username ($q->email):";
				$email = strtolower(trim($q->email));
				
				if ($email=='') {
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Has no email address.");
					continue;
				} // if
				if (in_array($email, $notifieds)) {
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Allready notified earlier.");
					continue;
				} // if
				if (in_array($email, $be_users) || in_array($email, $fe_members)) {
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Duplicate email address.");
					continue;
				} // if
				if (!$poster_be && $poster==$q->username && !$by_email) { 
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Is poster himself, and not posted by email.");
					continue;
				} // if
				
				// check category authorization
				$g = Helpdesk::unpackArray($q->groups);
				if (intval($qrec->cat_access)!=HELPDESK_PUBLIC_FORUM &&
					intval($qrec->cat_access)!=HELPDESK_PUBLIC_SUPPORT &&	
					!Helpdesk::matchGroups($g, $fe_sups) && 
					!Helpdesk::matchGroups($g, $fe_clis)) {
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Is not authorized for this category.");
					continue;
				} // if

				// check subscription
				$subs = Helpdesk::unpackArray($q->helpdesk_subscriptions);
				$take = false;
				if (in_array($qrec->cat_id, $subs)) {
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Subscribed to the category.");
					$take = true;
				} else
					if (in_array(999998, $subs) && !$owner_be && $q->username==$owner) {
						$this->hlog(HELPDESK_DEBUGLOG, "$logName Subscribed to all own tickets/threads.");
						$take = true;
					} else
						if (in_array(999999, $subs) && in_array($q->username,$post_fe)) {
							$this->hlog(HELPDESK_DEBUGLOG, "$logName Subscribed to all contributed tickets/threads.");
							$take = true;
						} // if
				if ($take)
					$fe_members[] = $email;
				else
					$this->hlog(HELPDESK_DEBUGLOG, "$logName Is not subscribed.");
			} // while
		} // if

		if (count($be_users) || count($fe_members)) {
			// create subject
			$sub = str_replace('[[subject]]', $qrec->tck_subject, $reply ? $qrec->cat_notify_replysubj : $qrec->cat_notify_newsubj);
			$sub = str_replace('[[poster]]', $poster, $sub);
			$sub = str_replace('[[replytag]]', '[replyto#'.$qrec->tck_id.']', $sub);

			// create content
			if (is_null($this->parser)) $this->parser = new HelpdeskBbcodeParser(new HelpdeskTheme());
			$cont = str_replace('[[subject]]', $qrec->tck_subject, $reply ? $qrec->cat_notify_replytext : $qrec->cat_notify_newtext);
			$cont = str_replace('[[poster]]', $poster, $cont);
			$cont = str_replace(
				'[[message]]', 
				'<div class="helpdesk-message">'.$this->parser->parse($qrec->msg_message).'</div>', 
				$cont
			);

			// notify backend users
			if (count($be_users)) {
				$this->hlog(HELPDESK_DEBUGLOG, "Notify backend users.");
				if (!$this->notify($qrec, $be_users, $be_url.'&message=' .$qrec->msg_id, $sub, $cont, true)) {
					$this->hlog(HELPDESK_DEBUGLOG, '-notifyMessage', -1);
					return false;
				} // if
			} // if
			
			// notify frontend members
			if (count($fe_members)) {
				$this->hlog(HELPDESK_DEBUGLOG, "Notify frontend members.");
				if (!$this->notify($qrec, $fe_members, $fe_url.'/message/'.$qrec->msg_id.$GLOBALS['TL_CONFIG']['urlSuffix'], $sub, $cont, false)) {
					$this->hlog(HELPDESK_DEBUGLOG, '-notifyMessage', -1);
					return false;
				} // if
			} // if
		} // if
		$this->hlog(HELPDESK_DEBUGLOG, '-notifyMessage', -1);
		return true;
	} // notifyMessage

	/**
	 * Notification subroutine
	 */
	private function notify(&$qrec, &$recipients, $url, $sub, $cont, $backend)
	{
		global  $cronJob;

		$this->hlog(HELPDESK_DEBUGLOG, '+notify', 1);
		// create link
		$link = '<a href="' . $url . '">' . $url . '</a>';

		// create the email
		$objEmail = new Email();
		$objEmail->fromName = $qrec->cat_notify_name;
		$objEmail->from = $qrec->cat_notify_sender;
		$objEmail->subject = html_entity_decode($sub, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);

		// body
		$cont = str_replace('[[link]]', $link, $cont);
		if (intval($qrec->cat_notify_astext)) {
			// send as text
			$objEmail->text = $this->html2text($this->convertRelativeLinks($cont));
		} else {
			// Send as HTML
			$nl = "\n";
			$objEmail->html = $this->convertRelativeLinks(
				'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">'.$nl.
				'<html>'.$nl.
				'<head>'.$nl.
				'<meta http-equiv="Content-Type" content="text/html; charset='.$GLOBALS['TL_CONFIG']['characterSet'].'">'.$nl.
				'<META http-equiv="Content-Style-Type" content="text/css">'.$nl.
				'<meta name="Generator" content="Contao Helpdesk">'.$nl.
				'<link rel="stylesheet" type="text/css" href="system/themes/'.$this->getTheme().'/basic.css" />'.$nl.
				'<link rel="stylesheet" type="text/css" href="'.HelpdeskTheme::file('message.css').'" />'.$nl.
				'<title>'.$sub.'</title>'.$nl.
				'</head>'.$nl.
				'<body>'.$nl.
				$cont.$nl.
				'</body>'.$nl.
				'</html>'.$nl
			);
			$objEmail->imageDir = TL_ROOT . '/';
		} // if
		
		if (intval($qrec->cat_notify_atch)) {
			// attachments
			$atchnames = array(
				1 => $qrec->msg_atch1name,
				2 => $qrec->msg_atch2name,
				3 => $qrec->msg_atch3name,
				4 => $qrec->msg_atch4name,
				5 => $qrec->msg_atch5name
			);
			$path = null;
			foreach ($atchnames as $id => $name)
				if (strlen($name)) {
					if (!$path) {
						// get message attachment path
						$parts = explode('/',str_replace('\\', '/', $qrec->cat_atch_dir));
						$path = TL_ROOT.'/';
						foreach ($parts as $part) if (strlen($part)) $path .= $part.'/';
						$path .= $qrec->msg_id.'.';
					} // if
					if (file_exists($path.$id))
						$objEmail->attachFileFromString(
							file_get_contents($path.$id), 
							$name, 
							Helpdesk::getMimeType($name)
						);
				} // if
		} // if

		// go
		$complete = true;
		$cnt = 0;
		foreach ($recipients as $recipient) {
			if (time() >= $cronJob['endtime']) {
				$this->hlog(HELPDESK_DEBUGLOG, 'Time limit reached.');
				$complete = false;
				break;
			} // if
			$this->hlog(HELPDESK_DETAILEDLOG, "Notifying $recipient");
			$objEmail->sendTo($recipient);
			$this->Database->prepare("INSERT INTO `tl_helpdesk_notifieds` %s")
				->set(array('pid'=>$qrec->ntf_id, 'email'=>$recipient))
				->execute();
			$cnt++;
		} // foreach
		$whos = $backend ? 'backend user(s)' : 'frontend member(s)';
		$this->hlog(HELPDESK_BRIEFLOG, "Notified to $cnt $whos.");
		if ($cronJob['logging'])
			$this->log(
				"Notified <em>$sub</em> in <em>$qrec->cat_title</em> to $cnt $whos.",
				'HelpdeskNotify notify()', TL_GENERAL);
		$this->hlog(HELPDESK_DEBUGLOG, '-notify', -1);
		return $complete;
	} // notify

	/**
	 * Convert relative links
	 */
	private function convertRelativeLinks($text)
	{
		$links = array();
		preg_match_all('/href="([^"]+)"/i', $text, $links);
		foreach ($links[1] as $link) {
			if (!preg_match('@^(http://|https://|ftp:|mailto:)@i', $link)) {
				if ($link == '/')
					$text = str_replace('href="/"', 'href="' . $this->Environment->base . '"', $text);
				else
					$text = str_replace($link, $this->Environment->base . $link, $text);
			} // if
		} // foreach
		preg_match_all('/src="([^"]+)"/i', $text, $links);
		foreach ($links[1] as $link) {
			if (!preg_match('@^(http://|https://)@i', $link)) {
				if ($link == '/')
					$text = str_replace('src="/"', 'src="' . $this->Environment->base . '"', $text);
				else
					$text = str_replace($link, $this->Environment->base . $link, $text);
			} // if
		} // foreach
		return $text;
	} // convertRelativeLinks

	/**
	 * Convert html to text
	 */
	private function html2text($html)
	{
		// Variables used for building the link list
		$this->h2t_linkcount = 0;
		$this->h2t_linklist = '';
		
        // Run main search-and-replace
        $text = preg_replace(
			array(
				'/(\&lt;|<)\?php/i',					// illegal
				'/(\&lt;|<)\?xml/i',					// illegal
				'/(\&lt;|<)\?/i',						// illegal				
				'/\?(\&gt;|>)/i',						// illegal				
				"/\r/",									// Non-legal carriage return
				"/[\t]+/",								// Tabs
				'/[ ]{2,}/',							// Runs of spaces, pre-handling
				'/<h[123][^>]*>(.*?)<\/h[123]>/ie',		// H1 - H3
				'/<h[456][^>]*>(.*?)<\/h[456]>/ie',		// H4 - H6
				'/<li[^>]*><div[^>]*>/i', 				// <li><div> (GeSHi)
				'/<\/div><\/li>/i', 					// </div></li> (GeSHi)
				'/(<div[^>]*>|<\/div>)/i',				// <div> and </div>
				'/(<p[^>]*>|<\/p>)/i',					// <p> and </p>
				'/<br[^>]*>/i',							// <br>
				'/<b[^>]*>(.*?)<\/b>/ie',				// <b>
				'/<strong[^>]*>(.*?)<\/strong>/ie',		// <strong>
				'/<i[^>]*>(.*?)<\/i>/i',				// <i>
				'/<em[^>]*>(.*?)<\/em>/i',				// <em>
				'/(<ul[^>]*>|<\/ul>)/i',				// <ul> and </ul>
				'/(<ol[^>]*>|<\/ol>)/i',				// <ol> and </ol>
				'/<li[^>]*>/i',							// <li>
				'/<a [^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/ie',	// <a href="">
				'/<hr[^>]*>/ie',						// <hr>
				'/(<table[^>]*>|<\/table>)/i',			// <table> and </table>
				'/(<\/tr>)/i',							// </tr>
				'/<td[^>]*>(.*?)<\/td>/i',				// <td> and </td>
				'/<th[^>]*>(.*?)<\/th>/ie',				// <th> and </th>
				'/[ ]{2,}/'								// Runs of spaces, post-handling
		    ),
			array(
				'',										// illegal
				'',										// illegal
				'',										// illegal
				'',										// illegal
				'',										// Non-legal carriage return
				' ',									// Tabs
				' ',									// Runs of spaces, pre-handling
				"strtoupper(\"\n\n\\1\n\n\")",			// H1 - H3
				"ucwords(\"\n\n\\1\n\n\")",				// H4 - H6
				"\n", 									// <li><div> and </div></li> (GeSHi)
				'',					 					// </div></li> (GeSHi)
				"\n",									// <div> and </div>
				"\n",									// <p> and </p>
				"\n",									// <br>
				'strtoupper("\\1")',					// <b>
				'strtoupper("\\1")',					// <strong>
				'_\\1_',								// <i>
				'_\\1_',								// <em>
				"\n\n",									// <ul> and </ul>
				"\n\n",									// <ol> and </ol>
				"\n\t* ",								// <li>
				'$this->h2t_addlink("\\1", "\\2")',		// <a href="">
				'"\n".str_pad("",$this->h2t_wrap,"-")."\n"',	// <hr>
				"\n\n",									// <table> and </table>
				"\n",									// </tr>
				"\t\\1",								// <td> and </td>
				'strtoupper("\t\\1")',					// <th> and </th>
				' '										// Runs of spaces, post-handling
		    ),
			trim(stripslashes($html))
		);

        // Strip any other HTML tags
		$text = strip_tags($text);

        // Add link list
        if ($this->h2t_linkcount)
            $text .= "\n\n---\n" . $this->h2t_linklist;

        // Bring down number of empty lines to 2 max
        $text = preg_replace("/\n\s+\n/", "\n\n", $text);
        $text = preg_replace("/[\n]{3,}/", "\n\n", $text);

		// decode entities
		$text = html_entity_decode($text, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
		
        // Wrap the text to a convenient readable format
		return wordwrap($text, $this->h2t_wrap);
	} // html2text
	
	/**
	 * Add a link to converted text
	 */
    private function h2t_addlink($link, $text)
    {
		if ($link!=$text &&
				(substr($link, 0, 7) == 'http://'
				 || substr($link, 0, 8) == 'https://'
				 || substr($link, 0, 7) == 'mailto:') ) {
			$this->h2t_linkcount++;
			$ref = '[' . $this->h2t_linkcount . ']';
			$this->h2t_linklist .= "$ref $link\n";
			return $text . ' ' . $ref;
		} // if
		return $text;
    } // h2t_addlink
	
} // class HelpdeskNotify

/**
 * Instantiate controller
 */
$objNotify = new HelpdeskNotify();
$objNotify->run();

?>
