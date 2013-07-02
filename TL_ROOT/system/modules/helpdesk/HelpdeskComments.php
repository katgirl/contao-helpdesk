<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Class HelpdeskComments
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

require_once('HelpdeskConstants.php');

class HelpdeskComments extends ContentElement
{

	protected $strTemplate = 'helpdesk_comments';
	
	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### FORUM/HELPDESK COMMENTS (' . $this->helpdesk_reference . ') ###';
			return $objTemplate->parse();
		} // if
		return parent::generate();
	} // generate
	
	protected function compile()
	{
		// $this->id = tl_content.id
		// $this->pid = tl_article.id
		$this->import('HelpdeskSettings', 'settings');
		$db = &$this->Database;
		$hd = new Helpdesk($this, '', false);

		$this->loadLanguageFile('tl_helpdesk_comments');
		$this->Template->ticketLink = null;
		$this->Template->replyCount = 0;
		
		
		// find the item id
		$item_type = $this->helpdesk_reference;
		$item_id = -1;
		$item_title = '';
		$item_author = '';
		switch ($item_type) {
			case 'page':
				$s = 
					"select `p`.`id` as `id`, `p`.`title` as `title`, `u`.`username` as `author`" .
					"\n from `tl_article` as `a`" .
					"\n inner join `tl_page` as `p` on `a`.`pid`=`p`.`id`".
					"\n inner join `tl_user` as `u` on `a`.`author`=`u`.`id`".
					"\n where `a`.`id`=?";
				$q = $db->prepare($s)->execute(intval($this->pid));
				if ($q->next()) {
					$item_id = $q->id;
					$item_title = $q->title;
					$item_author = $q->author;
				} // if
				break;
				
			case 'article':
				$s = 
					"select `a`.`id` as `id`, `a`.`title` as `title`, `u`.`username` as `author`" .
					"\n from `tl_article` as `a`" .
					"\n inner join `tl_user` as `u` on `a`.`author`=`u`.`id`".
					"\n where `a`.`id`=?";
				$q = $db->prepare($s)->execute(intval($this->pid));
				if ($q->next()) {
					$item_id = $q->id;
					$item_title = $q->title;
					$item_author = $q->author;
				} // if
				break;

			case 'news':
				// get news archives in this article
				$all_archs = array();
				$s =
					"select `md`.`news_archives` as `news_archives`" .
					"\n from `tl_content` as `ct`" .
					"\n inner join `tl_module` as `md` on `ct`.`module`=`md`.`id` and `md`.`type`='newsreader'" .
					"\n where `ct`.`pid`=?" .
					"\n and `ct`.`type`='module'";
				$q = $db->prepare($s)->execute(intval($this->pid));
				while ($q->next()) {
					$archs = deserialize($q->news_archives, true);
					$all_archs = array_merge($all_archs, $archs);
				} // while
				$all_archs = array_unique($all_archs);
				if (count($all_archs) > 0)
					$all_archs = $this->sortOutProtected('tl_news_archive', $all_archs);
				
				// find the news id
				if (count($all_archs) > 0) {
					$s =
						"select `n`.`id` as `id`, `n`.`headline` as `title`, `u`.`username` as `author`" . 
						"\n from `tl_news` as `n`" . 
						"\n inner join `tl_user` as `u` on `n`.`author`=`u`.`id`".
						"\n where `n`.`pid` in  (" . implode(',', $all_archs) . ")" . 
						"\n and (`n`.`id`=? or `n`.`alias`=?)";
					if (!BE_USER_LOGGED_IN) 
						$s .= 	
							"\n and (`n`.`start`='' or `n`.`start`<?)" .
							"\n and (`n`.`stop`='' or `n`.`stop`>?)".
							"\n and `n`.`published`=1";
					$i = $this->Input->get('items');
					$q = $db->prepare($s)->limit(1);
					if (BE_USER_LOGGED_IN)
						$q = $q->execute((is_numeric($i) ? $i : 0), $i);
					else {
						$t = time();
						$q = $q->execute((is_numeric($i) ? $i : 0), $i, $t, $t);
					} // if
					if ($q->next()) {
						$item_id = $q->id;
						$item_title = $q->title;
						$item_author = $q->author;
					} // if
				} // if
				break;
			
			case 'faq':
				// get faq categories in this article
				$all_cats = array();
				$s =
					"select `md`.`faq_categories` as `faq_categories`" .
					"\n from `tl_content` as `ct`" .
					"\n inner join `tl_module` as `md` on `ct`.`module`=`md`.`id` and `md`.`type`='faqreader'" .
					"\n where `ct`.`pid`=?" .
					"\n and `ct`.`type`='module'";
				$q = $db->prepare($s)->execute(intval($this->pid));
				while ($q->next()) {
					$cats = deserialize($q->faq_categories, true);
					$all_cats = array_merge($all_cats, $cats);
				} // while
				
				// find the faq id, author title
				if (count($all_cats) > 0) {
					$all_cats = array_unique($all_cats);
					$s =
						"select `f`.`id` as `id`, `f`.`question` as `title`, `u`.`username` as `author`" . 
						"\n from `tl_faq` as `f`" . 
						"\n inner join `tl_user` as `u` on `f`.`author`=`u`.`id`".
						"\n where `f`.`pid` in  (" . implode(',', $all_cats) . ")" . 
						"\n and (`f`.`id`=? or `f`.`alias`=?)";
					if (!BE_USER_LOGGED_IN) $s .= "\n and `f`.`published`=1";
					$i = $this->Input->get('items');
					$q = $db->prepare($s)->limit(1)->execute((is_numeric($i) ? $i : 0), $i);
					if ($q->next()) {
						$item_id = $q->id;
						$item_title = $q->title;
						$item_author = $q->author;
					} // if
				} // if
			break;

			case 'event':
				// get calendars in this article
				$all_cals = array();
				$s =
					"select `md`.`cal_calendar` as `cal_calendar`" .
					"\n from `tl_content` as `ct`" .
					"\n inner join `tl_module` as `md` on `ct`.`module`=`md`.`id` and `md`.`type`='eventreader'" .
					"\n where `ct`.`pid`=?" .
					"\n and `ct`.`type`='module'";
				$q = $db->prepare($s)->execute(intval($this->pid));
				while ($q->next()) {
					$cals = deserialize($q->cal_calendar, true);
					$all_cals = array_merge($all_cals, $cals);
				} // while
				$all_cals = array_unique($all_cals);
				if (count($all_cals) > 0)
					$all_cals = $this->sortOutProtected('tl_calendar', $all_cals);
				
				// find the news id
				if (count($all_cals) > 0) {
					$s =
						"select `e`.`id` as `id`, `e`.`title` as `title`, `u`.`username` as `author`" . 
						"\n from `tl_calendar_events` as `e`" . 
						"\n inner join `tl_user` as `u` on `e`.`author`=`u`.`id`".
						"\n where `e`.`pid` in  (" . implode(',', $all_cals) . ")" . 
						"\n and (`e`.`id`=? or `e`.`alias`=?)";
					if (!BE_USER_LOGGED_IN)
						$s .= 	
							"\n and (`e`.`start`='' or `e`.`start`<?)" .
							"\n and (`e`.`stop`='' or `e`.`stop`>?)".
							"\n and `e`.`published`=1";
					$i = $this->Input->get('events');
					$q = $db->prepare($s)->limit(1);
					if (BE_USER_LOGGED_IN)
						$q = $q->execute((is_numeric($i) ? $i : -1), $i);
					else {
						$t = time();
						$q = $q->execute((is_numeric($i) ? $i : -1), $i, $t, $t);
					} // if
					if ($q->next()) {
						$item_id = $q->id;
						$item_title = $q->title;
						$item_author = $q->author;
					} // if
				} // if
				break;
		} // switch
		if ($item_id < 0) return;
		
		// find the ticket id
		$q = $db->prepare(
			"\n select " .
				HELPDESK_TCKCOLS.','.
				HELPDESK_CATCOLS.
			"\n from `tl_helpdesk_comments` as `cmt`" .
			"\n inner join `tl_helpdesk_tickets` as `tck`" .
				" on `cmt`.`ticket`=`tck`.`id`" .
			"\n inner join `tl_helpdesk_categories` as `cat`" .
				" on `tck`.`pid`=`cat`.`id`" .
				($hd->role<HELPDESK_ADMIN ? " and `cat`.`published`='1'" : "") .
			"\n where `cmt`.`item_id`=? and `cmt`.`item_type`=?"
		)->limit(1)->execute($item_id, $item_type);
		if ($q->next() && trim($q->cat_notify_fe_url)!='') {
			$hd->authorize($q);
			if ($hd->hasTicketAccess($q->cat_access, $q)) {
				$this->Template->ticketLink = $this->createUrl($q->cat_notify_fe_url, 'topic', $q->tck_id);

				// count the replies to this ticket
				$q = 
					$db->prepare(
						"\n select count(distinct `id`) as `replycount`" .
						"\n from `tl_helpdesk_messages`" .
						"\n where `pid`=?".
						($hd->role<HELPDESK_SUPPORTER ? " and `published`='1'" : "")
					)->execute($q->tck_id);
				if ($q->next() && $q->replycount>1) 
					$this->Template->replyCount = $q->replycount-1;
			} // if
		} // if
		
		if (is_null($this->Template->ticketLink)) {
			// compile category
			$q = $db->prepare(
				"\n select " .
					HELPDESK_CATCOLS.
				"\n from `tl_helpdesk_categories` as `cat`" .
				"\n where `cat`.`id`=?" .
				  ($hd->role<HELPDESK_ADMIN ? "\n and `cat`.`published`='1'" : "")
			)->limit(1)->execute($this->helpdesk_category);
			if ($q->next() && trim($q->cat_notify_fe_url)!='') {
				$hd->authorize($q);
				if ($hd->hasCategoryAccess($q->cat_access)) {
					// create new ticket
					$ticketSet = array(
						'pid'			=> $this->helpdesk_category,
						'tstamp'		=> time(),
						'client'		=> $item_author,
						'client_be'		=> '1',
						'supporter'		=> '',
						'supporter_be'	=> '1',
						'subject'		=> $item_title,
						'status'		=> '1',
						'published'		=> '1'
					);
					$objNewTicket = 
						$db	->prepare("INSERT INTO `tl_helpdesk_tickets` %s")
							->set($ticketSet)
							->execute();
					$ticketId = $objNewTicket->insertId;
					$this->Template->ticketLink = $this->createUrl($q->cat_notify_fe_url, 'topic', $ticketId);

					// create message
					$t = $GLOBALS['TL_LANG']['tl_helpdesk_comments']['commentsfor'];
					$m = str_replace('[[title]]', $item_title, $t[0]."\n\n".$t[1]."\n\n".$t[2]."\n");
					$m = str_replace('[[url]]', $this->Environment->base . $this->Environment->request, $m);
					$messageSet = array(
						'pid'			=> $ticketId,
						'tstamp'		=> time(),
						'reply'			=> '0',
						'by_email'		=> '0',
						'poster'		=> $item_author,
						'poster_cd'		=> '1',
						'message'		=> $m,
						'published'		=> '1'
					);
					$db	->prepare("INSERT INTO `tl_helpdesk_messages` %s")
						->set($messageSet)
						->execute();
					
					// create index
					$indexSet = array(
						'item_type'		=> $item_type,
						'item_id'		=> $item_id,
						'ticket'		=> $ticketId,
					);
					$db	->prepare("INSERT INTO `tl_helpdesk_comments` %s")
						->set($indexSet)
						->execute();

					// synchronize
					$this->settings->syncCat($this->helpdesk_category);
					$this->settings->syncUser($item_author);
					$this->settings->syncTotals();
				} // if
			} // if
		} // if
	} // compile
	
	/**
	 * Get array of category id's to display by this module
	 */
	public function categories()
	{
		return array($this->helpdesk_category);
	} // categories
	
	/**
	 * Create frontend url for hyperlink.
	 */
	public function createUrl()
	{
		$params = func_get_args();
		if (isset($params[0]) && is_array($params[0])) 
			$params = array_values($params[0]);
		
		global $objPage;
		$arrRow = array(
				'id' => $objPage->id,
				'alias' => $objPage->alias
		);
		
		if (empty($params))
		{
			return $this->generateFrontendUrl($arrRow);
		}
		else 
		{
			$strParams = '/' . implode('/', $params);
			return $this->generateFrontendUrl($arrRow, $strParams);
		}
	} // createUrl

	/**
	 * Filter out protected items.
	 * $aTable = tl_news_archive, tl_calendar
	 */
	private function sortOutProtected($aTable, $aIds)
	{
		if (BE_USER_LOGGED_IN) return $aIds;
		$this->import('FrontendUser', 'User');
		$objArchive = $this->Database->execute(
			"select id, protected, groups from " .$aTable . " where id in (" . implode(',', $aIds) . ")");
		$aIds = array();
		while ($objArchive->next()) {
			if ($objArchive->protected) {
				$groups = deserialize($objArchive->groups, true);
				if (!is_array($this->User->groups) || count($this->User->groups) < 1 || !is_array($groups) || count($groups) < 1) continue;
				if (count(array_intersect($groups, $this->User->groups)) < 1) continue;
			} // if
			$aIds[] = $objArchive->id;
		} // while
		return $aIds;
	} // sortOutProtected
	
	
} // HelpdeskComments

?>
