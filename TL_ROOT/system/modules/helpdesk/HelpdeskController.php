<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Class HelpdeskController
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

require_once('HelpdeskConstants.php');

class HelpdeskController extends Controller
{
	private	$compiler;
	private	$ident;
	private	$pageof;
	private	$module;
	private	$template;
	private $urlsep;
	protected $settings;

	/**
	 * Constructor
	 */
	public function __construct(&$module)
	{
		parent::__construct();
		$this->import('Database');
		$this->module = &$module;
	} // __construct
	
	/**
	 * Assign compiler and template
	 */
	public function generate(&$strTemplate)
	{
		$this->urlsep = $GLOBALS['TL_CONFIG']['disableAlias'] ? '&' : '?';
		
		if (isset($GLOBALS['HELPDESKSKIP']) && $GLOBALS['HELPDESKSKIP']) {
			$strTemplate	= 'helpdesk_empty';	
			$this->compiler	= 'hide';
			return;
		} // if

		$this->import('HelpdeskSettings', 'settings');
		if ($this->settings->updaterequired) {
			$strTemplate	= 'helpdesk_message';	
			$this->ident	= 'error';
			$this->compiler	= 'message';
			$this->Session->set('HELPDESK_MESSAGE_TEXT', 'Please notify system admin to update Contao database.');
			$this->Session->set('HELPDESK_MESSAGE_BUTTONS', null);
			$GLOBALS['HELPDESKSKIP'] = true;
			return;
		} // if
	
		$actions = array(
			//	  act[0]				strTemplate					compiler
			array('category',			'helpdesk_listtickets',		'listTickets'		),
			array('unread',				'helpdesk_listunread',		'listUnread'		),
			array('mine',				'helpdesk_listunread',		'listMine'			),
			array('recent',				'helpdesk_listunread',		'listRecent'		),
			array('unanswered',			'helpdesk_listunread',		'listUnanswered'	),
			array('search',				'helpdesk_search',			'search'			),
			array('find',				'helpdesk_find',			'find'				),
			array('ticket',				'helpdesk_listmessages',	'listMessages'		), // relict, no nonger actively used
			array('topic',				'helpdesk_listmessages',	'listMessages'		),
			array('message',			'helpdesk_listmessages',	'showMessage'		),
			array('create',				'helpdesk_editmessage',		'createTicket'		),
			array('reply',				'helpdesk_editmessage',		'replyTicket'		),
			array('quote',				'helpdesk_editmessage',		'quoteMessage'		),
			array('edit',				'helpdesk_editmessage',		'editMessage'		),
			
			array('markread',			'helpdesk_empty',			'markRead'			),
			array('open',				'helpdesk_empty',			'openTicket'		),
			array('close',				'helpdesk_empty',			'closeTicket'		),
			array('publishticket',		'helpdesk_empty',			'publishTicket'		),
			array('unpublishticket',	'helpdesk_empty',			'unpublishTicket'	),
			array('pinup',				'helpdesk_empty',			'pinupTicket'		),
			array('unpin',				'helpdesk_empty',			'unpinTicket'		),
			array('remove',				'helpdesk_empty',			'removeTicket'		),
			array('cut',				'helpdesk_empty',			'cutTicket'			),
			array('paste',				'helpdesk_empty',			'pasteCategory'		),
			array('cutmsg',				'helpdesk_empty',			'cutMessage'		),
			array('pastetck',			'helpdesk_empty',			'pasteTicket'		),
			
			array('delete',				'helpdesk_empty',			'deleteMessage'		),
			array('publish',			'helpdesk_empty',			'publishMessage'	),
			array('unpublish',			'helpdesk_empty',			'unpublishMessage'	),
			
			array('show',				'helpdesk_message',			'message'			)
		);
		$strTemplate	= 'helpdesk_listcategories';	
		$this->compiler	= 'listCategories';
		foreach ($actions as $act) {
			$this->ident = $this->Input->get($act[0]);
			if ($this->ident) {
				$strTemplate = $act[1];
				$this->compiler = $act[2];
				$GLOBALS['HELPDESKSKIP'] = true;
				break;
			} // if
		} // foreach			
	} // generate
	
	/**
	 * Compile module
	 */
	public function compile(Template &$template)
	{
		$compiler = $this->compiler;
		if ($compiler=='hide') return;
		$this->template = &$template;
		$this->loadLanguageFile('tl_helpdesk_frontend');
		$this->loadLanguageFile('tl_helpdesk_bbcode');
		$this->loadLanguageFile('countries');
		$this->template->helpdesk = new Helpdesk(
			$this->module, 
			$this->module->createUrl(),
			$compiler=='listCategories'
		);
		$this->$compiler($this->ident);
	} // compile

	/**
	 * Display message
	 */
	private function message()
	{
		$hd = &$this->template->helpdesk;
		$hd->severity = $this->ident;
		$hd->message = $this->Session->get('HELPDESK_MESSAGE_TEXT');
		$hd->buttons = $this->Session->get('HELPDESK_MESSAGE_BUTTONS');
		if (!is_array($hd->buttons)) $hd->buttons = array();
		$this->Session->set('HELPDESK_MESSAGE_TEXT', null);
		$this->Session->set('HELPDESK_MESSAGE_BUTTONS', null);
	} // message
	
	/**
	 * Search form 
	 */
	private function search()
	{
		$hd = &$this->template->helpdesk;

		// submitting new search?
		$action = $this->Input->post('helpdesk_action');
		if ($action == $this->compiler) {
			// get advanced
			$hd->advanced = intval($this->Input->post('helpdesk_advanced'));
			
			// get categories
			$categories = $this->Input->post('helpdesk_categories');
			if (is_array($categories)) sort($categories); else $categories = array();
			
			// get parts
			$hd->poster = intval($this->Input->post('helpdesk_poster'));
			$hd->subject = intval($this->Input->post('helpdesk_subject'));
			$hd->msgtext = intval($this->Input->post('helpdesk_message'));
			$hd->attachments = intval($this->Input->post('helpdesk_attachments'));
			
			// get terms
			$hd->searchterms = html_entity_decode($_POST['helpdesk_searchterms'], ENT_COMPAT, $GLOBALS['TL_CONFIG']['characterSet']);
			if (get_magic_quotes_gpc()) $hd->searchterms = stripslashes($hd->searchterms);
			$hd->searchterms = trim($hd->searchterms);
			
			// get find mode
			$hd->findmode = $this->Input->post('helpdesk_findmode');
			if (!array_key_exists($hd->findmode, $hd->text['findmodes'])) {
				$x = array_keys($hd->text['findmodes']);
				$hd->findmode = reset($x);
			} // if
			
			// get search mode
			$hd->searchmode = $this->Input->post('helpdesk_searchmode');
			if (!array_key_exists($hd->searchmode, $hd->text['searchmodes'])) {
				$x = array_keys($hd->text['searchmodes']);
				$hd->searchmode = reset($x);
			} // if
			
			// ok?
			if ($hd->searchterms!='' && ($hd->poster || $hd->subject || $hd->msgtext || $hd->attachments) && count($categories)>0) {
				if (!$this->floodCheck('search')) { 
					$this->Session->set(
						'HELPDESK_MESSAGE_TEXT', 
						sprintf($hd->text['warnsearchflood'], $this->settings->searchdelay)
					);
					$this->Session->set(
						'HELPDESK_MESSAGE_BUTTONS', 
						array(array(
							'link'	=> 'javascript:history.go(-1)',
							'label'	=> $hd->text['continue']
						))
					);
					$this->redirect($this->module->createUrl('show', 'warning'));
				} // if
				
				$this->Session->set('HELPDESK_SEARCHRESULT', null);
				$this->redirect($this->makeSearchUrl('find', $categories));
			} else { 
				if ($hd->searchterms=='') $hd->searchtermsMissing = true;
				if (!$hd->poster && !$hd->subject && !$hd->msgtext && !$hd->attachments) $hd->noPartsChecked = true;
				if (!count($categories)) $hd->noCategoriesChecked = true;
			} // if
		} else
			$categories = $this->getSearchParams('search');

		// compile list of categories for selection
		$hd->categories = array();
		$qcat = $this->Database->execute(
			"\n select" .
				HELPDESK_CATCOLS.
			"\n from `tl_helpdesk_categories` AS `cat`" .
			($hd->backend
				? ($hd->role==HELPDESK_ADMIN ? "" : "\n where `published`='1'")
				: "\n where `published`='1' and `id` in (" . $this->implodeInt($this->module->allcategories(),-1) . ")" 
			).
			"\n order by `sorting`, `access`, `title`"
		);
		while ($qcat->next()) {
			$hd->authorize($qcat);
			if ($hd->hasCategoryAccess($qcat->cat_access)) {
				// setup category record
				$cat = new HelpdeskCategory($this->module, $hd, $qcat);
				$cat->checked = in_array('all', $categories) || in_array($cat->id, $categories);
				$hd->categories[] = $cat;
			} // while
		} // while
		
		$hd->formLink	= $this->module->createUrl('search', $this->Input->get('search'));
		$hd->formAction = $this->compiler;
		$hd->submitText = $hd->text['search'];
		
		$this->setBreadcrumb(array(
			array($hd->text['index'], $hd->listCategoriesLink),
			array($hd->text['search'], $hd->formLink)
		));
	} // search
	
	/**
	 * Find and display search results 
	 */
	private function find()
	{
		$this->Session->set('HELPDESK_CLIPBOARD', null);
		$hd = &$this->template->helpdesk;
		$hd->result = array();

		$categories = $this->getSearchParams('find');
		
		// check if cached
		$hd->result = $this->Session->get('HELPDESK_SEARCHRESULT');
		if (is_array($hd->result) &&
			$categories			== $this->Session->get('HELPDESK_CATEGORIES')	&&
			$hd->searchterms	== $this->Session->get('HELPDESK_SEARCHTERMS')	&&
			$hd->findmode		== $this->Session->get('HELPDESK_FINDMODE')		&&
			$hd->searchmode		== $this->Session->get('HELPDESK_SEARCHMODE')	&&
			$hd->poster			== $this->Session->get('HELPDESK_INPOSTER')		&&
			$hd->subject		== $this->Session->get('HELPDESK_INSUBJECT')	&&
			$hd->msgtext		== $this->Session->get('HELPDESK_INMESSAGE')	&&
			$hd->attachments	== $this->Session->get('HELPDESK_INATCH')		) {
			// results are (should be) allready in session
			$hd->totrecs	= count($hd->result);
			$hd->totmatches	= intval($this->Session->get('HELPDESK_TOTMATCHES'));
		} else {
			// new search
			$hd->result = array();
			$this->Session->set('HELPDESK_SEARCHRESULT', null);
			
			// prepare terms clause and args
			$clause = ''; 
			$clauseargs = $selargs = array();
			$subjectrel = $messagerel = null;

			if ($hd->subject)
				switch ($hd->searchmode) {
					case 'like':
						$this->termsClause("(`msg`.`reply`='0' and `tck`.`subject` like ?)", $hd->searchterms, $clause, $clauseargs);
						break;
					case 'binary':
						$this->termsClause("(binary `msg`.`reply`='0' and `tck`.`subject` like ?)", $hd->searchterms, $clause, $clauseargs);
						break;
					case 'boolean':
						$subjectrel = 'match (`tck`.`subject`) against (? in boolean mode)';
						$selargs[] = $hd->searchterms;
						$this->termsClause("(`msg`.`reply`='0' and $subjectrel)", $hd->searchterms, $clause, $clauseargs);
						break;
					default:
						$subjectrel = 'match (`tck`.`subject`) against (?)';
						$selargs[] = $hd->searchterms;
						$this->termsClause("(`msg`.`reply`='0' and $subjectrel)", $hd->searchterms, $clause, $clauseargs);
				} // switch
				
			if ($hd->msgtext)
				switch ($hd->searchmode) {
					case 'like':
						$this->termsClause("`msg`.`message` like ?", $hd->searchterms, $clause, $clauseargs);
						break;
					case 'binary':
						$this->termsClause("binary `msg`.`message` like ?", $hd->searchterms, $clause, $clauseargs);
						break;
					case 'boolean':
						$messagerel = 'match (`msg`.`message`) against (? in boolean mode)';
						$selargs[] = $hd->searchterms;
						$this->termsClause($messagerel, $hd->searchterms, $clause, $clauseargs);
						break;
					default:
						$messagerel = 'match (`msg`.`message`) against (?)';
						$selargs[] = $hd->searchterms;
						$this->termsClause($messagerel, $hd->searchterms, $clause, $clauseargs);
				} // switch
				
			if ($hd->poster)
				switch ($hd->searchmode) {
					case 'like':
						$this->termsClause("`msg`.`poster` like ?", $hd->searchterms, $clause, $clauseargs);
						break;
					case 'binary':
						$this->termsClause("binary `msg`.`poster` like ?", $hd->searchterms, $clause, $clauseargs);
						break;
					default:
						$this->termsClause("`msg`.`poster` like ?", '%'.$hd->searchterms.'%', $clause, $clauseargs);
				} // switch
				
			if ($hd->attachments) {
				switch ($hd->searchmode) {
					case 'like':
						$this->termsClause("`msg`.`atch1name` like ?", $hd->searchterms, $clause, $clauseargs);
						$this->termsClause("`msg`.`atch2name` like ?", $hd->searchterms, $clause, $clauseargs);
						$this->termsClause("`msg`.`atch3name` like ?", $hd->searchterms, $clause, $clauseargs);
						$this->termsClause("`msg`.`atch4name` like ?", $hd->searchterms, $clause, $clauseargs);
						$this->termsClause("`msg`.`atch5name` like ?", $hd->searchterms, $clause, $clauseargs);
						break;
					case 'binary':
						$this->termsClause("binary `msg`.`atch1name` like ?", $hd->searchterms, $clause, $clauseargs);
						$this->termsClause("binary `msg`.`atch2name` like ?", $hd->searchterms, $clause, $clauseargs);
						$this->termsClause("binary `msg`.`atch3name` like ?", $hd->searchterms, $clause, $clauseargs);
						$this->termsClause("binary `msg`.`atch4name` like ?", $hd->searchterms, $clause, $clauseargs);
						$this->termsClause("binary `msg`.`atch5name` like ?", $hd->searchterms, $clause, $clauseargs);
						break;
					default:
						$this->termsClause("`msg`.`atch1name` like ?", '%'.$hd->searchterms.'%', $clause, $clauseargs);
						$this->termsClause("`msg`.`atch2name` like ?", '%'.$hd->searchterms.'%', $clause, $clauseargs);
						$this->termsClause("`msg`.`atch3name` like ?", '%'.$hd->searchterms.'%', $clause, $clauseargs);
						$this->termsClause("`msg`.`atch4name` like ?", '%'.$hd->searchterms.'%', $clause, $clauseargs);
						$this->termsClause("`msg`.`atch5name` like ?", '%'.$hd->searchterms.'%', $clause, $clauseargs);
				} // switch
			} // if
			
			// create preg patterns
			$pattern = str_replace(array('%','_'), array('.*', '.'), preg_quote($hd->searchterms,'#'));
			$hd->pattern_binary = '#'.$pattern.'#';
			$hd->pattern_like = $hd->pattern_binary.'i';
			$hd->pattern_full = '#.*'.$pattern.'.*#i';
			
			// loop over categories
			$sql =	"\n select" . HELPDESK_CATCOLS. 
					"\n from `tl_helpdesk_categories` AS `cat`";
			if ($hd->role<HELPDESK_ADMIN) {
				$sql .= "\n where `published`='1'";
				if (!$hd->backend && !in_array('all', $categories)) 
					$sql .= "\n and `id` in (" . $this->implodeInt($categories,-1) . ")";
			} // if
			$qcat = $this->Database->execute($sql);
			
			$hd->totrecs = $hd->totmatches = 0;
			while ($qcat->next()) {
				$hd->authorize($qcat);
				if ($hd->hasCategoryAccess($qcat->cat_access)) {
					// setup category record
					$hd->category = new HelpdeskCategory($this->module, $hd, $qcat);
					$cat = &$hd->category;
					$privateAccess = $cat->isPrivate && $hd->role<HELPDESK_SUPPORTER;
					
					// load bbcode parser
					if (is_null($hd->parser)) {
						$hd->parser = new HelpdeskBbcodeParser($hd->theme);
						$hd->parser->module = &$this->module;
					} // if
					
					// prepare current args
					$args = array();
					foreach ($selargs as $arg) $args[] = $arg;
					$args[] = $cat->id;
					if ($privateAccess) {
						$args[] = $hd->username;
						$args[] = $hd->backend;
					} // if
					foreach ($clauseargs as $arg) $args[] = $arg;
					
					$topics = array();
					
					// find matching messages
					$qmsg = $this->Database->prepare(
						"\n select" .
							"\n `tck`.`id` as `tck_id`," .
							"\n `msg`.`id` as `msg_id`," .
							"\n `msg`.`reply` as `msg_reply`," .
							"\n `msg`.`poster` as `msg_poster`," .
							"\n `tck`.`subject` as `tck_subject`," .
							"\n `msg`.`message` as `msg_message`," .
							"\n `msg`.`atch1name` as `msg_atch1name`," .
							"\n `msg`.`atch2name` as `msg_atch2name`," .
							"\n `msg`.`atch3name` as `msg_atch3name`," .
							"\n `msg`.`atch4name` as `msg_atch4name`," .
							"\n `msg`.`atch5name` as `msg_atch5name`" .
							($subjectrel ? ",\n $subjectrel as `tck_relevance`" : "") .
							($messagerel ? ",\n $messagerel as `msg_relevance`" : "") .
						"\n from `tl_helpdesk_tickets` as `tck`" .
							"\n left join `tl_helpdesk_messages` as `msg`" .
								" on `tck`.`id`=`msg`.`pid`" .
								($hd->role<HELPDESK_SUPPORTER ? " and `msg`.`published`='1'" : "") .
						"\n where `tck`.`pid`=?" . 
								($hd->role<HELPDESK_SUPPORTER ? " and `tck`.`published`='1'" : "") .
								($privateAccess ? " and `tck`.`client`=? and `client_be`=?" : "") .
						"\n and (\n".
							$clause.
						"\n )"
					)->execute($args);

					while ($qmsg->next()) {
						// compute relevance
						$relevance = 0;
						if ($hd->poster)
							$relevance += $this->matchLike($qmsg->msg_poster);
						switch ($hd->searchmode) {
							case 'binary':
							case 'like':
								if ($hd->subject && intval($qmsg->msg_reply)==0)
									$relevance += $this->matchLike($qmsg->tck_subject);
								if ($hd->msgtext)
									$relevance += $this->matchLike($qmsg->msg_message);
								break;
							default:
								if ($hd->subject && intval($qmsg->msg_reply)==0) 
									$relevance += $qmsg->tck_relevance;
								if ($hd->msgtext) 
									$relevance += $qmsg->msg_relevance;
						} // switch
						if ($hd->attachments)
							$relevance +=
								$this->matchLike($qmsg->msg_atch1name) +
								$this->matchLike($qmsg->msg_atch2name) +
								$this->matchLike($qmsg->msg_atch3name) +
								$this->matchLike($qmsg->msg_atch4name) +
								$this->matchLike($qmsg->msg_atch5name);

						// only add true matches (false matches my be from terms containing % or _)
						if ($relevance > 0) {
							if ($hd->findmode == 'messages') {
								$hd->totmatches++;
								
								// find insert position
								$ipos = 0;
								while ($ipos < $hd->totrecs) {
									if ($relevance > $hd->result[$ipos]['relevance']) break;
									if ($relevance == $hd->result[$ipos]['relevance'] && 
										$qmsg->msg_id > $hd->result[$ipos]['id']) break;
									$ipos++;
								} // while
								
								if ($ipos < $this->settings->searchmax) {
									// prepare message
									$message = preg_replace('#\s+#', ' ', strip_tags($hd->parser->parse(trim($qmsg->msg_message)."\n",$qmsg->msg_id)));
									if (strlen($message) > 200) $message = substr($message, 0, 179) . '...';
									
									// create attachments array
									$attachments = array();
									$this->appendAttachment($qmsg->msg_id, 1, $qmsg->msg_atch1name, $attachments);
									$this->appendAttachment($qmsg->msg_id, 2, $qmsg->msg_atch2name, $attachments);
									$this->appendAttachment($qmsg->msg_id, 3, $qmsg->msg_atch3name, $attachments);
									$this->appendAttachment($qmsg->msg_id, 4, $qmsg->msg_atch4name, $attachments);
									$this->appendAttachment($qmsg->msg_id, 5, $qmsg->msg_atch5name, $attachments);
							
									// create result record
									$newrec = array(
										'relevance'		=> $relevance,
										'link'			=> $this->module->createUrl('message', $qmsg->msg_id),
										'id'			=> $qmsg->msg_id,
										'poster'		=> $qmsg->msg_poster,
										'subject'		=> $qmsg->tck_subject, 
										'message'		=> $message,
										'attachments'	=> $attachments
									);
											
									if ($ipos == $hd->totrecs) {
										$hd->result[] = $newrec;
										$hd->totrecs++;
									} else {
										array_splice($hd->result, $ipos, 0, array($newrec));
										if ($hd->totrecs < $this->settings->searchmax)
											$hd->totrecs++;
										else
											array_pop($hd->result);
									} // if
								} // if
							} else {
								// findmode == topics
								if (array_key_exists($qmsg->tck_id, $topics)) {
									$topic = &$topics[$qmsg->tck_id];
									$topic['relevance'] += $relevance;
									if ($topic['id'] > $qmsg->msg_id) {
										// update record
										$topic['id']		= $qmsg->msg_id;
										$topic['poster']	= $qmsg->msg_poster;
										$topic['message']	= $qmsg->msg_message;
										$topic['attachments'] = array(
											$qmsg->msg_atch1name,
											$qmsg->msg_atch2name,
											$qmsg->msg_atch3name,
											$qmsg->msg_atch4name,
											$qmsg->msg_atch5name
										);
									} // if
									unset($topic);
								} else {
									$hd->totmatches++;
									$topics[$qmsg->tck_id] = array(
										// create attachments array
										'relevance'		=> $relevance,
										'id'			=> $qmsg->msg_id,
										'poster'		=> $qmsg->msg_poster,
										'subject'		=> $qmsg->tck_subject, 
										'message'		=> $qmsg->msg_message,
										'atch'			=> array(
											$qmsg->msg_atch1name,
											$qmsg->msg_atch2name,
											$qmsg->msg_atch3name,
											$qmsg->msg_atch4name,
											$qmsg->msg_atch5name
										)
									);
								} // if
							} // if findmode
						} // if
					} // while
					foreach ($topics as $pid => $topic) {
						// find insert position
						$ipos = 0;
						while ($ipos < $hd->totrecs) {
							if ($topic['relevance'] > $hd->result[$ipos]['relevance']) break;
							if ($topic['relevance'] == $hd->result[$ipos]['relevance'] && 
								$pid > $hd->result[$ipos]['pid']) break;
							$ipos++;
						} // while
						
						if ($ipos < $this->settings->searchmax) {
							// prepare message
							$message = preg_replace('#\s+#', ' ', strip_tags($hd->parser->parse(trim($topic['message'])."\n",$topic['id'])));
							if (strlen($message) > 200) $message = substr($message, 0, 179) . '...';
							
							// create attachments array
							$attachments = array();
							foreach ($topic['atch'] as $atch)
								$this->appendAttachment($topic['id'], 1, $atch, $attachments);
									
							// create result record
							$newrec = array(
								'relevance'		=> $topic['relevance'],
								'link'			=> $this->module->createUrl('message', $topic['id']),
								'pid'			=> $pid,
								'id'			=> $topic['id'],
								'poster'		=> $topic['poster'],
								'subject'		=> $topic['subject'], 
								'message'		=> $message,
								'attachments'	=> $attachments
							);
			
							if ($ipos == $hd->totrecs) {
								$hd->result[] = $newrec;
								$hd->totrecs++;
							} else {
								array_splice($hd->result, $ipos, 0, array($newrec));
								if ($hd->totrecs < $this->settings->searchmax)
									$hd->totrecs++;
								else
									array_pop($hd->result);
							} // if
						} // if
					} // foreach
				} // if
			} // while

			// cache result in session for subsequent paging
			$this->Session->set('HELPDESK_SEARCHRESULT',$hd->result);
			$this->Session->set('HELPDESK_TOTMATCHES',	$hd->totmatches);
			$this->Session->set('HELPDESK_CATEGORIES',	$categories);
			$this->Session->set('HELPDESK_SEARCHTERMS',	$hd->searchterms);
			$this->Session->set('HELPDESK_FINDMODE',	$hd->findmode);
			$this->Session->set('HELPDESK_SEARCHMODE',	$hd->searchmode);
			$this->Session->set('HELPDESK_INPOSTER',	$hd->poster);
			$this->Session->set('HELPDESK_INSUBJECT',	$hd->subject);
			$this->Session->set('HELPDESK_INMESSAGE',	$hd->msgtext);
			$this->Session->set('HELPDESK_INATCH',		$hd->attachments);
		} // if new search
			
		// create page array and navigation
		$hd->pagesize = $hd->settings->spage;
		if ($hd->pagesize > 0) {
			$hd->pages = intval(($hd->totrecs+$hd->pagesize-1)/$hd->pagesize);
			$hd->page = intval($this->Input->get(Helpdesk::pageParam()));
			if ($hd->page < 1) $hd->page = 1; else if ($hd->page > $hd->pages) $hd->page = $hd->pages;
			$hd->createPageNav($this->module, $this->makeSearchParams('find', $categories));
			$hd->pageResult = array();
			$r = ($hd->page-1) * $hd->pagesize;
			$n = 0;
			while ($r < $hd->totrecs && $n < $hd->pagesize) {
				$hd->pageResult[] = &$hd->result[$r++];
				$n++;
			} // while
		} else {
			$hd->pages = $hd->page = 1;
			$hd->pageResult = &$hd->result;
		} // if
		
		// create re-search link
		$hd->searchLink = $this->makeSearchUrl('search', $categories);
		$hd->findLink = $this->makeSearchUrl('find', $categories);
		if ($hd->page > 1) $hd->findLink = Helpdesk::addPage($hd->findLink, $hd->page);

		// setup breadcrumb
		$this->setBreadcrumb(array(
			array($hd->text['index'], $hd->listCategoriesLink),
			array($hd->text['search'], $hd->searchLink),
			array($hd->text['searchresult'], $hd->findLink)
		));
	} // find

	/**
	 * Find # of matches of the search pattern in a subject, where pattern is 
	 * formed with SQL wildcards % and _
	 */
	private function matchLike($subject)
	{
		if ($subject) {
			$hd = &$this->template->helpdesk;
			switch ($hd->searchmode) {
				case 'binary': 
					return preg_match_all($hd->pattern_binary, $subject, $matches);
				case 'like': 
					return preg_match_all($hd->pattern_like, $subject, $matches);
				default:
					return preg_match_all($hd->pattern_full, $subject, $matches);
			} // switch
		} // if
		return 0;
	} // matchLike
	
	/**
	 * Make search/find url
	 */
	private function makeSearchUrl($key, $categories)
	{
		return $this->module->createUrl($this->makeSearchParams($key, $categories));
	} // makeSearchUrl
	
	/**
	 * Make search/find url parameters
	 */
	private function makeSearchParams($key, $categories)
	{
		$hd = &$this->template->helpdesk;
		if ($hd->advanced) $opts .= 'v';
		if ($hd->findmode=='topics') $opts .= 't';
		if ($hd->searchmode=='boolean') $opts .= 'b';
		if ($hd->searchmode=='like') $opts .= 'l';
		if ($hd->searchmode=='binary') $opts .= 'i';
		if ($hd->poster) $opts .= 'p';
		if ($hd->subject) $opts .= 's';
		if ($hd->msgtext) $opts .= 'm';
		if ($hd->attachments) $opts .= 'a';
		return 
			array(
				$key, implode(',',$categories),
				'terms', $this->encode($hd->searchterms),
				'opts', $opts
			);
	} // makeSearchParams
	
	/**
	 * Get search/find parameters
	 */
	private function getSearchParams($key)
	{
		$hd = &$this->template->helpdesk;
		
		// get search terms
		$hd->searchterms = $this->decode($this->Input->get('terms'));
		
		// get options
		$opts = $this->Input->get('opts');

		// get avdanced
		$hd->advanced = strpos($opts,'v')!==false;

		// get findmode
		$hd->findmode = strpos($opts,'t')!==false ? 'topics' : 'messages';
				
		// get search mode
		$hd->searchmode = 'natural';
		if (strpos($opts,'b')!==false) $hd->searchmode = 'boolean'; else
		if (strpos($opts,'l')!==false) $hd->searchmode = 'like'; else
		if (strpos($opts,'i')!==false) $hd->searchmode = 'binary';

		// get searched parts
		$hd->poster			= strpos($opts,'p')!==false;
		$hd->subject		= strpos($opts,'s')!==false;
		$hd->msgtext		= strpos($opts,'m')!==false;
		$hd->attachments	= strpos($opts,'a')!==false;
		if (!$hd->poster && !$hd->subject && !$hd->msgtext && !$hd->attachments)
			$hd->subject = $hd->msgtext = true;
		
		// return array of categories
		$categories = explode(',', $this->Input->get($key));
		return is_array($categories) ? $categories : array('all');
	} // getSearchParams

	/**
	 * Encode a string to a harmless url part
	 */
	static private function encode($str)
	{
		$len = strlen($str);
		$dst = '';
		for ($c = 0; $c < $len; $c++) {
			$ch = $str{$c};
			if ($ch==' ')
				$dst .= '+';
			else
				if (preg_match('#[a-zA-Z0-9]#', $ch))
					$dst .= $ch;
				else
					$dst .= sprintf('~%02x', ord($ch));
		} // for
		return $dst;
	} // encode
	
	/**
	 * Decode string encoded by the function above
	 */
	static private function decode($src)
	{
		$dst = '';
		$src = str_replace('+', ' ', $src);
		while ($src != '') {
			$pos = strpos($src, '~');
			if ($pos === false) {
				$dst .= $src;
				break;
			} // if
			$dst .= substr($src, 0, $pos) . chr(intval(substr($src, $pos+1, 2), 16));
			$src = substr($src, $pos+3);
		} // for
		return $dst;
	} // decode
	
	/**
	 * Append attachments
	 */
	static private function appendAttachment($msg_id, $id, $name, &$attachments)
	{
		$dld = (TL_MODE=='BE') ? 'HelpdeskBackendDownload.php' : 'HelpdeskFrontendDownload.php';
		$name = trim($name);
		if ($name!='') 
			$attachments[] = array(
				'icon'	=> Helpdesk::getFileIcon($name),
				'href'	=> 'system/modules/helpdesk/'.$dld.'?msg='.$msg_id.'&id='.$id,
				'name'	=> $name
			);
	} // appendAttachment
	
	/**
	 * Create a terms clause 
	 */
	static private function termsClause($cond, $arg, &$clause, &$args)
	{
		if ($clause != '') $clause .= "\n or ";
		$clause .= $cond;
		$args[] = $arg;
	} // termsClause
	
	/**
	 * List the available categories
	 */
	private function listCategories()
	{
		$this->Session->set('HELPDESK_SEARCHRESULT', null);
		$this->Session->set('HELPDESK_UNREADRESULT', null);
		$hd = &$this->template->helpdesk;
		if (strlen($hd->username)) $this->initReadStatus();
		$db = &$this->Database;
		$hd->categories = array();
		$qcat = $db->prepare(
			"\n select" .
				HELPDESK_CATCOLS.
			"\n from `tl_helpdesk_categories` AS `cat`" .
			($hd->backend
				? ($hd->role==HELPDESK_ADMIN ? "" : "\n where `published`='1'")
				: "\n where `published`='1' and `id` in (" . $this->implodeInt($this->module->categories(),-1) . ")" 
			).
			"\n order by `sorting`, `access`, `title`"
		)->execute($hd->username);
		$feedcats = array();
		$allcats = array();
		while ($qcat->next()) {
			$hd->authorize($qcat);
			if ($hd->hasCategoryAccess($qcat->cat_access)) {
				// setup category record
				$cat = new HelpdeskCategory($this->module, $hd, $qcat);
				$privateAccess = $cat->isPrivate && $hd->role<HELPDESK_SUPPORTER;

				if ($privateAccess) {
					// count topics and messages
					$q = $db
						->prepare(
							"\n select" .
								"\n count(distinct `tck`.`id`) as `ticketcount`," .
								"\n count(distinct `msg`.`id`) as `replycount`, " .
								"\n max(`msg`.`id`) as `latestmessage` " .
							"\n from `tl_helpdesk_tickets` as `tck`" .
								"\n left join `tl_helpdesk_messages` as `msg`" .
									" on `tck`.`id`=`msg`.`pid` and `msg`.`reply`='1' and `msg`.`published`='1'" .
							"\n where `tck`.`pid`=? and `tck`.`published`='1' and `tck`.`client`=? and `client_be`=?"
						  )
						->execute($cat->id, $hd->username, $hd->backend);
					if ($q->next()) { 
						$cat->ticketcount = $q->ticketcount;
						$cat->replycount = $q->replycount;
						$cat->latestmessage = $q->latestmessage;
					} // if
				} else
					if ($hd->role<HELPDESK_SUPPORTER) {
						$cat->ticketcount = $cat->pub_tickets;
						$cat->replycount = $cat->pub_replies;
						$cat->latestmessage = $cat->pub_latest;
					} else {
						$cat->ticketcount = $cat->all_tickets;
						$cat->replycount = $cat->all_replies;
						$cat->latestmessage = $cat->all_latest;
					} // if
				
				$cat->read = true;
				if (intval($cat->latestmessage)) {
					// check for unread tickets
					if (strlen($hd->username)) {
						$q = $db->prepare(
							"select `message` from `tl_helpdesk_categorymarks` " .
							"where `pid`=? and `username`=? and `backend`=?"
						)->execute($cat->id, $hd->username, $hd->backend ? '1' : '0');
						$cat->read = $q->next() && $q->message>=$cat->latestmessage;
					} // if
					
					// get latest post
					$q = $db
						->prepare("select `id`, `tstamp`, `poster` from `tl_helpdesk_messages` where `id`=?")
						->execute($cat->latestmessage);
					if ($q->next()) {
						$cat->latestlink = $this->module->createUrl('message', $q->id);
						$cat->latesttstamp = $hd->localDate($q->tstamp);
						$cat->latestposter = sprintf($hd->text['postedby'], $q->poster);
					} // if
				} // if
				
				$hd->categories[] = $cat;
				if ($cat->feedLink) $feedcats[] = $cat->id;
				$allcats[] = $cat->id;
			} // while
		} // while
		
		if ($hd->settings->feeds>0 && count($feedcats))
			$hd->feedLink = $hd->settings->feedlink . $this->implodeInt($feedcats) . '.xml';
		
		if (strlen($hd->username) && count($allcats)>0) {
			$hd->markReadLink = $this->module->createUrl('markread', $this->implodeInt($allcats));
			$hd->unreadLink = $this->module->createUrl('unread', $this->implodeInt($allcats));
			$hd->mineLink = $this->module->createUrl('mine', $this->implodeInt($allcats));
		} // if
		
		if (count($allcats)>0) {
			$hd->recentLink = $this->module->createUrl('recent', $this->implodeInt($allcats));
			$hd->unansweredLink = $this->module->createUrl('unanswered', $this->implodeInt($allcats));
		} // if
		
		if (!count($hd->categories)) $this->module->hideWhenEmpty();
		
		// setup breadcrumb
		$this->setBreadcrumb(array(
			array($hd->text['index'], $hd->listCategoriesLink)
		));
	} // listCategories

	/**
	 * List the yet unread tickets
	 */
	private function listUnread()
	{
		// only available for logged-in users
		$hd = &$this->template->helpdesk;
		$hd->pageResult = array();
		if (!strlen($hd->username)) return;
		$db = &$this->Database;
		
		// get displayed category id's
		$cats = explode(',', $this->Input->get('unread'));
		if (in_array('all', $cats))
			$cats = $hd->backend ? array('all') : $this->module->allcategories();
		
		// check if cached
		$hd->result = $this->Session->get('HELPDESK_UNREADRESULT');
		if (is_array($hd->result) && $this->Session->get('HELPDESK_UNREADTYPE')=='unread' && $cats==$this->Session->get('HELPDESK_CATEGORIES')) {
			// results are (should be) allready in session
			$hd->totrecs = count($hd->result);
		} else {
			// new fetch
			$hd->result = array();
			$hd->totrecs = 0;
			
			// get the categories
			$qcat = $db
				->prepare(
					"\n select " . 
						HELPDESK_CATCOLS . ',' .
						"\n `cmk`.`message` as `cmk_message` " .
					"\n from `tl_helpdesk_categories` as `cat`" .
					"\n left join `tl_helpdesk_categorymarks` as `cmk`" .
						" on `cat`.`id`=`cmk`.`pid` and `cmk`.`username`=? and `cmk`.`backend`=?" .
					($hd->backend
						? ($hd->role==HELPDESK_ADMIN ? "" : "\n where `cat`.`published`='1'")
						: "\n where `cat`.`published`='1' and `cat`.`id` in (" . $this->implodeInt($cats,-1) . ")" 
					) .
					"\n order by `cat`.`sorting`, `cat`.`access`, `cat`.`title`"
				  )
				->execute($hd->username, $hd->backend);
			while ($qcat->next()) {
				$hd->authorize($qcat);
				if ($hd->hasCategoryAccess($qcat->cat_access)) {
					// setup category record
					$hd->category = $cat = new HelpdeskCategory($this->module, $hd, $qcat);
					$privateAccess = $cat->isPrivate && $hd->role<HELPDESK_SUPPORTER;
					if ($privateAccess) {
						$q = $db
							->prepare(
								"\n select" .
									"\n max(`msg`.`id`) as `latestmessage` " .
								"\n from `tl_helpdesk_tickets` as `tck`" .
									"\n left join `tl_helpdesk_messages` as `msg`" .
										" on `tck`.`id`=`msg`.`pid` and `msg`.`reply`='1' and `msg`.`published`='1'" .
								"\n where `tck`.`pid`=? and `tck`.`published`='1' and `tck`.`client`=? and `client_be`=?"
							  )
							->execute($cat->id, $hd->username, $hd->backend);
						if ($q->next())
							$cat->latestmessage = $q->latestmessage;
					} else
						if ($hd->role<HELPDESK_SUPPORTER) {
							$cat->latestmessage = $cat->pub_latest;
						} else {
							$cat->latestmessage = $cat->all_latest;
						} // if
					
					// unread?
					if (intval($qcat->cmk_message) < intval($cat->latestmessage)) {
						
						$qtck = $db
							->prepare(
								"\n select" .
									HELPDESK_TCKCOLS.','.
									"\n ifnull(`tmk`.`message`,-1) as `tmk_message` " .
								"\n from `tl_helpdesk_tickets` as `tck`" .
									"\n left join `tl_helpdesk_ticketmarks` as `tmk`" .
										" on `tck`.`id`=`tmk`.`pid` and `tmk`.`username`=? and `tmk`.`backend`=?" .
								"\n where `tck`.`pid`=?"
							  )
							->execute($hd->username, $hd->backend, $cat->id);
						
						while ($qtck->next()) {
							if ($hd->hasTicketAccess($cat->access, $qtck)) {
								// setup ticket record
								$tck = new HelpdeskTicket($this->module, $hd, $qtck);
								
								if ($hd->role<HELPDESK_SUPPORTER) {
									$tck->replycount = $tck->pub_replies;
									$tck->latestmessage = $tck->pub_latest;
								} else {
									$tck->replycount = $tck->all_replies;
									$tck->latestmessage = $tck->all_latest;
								} // if

								// unread?
								if (intval($qtck->tmk_message) < intval($tck->latestmessage) && intval($qcat->cmk_message) < intval($tck->latestmessage)) {
									$newrec = (object)array(
										'read'				=> false,
										'cat_id'			=> $cat->id,
										'cat_title'			=> $cat->title,
										'listTicketsLink'	=> $cat->listTicketsLink . $this->urlsep.'pageof=' . $tck->id,
										'tck_id'			=> $tck->id,
										'index'				=> $tck->index,
										'subject'			=> $tck->subject,
										'client'			=> $tck->client,
										'listMessagesLink'	=> $tck->listMessagesLink,
										'replycount'		=> $tck->replycount,
										'latestmessage'		=> $tck->latestmessage
									);
									
									// page links
									if ($tck->replycount > 0 && $hd->settings->mpage > 0) {
										$pages = ceil(($tck->replycount+1) / $hd->settings->mpage);
										if ($pages > 1) {
											$newrec->pageLinks[1] = $tck->listMessagesLink;
											for ($p = 2; $p <= $pages; $p++)
												$newrec->pageLinks[$p] = Helpdesk::addPage($tck->listMessagesLink, $p);
										} // if
									} // if
						
									// get latest post
									$q = $db
										->prepare("select `id`, `tstamp`, `poster` from `tl_helpdesk_messages` where `id`=?")
										->execute($tck->latestmessage);
									if ($q->next()) {
										$newrec->latestlink = $this->module->createUrl('message', $q->id);
										$newrec->latesttstamp = $hd->localDate($q->tstamp);
										$newrec->latestposter = sprintf($hd->text['postedby'], $q->poster);
									} // if

									// append new record 
									$hd->result[] = $newrec;
									$hd->totrecs++;
								} // if
							} // if
						} // while
						
						// sort result
						if ($hd->totrecs > 1)
							usort($hd->result, array(&$this, 'compareUnreadRecs'));
					} // if
				} // if
			} // while

			// cache result in session for subsequent paging
			$this->Session->set('HELPDESK_UNREADTYPE', 'unread');
			$this->Session->set('HELPDESK_UNREADRESULT', $hd->result);
			$this->Session->set('HELPDESK_CATEGORIES', $cats);
		} // if new search
			
		// create page array and navigation
		$hd->pagesize = $hd->settings->spage;
		if ($hd->pagesize > 0) {
			$hd->pages = intval(($hd->totrecs+$hd->pagesize-1)/$hd->pagesize);
			$hd->page = intval($this->Input->get(Helpdesk::pageParam()));
			if ($hd->page < 1) $hd->page = 1; else if ($hd->page > $hd->pages) $hd->page = $hd->pages;
			$hd->createPageNav($this->module, array('unread', $this->Input->get('unread')));
			$hd->pageResult = array();
			$r = ($hd->page-1) * $hd->pagesize;
			$n = 0;
			while ($r < $hd->totrecs && $n < $hd->pagesize) {
				$hd->pageResult[] = $hd->result[$r++];
				$n++;
			} // while
		} else {
			$hd->pages = $hd->page = 1;
			$hd->pageResult = &$hd->result;
		} // if
		
		// create mark link
		$hd->markReadLink = $this->module->createUrl('markread', 'current');
		$hd->listUnreadLink = $this->module->createUrl('unread', $this->Input->get('unread'));
		if ($hd->page > 1) $hd->listUnreadLink = Helpdesk::addPage($hd->listUnreadLink, $hd->page);
		
		// setup breadcrumb
		$this->setBreadcrumb(array(
			array($hd->text['index'], $hd->listCategoriesLink),
			array($hd->text['unread'], $hd->listUnreadLink)
		));
	} // listUnread
	
	private function compareUnreadRecs($a, $b)
	{
		return $a->latestmessage<$b->latestmessage ? 1 : ($a->latestmessage>$b->latestmessage ? -1 : 0); 
	} // compareUnreadRecs
	
	/**
	 * List the tickets where a user has posted to
	 */
	private function listMine()
	{
		$hd = &$this->template->helpdesk;
		$hd->pageResult = array();
		$db = &$this->Database;
		
		// get displayed category id's
		$cats = explode(',', $this->Input->get('mine'));
		if (in_array('all', $cats))
			$cats = $hd->backend ? array('all') : $this->module->allcategories();
		
		// get user/member name and be flag
		$username = $this->Input->get('user');
		$backend = $username != '';
		if (!$backend) {
			$username = $this->Input->get('member');
			if ($username == '') {
				$username = $hd->username;
				$backend = $hd->backend;
			} // if
		} // if

		// check if cached
		$hd->result = $this->Session->get('HELPDESK_UNREADRESULT');
		if (is_array($hd->result) 
			&& $this->Session->get('HELPDESK_UNREADTYPE')=='mine' 
			&& $this->Session->get('HELPDESK_USERNAME')==$username
			&& $this->Session->get('HELPDESK_BACKEND')==$backend
			&& $cats==$this->Session->get('HELPDESK_CATEGORIES')) {
			// results are (should be) allready in session
			$hd->totrecs = count($hd->result);
		} else {
			// new fetch
			$hd->result = array();
			$hd->totrecs = 0;
			
			// get the categories
			$qcat = $db
				->prepare(
					"\n select " . 
						HELPDESK_CATCOLS . ',' .
						"\n `cmk`.`message` as `cmk_message` " .
					"\n from `tl_helpdesk_categories` as `cat`" .
					"\n left join `tl_helpdesk_categorymarks` as `cmk`" .
						" on `cat`.`id`=`cmk`.`pid` and `cmk`.`username`=? and `cmk`.`backend`=?" .
					($hd->backend
						? ($hd->role==HELPDESK_ADMIN ? "" : "\n where `cat`.`published`='1'")
						: "\n where `cat`.`published`='1' and `cat`.`id` in (" . $this->implodeInt($cats,-1) . ")" 
					) .
					"\n order by `cat`.`sorting`, `cat`.`access`, `cat`.`title`"
				  )
				->execute($hd->username, $hd->backend);
			while ($qcat->next()) {
				$hd->authorize($qcat);
				if ($hd->hasCategoryAccess($qcat->cat_access)) {
					// setup category record
					$hd->category = $cat = new HelpdeskCategory($this->module, $hd, $qcat);
					$privateAccess = $cat->isPrivate && $hd->role<HELPDESK_SUPPORTER;
					if ($privateAccess) {
						$q = $db
							->prepare(
								"\n select" .
									"\n max(`msg`.`id`) as `latestmessage` " .
								"\n from `tl_helpdesk_tickets` as `tck`" .
									"\n left join `tl_helpdesk_messages` as `msg`" .
										" on `tck`.`id`=`msg`.`pid` and `msg`.`reply`='1' and `msg`.`published`='1'" .
								"\n where `tck`.`pid`=? and `tck`.`published`='1' and `tck`.`client`=? and `client_be`=?"
							  )
							->execute($cat->id, $hd->username, $hd->backend);
						if ($q->next())
							$cat->latestmessage = $q->latestmessage;
					} else
						if ($hd->role<HELPDESK_SUPPORTER) {
							$cat->latestmessage = $cat->pub_latest;
						} else {
							$cat->latestmessage = $cat->all_latest;
						} // if
					
					$qtck = $db
						->prepare(
							"\n select" .
								HELPDESK_TCKCOLS.','.
								"\n ifnull(`tmk`.`message`,-1) as `tmk_message` " .
							"\n from `tl_helpdesk_tickets` as `tck`" .
								"\n left join `tl_helpdesk_ticketmarks` as `tmk`" .
									" on `tck`.`id`=`tmk`.`pid` and `tmk`.`username`=? and `tmk`.`backend`=?" .
							"\n where `tck`.`pid`=?" .
							"\n and ( (`tck`.`client`=? and `tck`.`client_be`=?)" .
									" or" .
									" (`tck`.`supporter`=? and `tck`.`supporter_be`=?)" .
									" or" .
									" (0 < (select count(*) from `tl_helpdesk_messages` as `msg`".
											" where `msg`.`pid`=`tck`.`id`" .
											" and `msg`.`poster`=? ".
											" and `msg`.`poster_cd` in (?,?))" .
									" )" .
							  " )"
						  )
						->execute(
							$username, $backend ? '1' : '0', $cat->id,
							$username, $backend ? '1' : '0', 
							$username, $backend ? '1' : '0', 
							$username, $backend ? '1' : '0', $backend ? '3' : '2' 
						  );
					
					while ($qtck->next()) {
						if ($hd->hasTicketAccess($cat->access, $qtck)) {
							// setup ticket record
							$tck = new HelpdeskTicket($this->module, $hd, $qtck);
							
							if ($hd->role<HELPDESK_SUPPORTER) {
								$tck->replycount = $tck->pub_replies;
								$tck->latestmessage = $tck->pub_latest;
							} else {
								$tck->replycount = $tck->all_replies;
								$tck->latestmessage = $tck->all_latest;
							} // if

							$newrec = (object)array(
								'read'				=> $hd->username == '' ||
														intval($qtck->tmk_message) >= intval($tck->latestmessage) ||
														intval($qcat->cmk_message) >= intval($tck->latestmessage),
								'cat_id'			=> $cat->id,
								'cat_title'			=> $cat->title,
								'listTicketsLink'	=> $cat->listTicketsLink . $this->urlsep.'pageof=' . $tck->id,
								'tck_id'			=> $tck->id,
								'index'				=> $tck->index,
								'subject'			=> $tck->subject,
								'client'			=> $tck->client,
								'listMessagesLink'	=> $tck->listMessagesLink,
								'replycount'		=> $tck->replycount,
								'latestmessage'		=> $tck->latestmessage
							);
							
							// page links
							if ($tck->replycount > 0 && $hd->settings->mpage > 0) {
								$pages = ceil(($tck->replycount+1) / $hd->settings->mpage);
								if ($pages > 1) {
									$newrec->pageLinks[1] = $tck->listMessagesLink;
									for ($p = 2; $p <= $pages; $p++)
										$newrec->pageLinks[$p] = Helpdesk::addPage($tck->listMessagesLink, $p);
								} // if
							} // if
				
							// get latest post
							$q = $db
								->prepare("select `id`, `tstamp`, `poster` from `tl_helpdesk_messages` where `id`=?")
								->execute($tck->latestmessage);
							if ($q->next()) {
								$newrec->latestlink = $this->module->createUrl('message', $q->id);
								$newrec->latesttstamp = $hd->localDate($q->tstamp);
								$newrec->latestposter = sprintf($hd->text['postedby'], $q->poster);
							} // if

							// append new record 
							$hd->result[] = $newrec;
							$hd->totrecs++;
						} // if
					} // while
					
					// sort result
					if ($hd->totrecs > 1)
						usort($hd->result, array(&$this, 'compareUnreadRecs'));
				} // if
			} // while

			// cache result in session for subsequent paging
			$this->Session->set('HELPDESK_UNREADTYPE', 'mine');
			$this->Session->set('HELPDESK_UNREADRESULT', $hd->result);
			$this->Session->set('HELPDESK_CATEGORIES', $cats);
			$this->Session->set('HELPDESK_USERNAME', $username);
			$this->Session->set('HELPDESK_BACKEND', $backend);
		} // if new search
		
		// url params
		$params = array('mine', $this->Input->get('mine'));
		$name = $this->Input->get('user');
		if ($name != '') {
			$params[] = 'user';
			$params[] = $name;
		} else {
			$name = $this->Input->get('member');
			if ($name != '') {
				$params[] = 'member';
				$params[] = $name;
			} // if
		} // if
		
		// create page array and navigation
		$hd->pagesize = $hd->settings->spage;
		if ($hd->pagesize > 0) {
			$hd->pages = intval(($hd->totrecs+$hd->pagesize-1)/$hd->pagesize);
			$hd->page = intval($this->Input->get(Helpdesk::pageParam()));
			if ($hd->page < 1) $hd->page = 1; else if ($hd->page > $hd->pages) $hd->page = $hd->pages;
			$hd->createPageNav($this->module, $params);
			$hd->pageResult = array();
			$r = ($hd->page-1) * $hd->pagesize;
			$n = 0;
			while ($r < $hd->totrecs && $n < $hd->pagesize) {
				$hd->pageResult[] = $hd->result[$r++];
				$n++;
			} // while
		} else {
			$hd->pages = $hd->page = 1;
			$hd->pageResult = &$hd->result;
		} // if
		
		// create links
		if ($hd->username!='')
			$hd->markReadLink = $this->module->createUrl('markread', 'current');
		$hd->listMineLink = $this->module->createUrl($params);
		if ($hd->page > 1) $hd->listMineLink = Helpdesk::addPage($hd->listMineLink, $hd->page);
		
		// setup breadcrumb
		$this->setBreadcrumb(array(
			array($hd->text['index'], $hd->listCategoriesLink),
			array($username, $hd->listMineLink)
		));
	} // listMine
	
	/**
	 * List the recent modified tickets
	 */
	private function listRecent()
	{
		$hd = &$this->template->helpdesk;
		$hd->pageResult = array();
		$db = &$this->Database;
		
		// get displayed category id's
		$cats = explode(',', $this->Input->get('recent'));
		if (in_array('all', $cats))
			$cats = $hd->backend ? array('all') : $this->module->allcategories();
		
		// check if cached
		$hd->result = $this->Session->get('HELPDESK_UNREADRESULT');
		if (is_array($hd->result) 
			&& $this->Session->get('HELPDESK_UNREADTYPE')=='recent' 
			&& $cats==$this->Session->get('HELPDESK_CATEGORIES')) {
			// results are (should be) allready in session
			$hd->totrecs = count($hd->result);
		} else {
			// new fetch
			$hd->result = array();
			$hd->totrecs = 0;
			
			// get the categories
			$qcat = $db
				->prepare(
					"\n select " . 
						HELPDESK_CATCOLS . ',' .
						"\n `cmk`.`message` as `cmk_message` " .
					"\n from `tl_helpdesk_categories` as `cat`" .
					"\n left join `tl_helpdesk_categorymarks` as `cmk`" .
						" on `cat`.`id`=`cmk`.`pid` and `cmk`.`username`=? and `cmk`.`backend`=?" .
					($hd->backend
						? ($hd->role==HELPDESK_ADMIN ? "" : "\n where `cat`.`published`='1'")
						: "\n where `cat`.`published`='1' and `cat`.`id` in (" . $this->implodeInt($cats,-1) . ")" 
					) .
					"\n order by `cat`.`sorting`, `cat`.`access`, `cat`.`title`"
				  )
				->execute($hd->username, $hd->backend);
			while ($qcat->next()) {
				$hd->authorize($qcat);
				if ($hd->hasCategoryAccess($qcat->cat_access)) {
					// setup category record
					$hd->category = $cat = new HelpdeskCategory($this->module, $hd, $qcat);
					$privateAccess = $cat->isPrivate && $hd->role<HELPDESK_SUPPORTER;
					if ($privateAccess) {
						$q = $db
							->prepare(
								"\n select" .
									"\n max(`msg`.`id`) as `latestmessage` " .
								"\n from `tl_helpdesk_tickets` as `tck`" .
									"\n left join `tl_helpdesk_messages` as `msg`" .
										" on `tck`.`id`=`msg`.`pid` and `msg`.`reply`='1' and `msg`.`published`='1'" .
								"\n where `tck`.`pid`=? and `tck`.`published`='1' and `tck`.`client`=? and `client_be`=?"
							  )
							->execute($cat->id, $hd->username, $hd->backend);
						if ($q->next())
							$cat->latestmessage = $q->latestmessage;
					} else
						if ($hd->role<HELPDESK_SUPPORTER) {
							$cat->latestmessage = $cat->pub_latest;
						} else {
							$cat->latestmessage = $cat->all_latest;
						} // if
					
					$tstamp = time() - $this->settings->recenthours * 60 * 60;
					$qtck = $db
						->prepare(
							"\n select" .
								HELPDESK_TCKCOLS.','.
								"\n ifnull(`tmk`.`message`,-1) as `tmk_message` " .
							"\n from `tl_helpdesk_tickets` as `tck`" .
								"\n left join `tl_helpdesk_ticketmarks` as `tmk`" .
									" on `tck`.`id`=`tmk`.`pid` and `tmk`.`username`=? and `tmk`.`backend`=?" .
							"\n where `tck`.`pid`=?" .
							"\n and (`tck`.`tstamp`>=? or " .
									 "0 < (select count(*) from `tl_helpdesk_messages` as `msg`".
											" where `msg`.`pid`=`tck`.`id`" .
											" and (`msg`.`edited`>=? or `msg`.`tstamp`>=?)" .
										  ")" .
									")"
						  )
						->execute(
							$hd->username, $hd->backend ? '1' : '0', $cat->id,
							$tstamp, $tstamp, $tstamp
						  );
					
					while ($qtck->next()) {
						if ($hd->hasTicketAccess($cat->access, $qtck)) {
							// setup ticket record
							$tck = new HelpdeskTicket($this->module, $hd, $qtck);
							
							if ($hd->role<HELPDESK_SUPPORTER) {
								$tck->replycount = $tck->pub_replies;
								$tck->latestmessage = $tck->pub_latest;
							} else {
								$tck->replycount = $tck->all_replies;
								$tck->latestmessage = $tck->all_latest;
							} // if

							$newrec = (object)array(
								'read'				=> $hd->username == '' ||
													   intval($qtck->tmk_message) >= intval($tck->latestmessage) ||
													   intval($qcat->cmk_message) >= intval($tck->latestmessage),
								'cat_id'			=> $cat->id,
								'cat_title'			=> $cat->title,
								'listTicketsLink'	=> $cat->listTicketsLink . $this->urlsep.'pageof=' . $tck->id,
								'tck_id'			=> $tck->id,
								'index'				=> $tck->index,
								'subject'			=> $tck->subject,
								'client'			=> $tck->client,
								'listMessagesLink'	=> $tck->listMessagesLink,
								'replycount'		=> $tck->replycount,
								'latestmessage'		=> $tck->latestmessage
							);
							
							// page links
							if ($tck->replycount > 0 && $hd->settings->mpage > 0) {
								$pages = ceil(($tck->replycount+1) / $hd->settings->mpage);
								if ($pages > 1) {
									$newrec->pageLinks[1] = $tck->listMessagesLink;
									for ($p = 2; $p <= $pages; $p++)
										$newrec->pageLinks[$p] = Helpdesk::addPage($tck->listMessagesLink, $p);
								} // if
							} // if
				
							// get latest post
							$q = $db
								->prepare("select `id`, `tstamp`, `poster` from `tl_helpdesk_messages` where `id`=?")
								->execute($tck->latestmessage);
							if ($q->next()) {
								$newrec->latestlink = $this->module->createUrl('message', $q->id);
								$newrec->latesttstamp = $hd->localDate($q->tstamp);
								$newrec->latestposter = sprintf($hd->text['postedby'], $q->poster);
							} // if

							// append new record 
							$hd->result[] = $newrec;
							$hd->totrecs++;
						} // if
					} // while
					
					// sort result
					if ($hd->totrecs > 1)
						usort($hd->result, array(&$this, 'compareUnreadRecs'));
				} // if
			} // while

			// cache result in session for subsequent paging
			$this->Session->set('HELPDESK_UNREADTYPE', 'recent');
			$this->Session->set('HELPDESK_UNREADRESULT', $hd->result);
			$this->Session->set('HELPDESK_CATEGORIES', $cats);
		} // if new search
		
		// url params
		$params = array('recent', $this->Input->get('recent'));
		
		// create page array and navigation
		$hd->pagesize = $hd->settings->spage;
		if ($hd->pagesize > 0) {
			$hd->pages = intval(($hd->totrecs+$hd->pagesize-1)/$hd->pagesize);
			$hd->page = intval($this->Input->get(Helpdesk::pageParam()));
			if ($hd->page < 1) $hd->page = 1; else if ($hd->page > $hd->pages) $hd->page = $hd->pages;
			$hd->createPageNav($this->module, $params);
			$hd->pageResult = array();
			$r = ($hd->page-1) * $hd->pagesize;
			$n = 0;
			while ($r < $hd->totrecs && $n < $hd->pagesize) {
				$hd->pageResult[] = $hd->result[$r++];
				$n++;
			} // while
		} else {
			$hd->pages = $hd->page = 1;
			$hd->pageResult = &$hd->result;
		} // if
		
		// create links
		if ($hd->username!='')
			$hd->markReadLink = $this->module->createUrl('markread', 'current');
		$hd->listRecentLink = $this->module->createUrl($params);
		if ($hd->page > 1) $hd->listRecentLink = Helpdesk::addPage($hd->listRecentLink, $hd->page);
		
		// setup breadcrumb
		$this->setBreadcrumb(array(
			array($hd->text['index'], $hd->listCategoriesLink),
			array($hd->text['recent'], $hd->listRecentLink)
		));
	} // listRecent
	
	/**
	 * List the unanswered tickets
	 */
	private function listUnanswered()
	{
		$hd = &$this->template->helpdesk;
		$hd->pageResult = array();
		$db = &$this->Database;
		
		// get displayed category id's
		$cats = explode(',', $this->Input->get('unanswered'));
		if (in_array('all', $cats))
			$cats = $hd->backend ? array('all') : $this->module->allcategories();
		
		// check if cached
		$hd->result = $this->Session->get('HELPDESK_UNREADRESULT');
		if (is_array($hd->result) 
			&& $this->Session->get('HELPDESK_UNREADTYPE')=='unanswered' 
			&& $cats==$this->Session->get('HELPDESK_CATEGORIES')) {
			// results are (should be) allready in session
			$hd->totrecs = count($hd->result);
		} else {
			// new fetch
			$hd->result = array();
			$hd->totrecs = 0;
			
			// get the categories
			$qcat = $db
				->prepare(
					"\n select " . 
						HELPDESK_CATCOLS . ',' .
						"\n `cmk`.`message` as `cmk_message` " .
					"\n from `tl_helpdesk_categories` as `cat`" .
					"\n left join `tl_helpdesk_categorymarks` as `cmk`" .
						" on `cat`.`id`=`cmk`.`pid` and `cmk`.`username`=? and `cmk`.`backend`=?" .
					($hd->backend
						? ($hd->role==HELPDESK_ADMIN ? "" : "\n where `cat`.`published`='1'")
						: "\n where `cat`.`published`='1' and `cat`.`id` in (" . $this->implodeInt($cats,-1) . ")" 
					) .
					"\n order by `cat`.`sorting`, `cat`.`access`, `cat`.`title`"
				  )
				->execute($hd->username, $hd->backend);
			while ($qcat->next()) {
				$hd->authorize($qcat);
				if ($hd->hasCategoryAccess($qcat->cat_access)) {
					// setup category record
					$hd->category = $cat = new HelpdeskCategory($this->module, $hd, $qcat);
					$privateAccess = $cat->isPrivate && $hd->role<HELPDESK_SUPPORTER;
					if ($privateAccess) {
						$q = $db
							->prepare(
								"\n select" .
									"\n max(`msg`.`id`) as `latestmessage` " .
								"\n from `tl_helpdesk_tickets` as `tck`" .
									"\n left join `tl_helpdesk_messages` as `msg`" .
										" on `tck`.`id`=`msg`.`pid` and `msg`.`reply`='1' and `msg`.`published`='1'" .
								"\n where `tck`.`pid`=? and `tck`.`published`='1' and `tck`.`client`=? and `client_be`=?"
							  )
							->execute($cat->id, $hd->username, $hd->backend);
						if ($q->next())
							$cat->latestmessage = $q->latestmessage;
					} else
						if ($hd->role<HELPDESK_SUPPORTER) {
							$cat->latestmessage = $cat->pub_latest;
						} else {
							$cat->latestmessage = $cat->all_latest;
						} // if
					
					$qtck = $db
						->prepare(
							"\n select" .
								HELPDESK_TCKCOLS.','.
								"\n ifnull(`tmk`.`message`,-1) as `tmk_message` " .
							"\n from `tl_helpdesk_tickets` as `tck`" .
								"\n left join `tl_helpdesk_ticketmarks` as `tmk`" .
									" on `tck`.`id`=`tmk`.`pid` and `tmk`.`username`=? and `tmk`.`backend`=?" .
							"\n where `tck`.`pid`=?" .
							"\n and 0 = (select count(*) from `tl_helpdesk_messages` as `msg`".
										"where `msg`.`pid`=`tck`.`id` and `msg`.`reply`='1')"
						  )
						->execute(
							$hd->username, $hd->backend ? '1' : '0', $cat->id
						  );
					
					while ($qtck->next()) {
						if ($hd->hasTicketAccess($cat->access, $qtck)) {
							// setup ticket record
							$tck = new HelpdeskTicket($this->module, $hd, $qtck);
							
							if ($hd->role<HELPDESK_SUPPORTER) {
								$tck->replycount = $tck->pub_replies;
								$tck->latestmessage = $tck->pub_latest;
							} else {
								$tck->replycount = $tck->all_replies;
								$tck->latestmessage = $tck->all_latest;
							} // if

							$newrec = (object)array(
								'read'				=> $hd->username == '' ||
													   intval($qtck->tmk_message) >= intval($tck->latestmessage) ||
													   intval($qcat->cmk_message) >= intval($tck->latestmessage),
								'cat_id'			=> $cat->id,
								'cat_title'			=> $cat->title,
								'listTicketsLink'	=> $cat->listTicketsLink . $this->urlsep.'pageof=' . $tck->id,
								'tck_id'			=> $tck->id,
								'index'				=> $tck->index,
								'subject'			=> $tck->subject,
								'client'			=> $tck->client,
								'listMessagesLink'	=> $tck->listMessagesLink,
								'replycount'		=> $tck->replycount,
								'latestmessage'		=> $tck->latestmessage
							);
							
							// page links
							if ($tck->replycount > 0 && $hd->settings->mpage > 0) {
								$pages = ceil(($tck->replycount+1) / $hd->settings->mpage);
								if ($pages > 1) {
									$newrec->pageLinks[1] = $tck->listMessagesLink;
									for ($p = 2; $p <= $pages; $p++)
										$newrec->pageLinks[$p] = Helpdesk::addPage($tck->listMessagesLink, $p);
								} // if
							} // if
				
							// get latest post
							$q = $db
								->prepare("select `id`, `tstamp`, `poster` from `tl_helpdesk_messages` where `id`=?")
								->execute($tck->latestmessage);
							if ($q->next()) {
								$newrec->latestlink = $this->module->createUrl('message', $q->id);
								$newrec->latesttstamp = $hd->localDate($q->tstamp);
								$newrec->latestposter = sprintf($hd->text['postedby'], $q->poster);
							} // if

							// append new record 
							$hd->result[] = $newrec;
							$hd->totrecs++;
						} // if
					} // while
					
					// sort result
					if ($hd->totrecs > 1)
						usort($hd->result, array(&$this, 'compareUnreadRecs'));
				} // if
			} // while

			// cache result in session for subsequent paging
			$this->Session->set('HELPDESK_UNREADTYPE', 'unanswered');
			$this->Session->set('HELPDESK_UNREADRESULT', $hd->result);
			$this->Session->set('HELPDESK_CATEGORIES', $cats);
		} // if new search
		
		// url params
		$params = array('unanswered', $this->Input->get('unanswered'));
		
		// create page array and navigation
		$hd->pagesize = $hd->settings->spage;
		if ($hd->pagesize > 0) {
			$hd->pages = intval(($hd->totrecs+$hd->pagesize-1)/$hd->pagesize);
			$hd->page = intval($this->Input->get(Helpdesk::pageParam()));
			if ($hd->page < 1) $hd->page = 1; else if ($hd->page > $hd->pages) $hd->page = $hd->pages;
			$hd->createPageNav($this->module, $params);
			$hd->pageResult = array();
			$r = ($hd->page-1) * $hd->pagesize;
			$n = 0;
			while ($r < $hd->totrecs && $n < $hd->pagesize) {
				$hd->pageResult[] = $hd->result[$r++];
				$n++;
			} // while
		} else {
			$hd->pages = $hd->page = 1;
			$hd->pageResult = &$hd->result;
		} // if
		
		// create links
		if ($hd->username!='')
			$hd->markReadLink = $this->module->createUrl('markread', 'current');
		$hd->listUnansweredLink = $this->module->createUrl($params);
		if ($hd->page > 1) $hd->listUnansweredLink = Helpdesk::addPage($hd->listUnansweredLink, $hd->page);
		
		// setup breadcrumb
		$this->setBreadcrumb(array(
			array($hd->text['index'], $hd->listCategoriesLink),
			array($hd->text['unanswered'], $hd->listUnansweredLink)
		));
	} // listUnanswered
	
	/**
	 * List the tickets of a distinct category
	 */
	private function listTickets()
	{
		$this->Session->set('HELPDESK_SEARCHRESULT', null);
		$this->Session->set('HELPDESK_UNREADRESULT', null);
		$this->compileCategoryId();
		$hd = &$this->template->helpdesk;
		$db = &$this->Database;
		$hd->tickets = array();
		if (is_object($hd->category)) {
			$cat = &$hd->category;
			
			// get category read
			$catread = -1;
			if (strlen($hd->username)) {
				$q = $db->prepare(
					"select `message` from `tl_helpdesk_categorymarks` ".
					"where `pid`=? and `username`=? and `backend`=?"
				)->execute($cat->id, $hd->username, $hd->backend ? '1' : '0');
				if ($q->next()) $catread = $q->message;
				$q = null;
			} // if
			
			// build clause
			$private = $cat->access==HELPDESK_PRIVATE_SUPPORT && $hd->role<HELPDESK_SUPPORTER;
			$clause = "\n where `tck`.`pid`=?";
			if ($hd->role<HELPDESK_SUPPORTER) $clause .= "\n and `tck`.`published`='1'";
			if ($private) $clause .= "\n and `tck`.`client`=? and `tck`.`client_be`=?";
			
			$hd->pagesize = $hd->settings->tpage;
			$hd->totrecs = 0;
			$hd->pages = $hd->page = 1;
			if ($hd->pagesize>0) {
				// get total record count
				$qcnt = $db->prepare(
					"\n select count(*) as `totrecs`" .
					"\n from `tl_helpdesk_tickets` as `tck`" .
					$clause
				);
				if ($private)
					$qcnt = $qcnt->execute($cat->id, $hd->username, $hd->backend ? '1' : '0');
				else
					$qcnt = $qcnt->execute($cat->id);
				if ($qcnt->next()) {
					$hd->totrecs = $qcnt->totrecs;
					$hd->pages = intval(($hd->totrecs+$hd->pagesize-1)/$hd->pagesize);
				} // if
				$qcnt = null;
			} // if
			
			// find the limits
			$limit = '';
			if ($hd->pages>1) {
				$found = false;
				$pageof = intval($this->Input->get('pageof'));
				if ($pageof>0) {
					// find ticket index
					$q = $db->prepare(
						"\n select `tck`.`id` as `id`" .
						"\n from `tl_helpdesk_tickets` as `tck`" .
						$clause.
						"\n order by `tck`.`status`, `tck`.`tstamp` desc, `tck`.`id` desc"
					);
					if ($private)
						$q = $q->execute($cat->id);
					else
						$q = $q->execute($cat->id, $hd->username, $hd->backend ? '1' : '0');
					$pos = 0;
					while ($q->next()) {
						if ($q->id == $pageof) {
							$hd->page = intval(($pos+$hd->pagesize)/$hd->pagesize);
							$found = true;
							break;
						} // if
						$pos++;
					} // if
					$q = null;
				} // if
				if (!$found) {
					$hd->page = intval($this->Input->get(Helpdesk::pageParam()));
					if ($hd->page < 1) $hd->page = 1; else if ($hd->page>$hd->pages) $hd->page=$hd->pages;
				} // if
				$limit="\n limit ".(($hd->page-1)*$hd->pagesize).','.$hd->pagesize;
			} // if
			
			// now get the records
			$qtck = $db->prepare(
				"\n select" . HELPDESK_TCKCOLS .
				"\n from `tl_helpdesk_tickets` as `tck`" .
				$clause .
				"\n order by `tck_status`, `tck_tstamp` desc, `tck_id` desc".
				$limit
			);
			if ($private)
				$qtck = $qtck->execute($cat->id, $hd->username, $hd->backend ? '1' : '0');
			else
				$qtck = $qtck->execute($cat->id);
			while ($qtck->next()) {
				if ($hd->hasTicketAccess($cat->access, $qtck)) {
					// setup ticket record
					$tck = new HelpdeskTicket($this->module, $hd, $qtck);
					
					if ($hd->role<HELPDESK_SUPPORTER) {
						$tck->replycount = $tck->pub_replies;
						$tck->latestmessage = $tck->pub_latest;
					} else {
						$tck->replycount = $tck->all_replies;
						$tck->latestmessage = $tck->all_latest;
					} // if
					
					if ($tck->replycount > 0 && $hd->settings->mpage > 0) {
						$pages = ceil(($tck->replycount+1) / $hd->settings->mpage);
						if ($pages > 1) {
							$tck->pageLinks[1] = $tck->listMessagesLink;
							for ($p = 2; $p <= $pages; $p++)
								$tck->pageLinks[$p] = Helpdesk::addPage($tck->listMessagesLink, $p);
						} // if
					} // if
						
					$tck->read = true;
					if (intval($tck->latestmessage)) {
						// get latest post
						$q = $db
							->prepare("select `id`, `tstamp`, `poster` from `tl_helpdesk_messages` where `id`=?")
							->execute($tck->latestmessage);
						if ($q->next()) {
							$tck->latestlink = $this->module->createUrl('message', $q->id);
							$tck->latesttstamp = $hd->localDate($q->tstamp);
							$tck->latestposter = sprintf($hd->text['postedby'], $q->poster);
						} // if
						
						// check for unread tickets
						if (strlen($hd->username) && $catread<$tck->latestmessage) {
							$q = $this->Database->prepare(
								"select max(`message`) as `maxreadmsg` from `tl_helpdesk_ticketmarks` ".
								"where `pid`=? and `username`=? and `backend`=?"
							)->execute($tck->id, $hd->username, $hd->backend ? '1' : '0');
							$tck->read = ($q->next() && $q->maxreadmsg>=$tck->latestmessage);
						} // if
					} // if
						
					$hd->tickets[] = $tck;
					if ($hd->pagesize==0) $hd->totrecs++;
				} // if
			} // while
			$qtck = null;
			
			// create navigation links
			$hd->createPageNav($this->module, array('category', $cat->id));
		
			// setup breadcrumb
			if ($hd->page>1) $cat->listTicketsLink = Helpdesk::addPage($cat->listTicketsLink, $hd->page);
			$this->setBreadcrumb(array(
				array($hd->text['index'], $hd->listCategoriesLink),
				array($cat->title, $cat->listTicketsLink)
			));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if			
	} // listTickets
	
	/**
	 * List the messages of a distinct ticket
	 */
	private function listMessages()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		$db = &$this->Database;
		$hd->messages = array();
		if (is_object($hd->ticket)) {
			$cat = &$hd->category;
			$tck = &$hd->ticket;
			
			$hd->pagesize = $hd->settings->mpage;
			$hd->totrecs = 0;
			$hd->pages = $hd->page = 1;
			if ($hd->pagesize>0) {
				// get total record count
				$qcnt = $this->Database->prepare(
					"select count(*) as `totrecs` from `tl_helpdesk_messages` where `pid`=?" . 
					($hd->role<HELPDESK_SUPPORTER ? " and `published`='1'" : "")
				)->execute($tck->id);
				if ($qcnt->next()) {
					$hd->totrecs = $qcnt->totrecs;
					$hd->pages = intval(($hd->totrecs+$hd->pagesize-1)/$hd->pagesize);
				} // if
				$qcnt = null;
			} // if
			
			// find the limits
			$limit = '';
			if ($hd->pages>1) {
				$found = false;
				if (is_null($this->pageof))
					$this->pageof = intval($this->Input->get('pageof'));
				if ($this->pageof>0) {
					// find message index
					$q = $db->prepare(
						"select `id`, `reply` from `tl_helpdesk_messages` where `pid`=?" . 
						($hd->role<HELPDESK_SUPPORTER ? " and `published`='1'" : "").
						" order by `reply`, `id`"
					)->execute($tck->id);
					$pos = 0;
					while ($q->next()) {
						if ($q->id == $this->pageof) {
							$hd->page = intval(($pos+$hd->pagesize)/$hd->pagesize);
							$found = true;
							break;
						} // if
						$pos++;
					} // if
					$q = null;
				} // if
				if (!$found) {
					$hd->page = intval($this->Input->get(Helpdesk::pageParam()));
					if ($hd->page < 1) $hd->page = 1; else if ($hd->page>$hd->pages) $hd->page=$hd->pages;
				} // if
				$limit="\n limit ".(($hd->page-1)*$hd->pagesize).','.$hd->pagesize;
			} // if
			
			// get all messages
			$q = $db->prepare(
				"\n select" . HELPDESK_MSGCOLS.
				"\n from `tl_helpdesk_messages` as `msg`" .
				"\n where `msg`.`pid`=?" . 
				($hd->role<HELPDESK_SUPPORTER ? "\n and `msg`.`published`='1'" : "") .
				"\n order by `msg_reply`, `msg_id`".
				$limit
			)->execute(intval($this->ident));
			$hd->totrecs = 0;
			while ($q->next()) {
				$hd->messages[] = new HelpdeskMessage($this->module, $hd, $q);
				$hd->totrecs++;
			} // while
			
			// count ticket view
			$db	->prepare("update `tl_helpdesk_tickets` set `views`=`views`+1 where `id`=?")
				->execute($tck->id);
			
			// create navigation links
			$hd->createPageNav($this->module, array('topic', $tck->id));

			// mark this ticket as read if loggged in
			if (strlen($hd->username)) {
				$latest = $hd->role<HELPDESK_SUPPORTER ? $tck->pub_latest : $tck->all_latest;
				// mark as read in db
				if ($this->markTicketRead($cat->id, $tck->id, $latest))
					$this->purgeTicketMarks($cat->id);
					
				// mark as read in unread cache
				$result = $this->Session->get('HELPDESK_UNREADRESULT');
				if (is_array($result))
					foreach ($result as &$rec)
						if ($rec->tck_id == $tck->id) {
							if (!$rec->read) {
								$rec->read = true;
								$this->Session->set('HELPDESK_UNREADRESULT', $result);
							} // if
							break;
						} // if
			} // if
								
		
			// setup breadcrumb
			$this->setBreadcrumb(array(
				array($hd->text['index'], $hd->listCategoriesLink),
				array($cat->title, $cat->listTicketsLink),
				array($tck->subject, $tck->listMessagesLink)
			));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // listMessages

	/**
	 * Create a new ticket
	 */
	private function createTicket()
	{
		$this->compileCategoryId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->category) && $hd->category->createTicketLink) {
			$cat = &$hd->category;
			
			// access ok, but is it return from submit?
			$action = $this->Input->post('helpdesk_action');
			if ($action == 'createTicket') {
				// retrieve posted data
				$client = $hd->username;
				$client_be = $hd->backend;
				$supporterTicket = $hd->role>=HELPDESK_SUPPORTER;
				if ($supporterTicket) {
					$c = trim($this->Input->post('helpdesk_client'));
					if ($c) {
						$client = trim(substr($c,4));
						$client_be = substr($c,0,3) == '[B]';
						$supporterTicket = false;
					} // if
				} // if
				$hd->subject = trim($this->Input->post('helpdesk_subject'));
				$hd->msgtext = $this->retrieveMessage();
				$hd->published = ($hd->role>=HELPDESK_SUPPORTER) ? (intval($this->Input->post('helpdesk_published'))>0) : true;
				$this->checkAttachments();
				
				// ok?
				if ($hd->subject!='' && $hd->msgtext!='' && is_null($hd->atcherrs)) {
					$this->postFloodCheck();
					
					// create ticket
					$ticketSet = array(
						'pid'			=> $cat->id,
						'tstamp'		=> time(),
						'client'		=> $client,
						'client_be'		=> $client_be,
						'supporter'		=> ($hd->role>=HELPDESK_SUPPORTER ? $hd->username : ''),
						'supporter_be'	=> $hd->backend,
						'subject'		=> $hd->subject,
						'status'		=> $cat->isSupport ? ($supporterTicket ? '1' : '0') : '1',
						'published'		=> $hd->published ? '1' : '0'
					);
					$objNewTicket = $this->Database->prepare("INSERT INTO `tl_helpdesk_tickets` %s")->set($ticketSet)->execute();
					$ticketId = $objNewTicket->insertId;
					
					// create message
					$messageSet = array(
						'pid'			=> $ticketId,
						'tstamp'		=> time(),
						'reply'			=> '0',
						'by_email'		=> '0',
						'poster'		=> $client,
						'poster_cd'		=> HelpdeskMessage::encodePosterCd($client_be, $supporterTicket),
						'message'		=> $hd->msgtext,
						'published'		=> $hd->published ? '1' : '0'
					);
					foreach ($hd->atchfiles as $file)
						$messageSet['atch'.$file['index'].'name'] = $file['name'];
					$objNewMessage = $this->Database->prepare("INSERT INTO `tl_helpdesk_messages` %s")->set($messageSet)->execute();
 					$messageId = $objNewMessage->insertId;
					$this->saveAttachments($messageId);

					// queue notification
					if (intval($cat->notify)) {
						$notifySet = array('pid' => $messageId);
						$this->Database->prepare("INSERT INTO `tl_helpdesk_notifys` %s")->set($notifySet)->execute();
					} // if

					// synchronize
					$this->settings->syncCat($cat->id);
					if ($client_be)
						$this->settings->syncUser($client);
					else
						$this->settings->syncMember($client);
					$this->settings->syncTotals();

					// redirect to ticket view
					$this->redirect($this->module->createUrl('topic', $ticketId));
				} else {
					if (!supporterTicket) {
						$hd->client = $client;
						$hd->clientBe = $client_be;
					} // if
					if ($hd->subject=='') $hd->subjectMissing = true;
					if ($hd->msgtext=='') $hd->messageMissing = true;
				} // if
			} // if
			
			// prepare form
			$this->prepareForm('create', $cat->createTicketLink, 'createticket');
			$hd->editSubject = true;
			if ($hd->role>=HELPDESK_SUPPORTER) $this->getClientOptions();
			
			// setup breadcrumb
			$this->setBreadcrumb(array(
				array($hd->text['index'], $hd->listCategoriesLink),
				array($cat->title, $cat->listTicketsLink),
				array($hd->text[createticket], $hd->category->createTicketLink)
			));
		} else {
			// no access: fallback to category
			$this->redirect($this->module->createUrl('category', intval($this->ident)));
		} // if
	} // createTicket
	
	/**
	 * Edit a message
	 */
	private function editMessage()
	{
		$this->compileMessageId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->message) && $hd->message->editMessageLink) {
			$cat = &$hd->category;
			$tck = &$hd->ticket;
			$msg = &$hd->message;
			
			// fill in defaults
			$hd->client		= $tck->client;
			$hd->clientBe	= $tck->client_be;
			$hd->subject	= $tck->subject;
			$hd->msgtext	= $msg->bbmessage;
			$hd->published	= $msg->published;
			$hd->attachment	= &$msg->attachment;
			$hd->editSubject = !$msg->reply && ($hd->role>=HELPDESK_SUPPORTER || $tck->isOwner);
			
			// access ok, but is it return from submit?
			$action = $this->Input->post('helpdesk_action');
			if ($action == $this->compiler) {
				// retrieve posted data
				if ($hd->editSubject) $hd->subject = trim($this->Input->post('helpdesk_subject'));
				$hd->msgtext = $this->retrieveMessage();
				$hd->published = $hd->role>=HELPDESK_SUPPORTER ? (intval($this->Input->post('helpdesk_published'))>0) : true;
				$this->checkAttachments();
				
				// ok?
				if ((!$hd->editSubject || $hd->subject!='') && $hd->msgtext!='' && is_null($hd->atcherrs)) {
					// update the ticket
					$ticketSet = array(
						'subject'	=> $hd->subject
					);
					$this->Database->prepare("UPDATE `tl_helpdesk_tickets` %s WHERE id=?")
						->set($ticketSet)
						->execute($tck->id);

					// update message
					$messageSet = array(
						'message'	=> $hd->msgtext,
						'published'	=> $hd->published ? '1' : '0'
					);
					$now = time();
					if ($hd->msgtext!=$msg->bbmessage && $this->settings->edits>0 && ($msg->tstampraw+$this->settings->editswait)<=$now) {
						if ($this->settings->edits>1 || $hd->role<HELPDESK_SUPPORTER) {
							$messageSet['edited'] = $now;
							$messageSet['editor'] = $hd->username;
							$messageSet['editor_cd'] = $hd->backend ? '1' : '0';
						} // if					
					} // if					
					foreach ($hd->atchfiles as $file)
						$messageSet['atch'.$file['index'].'name'] = $file['name'];
					$this->Database->prepare("UPDATE `tl_helpdesk_messages` %s WHERE id=?")
						->set($messageSet)
						->execute($msg->id);
					$this->saveAttachments($msg->id);
						
					// redirect to message view
					$this->redirect($this->module->createUrl('message', $msg->id));
				} else {
					if ($hd->editSubject && $hd->subject=='') $hd->subjectMissing = true;
					if ($hd->msgtext=='') $hd->messageMissing = true;
				} // if
			} // if
			
			// prepare form
			$this->prepareForm('edit', $msg->editMessageLink, 'updatemessage');
			
			// setup breadcrumb
			$this->setBreadcrumb(array(
				array($hd->text['index'], $hd->listCategoriesLink),
				array($cat->title, $cat->listTicketsLink . $this->urlsep.'pageof=' . $tck->id),
				array($tck->subject, $tck->listMessagesLink),
				array($hd->text['edit'], $msg->editMessageLink)
			));			
		} else {
			// no access: fallback to message viewing
			$this->redirect($this->module->createUrl('message', intval($this->ident)));
		} // if
	} // editMessage
	
	/**
	 * Quote a message
	 */
	private function quoteMessage()
	{
		$this->compileMessageId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->message) && $hd->message->quoteMessageLink) {
			$cat = &$hd->category;
			$tck = &$hd->ticket;
			$msg = &$hd->message;
			
			$hd->msgtext = 
				'[quote=' . $hd->message->poster . ']' . trim($hd->message->bbmessage) . '[/quote]';
		
			// access ok, but is it return from submit?
			if ($this->Input->post('helpdesk_action') == $this->compiler) 
				$this->evalReplySubmission();
			
			// prepare form
			$this->prepareReplyForm($hd->message->quoteMessageLink);
			
			// setup breadcrumb
			$this->setBreadcrumb(array(
				array($hd->text['index'], $hd->listCategoriesLink),
				array($cat->title, $cat->listTicketsLink . $this->urlsep.'pageof=' . $tck->id),
				array($tck->subject, $tck->listMessagesLink),
				array($hd->text['quote'], $msg->editMessageLink)
			));			
		} else {
			// no access: fallback to message viewing
			$this->redirect($this->module->createUrl('message', intval($this->ident)));
		} // if
	} // quoteMessage
	
	/**
	 * Reply to a ticket (add a message)
	 */
	private function replyTicket()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->ticket) && $hd->ticket->replyTicketLink) {
			$cat = &$hd->category;
			$tck = &$hd->ticket;
			
			// access ok, but is it return from submit?
			if ($this->Input->post('helpdesk_action') == $this->compiler)
				$this->evalReplySubmission();
	
			// prepare form
			$this->prepareReplyForm($hd->ticket->replyTicketLink);
			
			// setup breadcrumb
			$this->setBreadcrumb(array(
				array($hd->text['index'], $hd->listCategoriesLink),
				array($cat->title, $cat->listTicketsLink . $this->urlsep.'pageof=' . $tck->id),
				array($tck->subject, $tck->listMessagesLink),
				array($hd->text['reply'], $tck->replyTicketLink)
			));			
		} else {
			// no access: fallback to ticket viewing
			$this->redirect($this->module->createUrl('topic', intval($this->ident)));
		} // if
	} // replyTicket
	
	/**
	 * Common submission eval code for quoteMessage and replyTicket
	 */
	private function evalReplySubmission()
	{
		$hd = &$this->template->helpdesk;	
		$hd->msgtext = $this->retrieveMessage();
		$hd->published = $hd->role>=HELPDESK_SUPPORTER ? (intval($this->Input->post('helpdesk_published'))>0) : true;
		$this->checkAttachments();
		
		// ok?
		if ($hd->msgtext!='' && is_null($hd->atcherrs)) {
			$this->postFloodCheck();

			// update ticket
			$ticketSet = array('tstamp'	=> time());
			if ($hd->category->isSupport) $ticketSet['status'] = $hd->role>=HELPDESK_SUPPORTER ? '1' : '0';
			if ($hd->role>=HELPDESK_SUPPORTER) {
				$ticketSet['supporter'] = $hd->username;
				$ticketSet['supporter_be'] = $hd->backend;
			} // if
			$this->Database->prepare("UPDATE `tl_helpdesk_tickets` %s WHERE id=?")
				->set($ticketSet)
				->execute($hd->ticket->id);

			// create message
			$messageSet = array(
				'pid'		=> $hd->ticket->id,
				'tstamp'	=> time(),
				'reply'		=> '1',
				'by_email'	=> '0',
				'poster'	=> $hd->username,
				'poster_cd'	=> HelpdeskMessage::encodePosterCd($hd->backend, $hd->role>=HELPDESK_SUPPORTER),
				'message'	=> $hd->msgtext,
				'published'	=> $hd->published
			);
			foreach ($hd->atchfiles as $file)
				$messageSet['atch'.$file['index'].'name'] = $file['name'];
			$objNewMessage = $this->Database->prepare("INSERT INTO `tl_helpdesk_messages` %s")->set($messageSet)->execute();
			$messageId = $objNewMessage->insertId;
			$this->saveAttachments($messageId);
					
			// queue notification
			if (intval($hd->category->notify)) {
				$notifySet = array('pid' => $messageId);
				$this->Database->prepare("INSERT INTO `tl_helpdesk_notifys` %s")->set($notifySet)->execute();
			} // if
			
			// synchronize
			$this->settings->syncCat($hd->category->id);
			if ($hd->backend)
				$this->settings->syncUser($hd->username);
			else
				$this->settings->syncMember($hd->username);
			$this->settings->syncTotals();
			
			// redirect to message view
			$this->redirect($this->module->createUrl('message', $messageId));
		} else {
			if ($hd->msgtext=='') $hd->messageMissing = true;
		} // if
	} // evalReplySubmission
	
	/**
	 * Common form preparation code for quoteMessage and replyTicket
	 */
	private function prepareReplyForm(&$link)
	{
		$hd = &$this->template->helpdesk;	
		
		// prepare form
		$this->prepareForm('reply', $link, 'postreply');
			
		// get previous messages in reverse order
		$hd = &$this->template->helpdesk;	
		$q = $this->Database->prepare(
			"\n select" . HELPDESK_MSGCOLS.
			"\n from `tl_helpdesk_messages` as `msg`" .
			"\n where `msg`.`pid`=?" . 
			($hd->role<HELPDESK_SUPPORTER ? "\n and `msg`.`published`='1'" : "") .
			"\n order by `msg`.`reply` desc, `msg`.`id` desc"
		);
		$pagesize = $hd->settings->mpage;
		if ($pagesize>0) $q->limit($pagesize);
		$q = $q->execute($hd->ticket->id);
		$hd->messages = array();
		while ($q->next()) $hd->messages[] = new HelpdeskMessage($this->module, $hd, $q);
	} // prepareReplyForm
	
	/**
	 * Common edit form preparation code
	 */
	private function prepareForm($mode, &$link, $submitText)
	{
		$hd = &$this->template->helpdesk;	
		$hd->formMode = $mode;
		$hd->formLink = $link;
		$hd->formAction = $this->compiler;
		if ($hd->role>=HELPDESK_SUPPORTER) $hd->editPublished = true;
		$bbbuttons = new HelpdeskBbcodeButtons();
		$hd->editorButtons = 
			$bbbuttons->generate(
				'helpdesk_editform',
				'helpdesk_message',
				$hd->theme,
				$hd->category->buttons
			);
		$hd->submitText = &$hd->text[$submitText];
	} // prepareForm
	
	/**
	 * Retrieve message from post: Cannot use Input->post for the message, 
	 * because stripping chars is not acceptable.
	 */
	private function &retrieveMessage()
	{
		$message = html_entity_decode($_POST['helpdesk_message'], ENT_COMPAT, $GLOBALS['TL_CONFIG']['characterSet']);
		if (get_magic_quotes_gpc()) $message = stripslashes($message);
		return trim(str_replace("\r","",$message));
	} // retrieveMessage

	/**
	 * Check attachments from post 
	 */
	private function checkAttachments()
	{
		$hd = &$this->template->helpdesk;
		$cat = &$hd->category;
		$hd->atcherrs = null;
		$hd->atchfiles = array();
		if (!$cat->atch) return;
		for ($a = 1; $a <= 5; $a++) {
			if (is_null($_FILES['attachment'.$a])) {
				if (intval($this->Input->post('atchdelete'.$a))) {
					$hd->atchfiles[] = array(
						'index'		=>	$a,
						'name'		=>	''
					);
				} // if
				continue;
			} // if
			$atch = &$_FILES['attachment'.$a];
			$totsize = 0;
			switch ($atch['error']) {
				case UPLOAD_ERR_OK:
					// check type
					$parts = explode('.',$atch['name']);
					if (!in_array(strtolower(end($parts)), $cat->atch_types) && !in_array('*', $cat->atch_types)) {
						$hd->atcherrs .= sprintf('<div>'.$hd->text['filebadtype'].'</div>', $atch['name']);
					} else {
						$totsize += $atch['size'];
						$hd->atchfiles[] = array(
							'index'		=>	$a,
							'name'		=>	$atch['name'],
							'tmp_name'	=>	$atch['tmp_name']
						);
					} // if
					break;
				case UPLOAD_ERR_INI_SIZE:
					$hd->atcherrs .= sprintf('<div>'.$hd->text['filetobig'].'</div>', $atch['name']);
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$hd->atcherrs .= sprintf('<div>'.$hd->text['filetobig'].'</div>', $atch['name']);
					break;
				case UPLOAD_ERR_PARTIAL:
					$hd->atcherrs .= sprintf('<div>'.$hd->text['filepartial'].'</div>', $atch['name']);
					break;
				// case UPLOAD_ERR_NO_FILE:
				default:;
			} // switch
		} // for
		if ($totsize > $cat->atch_size)
			$hd->atcherrs .= '<div>'.$hd->text['atchtobig'].'</div>';
	} // checkAttachments

	/**
	 * Save attachment files 
	 */
	private function saveAttachments($messageId)
	{
		$hd = &$this->template->helpdesk;
		$cat = &$hd->category;
		$this->import('Files');
		// create directory (blind)
		$parts = explode('/',str_replace('\\', '/', $cat->atch_dir));
		$dir = '';
		$messageSet = array();
		foreach ($parts as $part)
			if (strlen($part)) {
				if (strlen($dir)) $dir .= '/';
				$dir .= $part;
				$this->Files->mkdir($dir);
			} // if
		foreach ($hd->atchfiles as $file) {
			$name = $messageId.'.'.$file['index'];
			$this->Files->delete($dir.'/'.$name);
			if (strlen($file['name'])) {
				$this->Files->move_uploaded_file($file['tmp_name'], $dir.'/'.$name); 
				$this->Files->chmod($dir.'/'.$name, 0644);
			} // if
			$messageSet['atch'.$file['index'].'name'] = $name;
		} // foreach
		return $messageSet;
	} // saveAttachments
	
	/**
	 * Goto a distinct message
	 */
	private function showMessage()
	{
		$q = $this->Database->prepare(
			"select `pid` from `tl_helpdesk_messages` where `id`=?"
		)->limit(1)->execute(intval($this->ident));
		if ($q->next()) {
			$this->template->helpdesk->target = intval($this->ident);
			$this->pageof = intval($this->ident);
			$this->ident = $q->pid;
			$this->listMessages();
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // showMessage
	
	/**
	 * Delete a ticket with all messages
	 */
	private function removeTicket()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->ticket) && $hd->ticket->removeTicketLink) {
		
			$this->Database->prepare(
				"delete from `tl_helpdesk_messages` where `pid`=?"
			)->execute($hd->ticket->id);
			
			$this->Database->prepare(
				"delete from `tl_helpdesk_tickets` where `id`=?"
			)->execute($hd->ticket->id);
			
			$this->Database->prepare(
				"delete from `tl_helpdesk_comments` where `ticket`=?"
			)->execute($hd->ticket->id);
			
			// synchronize
			$this->settings->syncCat($hd->category->id);
			$this->settings->syncAllMembers();
			$this->settings->syncAllUsers();
			$this->settings->syncTotals();
			
			$this->redirect($this->module->createUrl('category', $hd->category->id));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // removeTicket
	
	/**
	 * Close a ticket
	 */
	private function closeTicket()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->ticket) && $hd->ticket->closeTicketLink) {
			$this->Database->prepare(
				"update `tl_helpdesk_tickets` set `status`='2' where id=?"
			)->execute($hd->ticket->id);
			$this->redirect($this->getReferer(ENCODE_AMPERSANDS));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // closeTicket
	
	/**
	 * Open a ticket
	 */
	private function openTicket()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->ticket) && $hd->ticket->openTicketLink) {
			$this->Database->prepare(
				"update `tl_helpdesk_tickets` set `status`='1' where id=?"
			)->execute($hd->ticket->id);
			$this->redirect($this->getReferer(ENCODE_AMPERSANDS));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // openTicket

	/**
	 * Pinup a ticket
	 */
	private function pinupTicket()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->ticket) && $hd->ticket->pinupTicketLink) {
			$this->Database->prepare(
				"update `tl_helpdesk_tickets` set `status`='0' where id=?"
			)->execute($hd->ticket->id);
			$this->redirect($this->getReferer(ENCODE_AMPERSANDS));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // pinupTicket

	/**
	 * Unpin a ticket
	 */
	private function unpinTicket()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->ticket) && $hd->ticket->unpinTicketLink) {
			$this->Database->prepare(
				"update `tl_helpdesk_tickets` set `status`='1' where id=?"
			)->execute($hd->ticket->id);
			$this->redirect($this->getReferer(ENCODE_AMPERSANDS));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // unpinTicket

	/**
	 * Unpublish a ticket
	 */
	private function unpublishTicket()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->ticket) && 
			(	($hd->role>=HELPDESK_SUPPORTER && $hd->ticket->unpublishTicketLink) ||
				($hd->role<HELPDESK_SUPPORTER && $hd->ticket->removeTicketLink)	)	) {
				
			$this->Database->prepare(
				"update `tl_helpdesk_tickets` set `published`='0' where id=?"
			)->execute($hd->ticket->id);
			
			$this->Database->prepare(
				"update `tl_helpdesk_messages` set `published`='0' where `pid`=? and `reply`='0'"
			)->execute($hd->ticket->id);
			
			// synchronize
			$this->settings->syncCat($hd->category->id);
			
			$this->redirect($this->getReferer(ENCODE_AMPERSANDS));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // unpublishTicket
	
	/**
	 * Publish a ticket
	 */
	private function publishTicket()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->ticket) && $hd->ticket->publishTicketLink) {
		
			$this->Database->prepare(
				"update `tl_helpdesk_tickets` set `published`='1' where `id`=?"
			)->execute($hd->ticket->id);
			
			$this->Database->prepare(
				"update `tl_helpdesk_messages` set `published`='1' where `pid`=? and `reply`='0'"
			)->execute($hd->ticket->id);
			
			// synchronize
			$this->settings->syncCat($hd->category->id);
			
			$this->redirect($this->getReferer(ENCODE_AMPERSANDS));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // publishTicket
	
	/**
	 * Cut a ticket
	 */
	private function cutTicket()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->ticket) && $hd->ticket->cutTicketLink) {
			if (is_array($hd->clipboard) && $hd->clipboard['mode']=='cutticket')
				$hd->clipboard['id'][] = $hd->ticket->id;
			else
				$hd->clipboard = array('mode'=>'cutticket', 'id'=>array($hd->ticket->id));
			$this->Session->set('HELPDESK_CLIPBOARD', $hd->clipboard);
			$this->redirect($this->getReferer(ENCODE_AMPERSANDS));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // cutTicket
	
	/**
	 * Paste tickets or messages into a category
	 */
	private function pasteCategory()
	{
		$this->compileCategoryId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->category) && $hd->category->pasteLink) {
			$db = &$this->Database;
			if ($hd->clipboard['mode']=='cutticket') {
				// just move the tickets
				$db->prepare(
					"update `tl_helpdesk_tickets` set `pid`=? ".
					"where `id` in (" . $this->implodeInt($hd->clipboard['id'],-1) . ")"
				)->execute($hd->category->id);
			} else {
				// create a new ticket from first message
				$firstId = $hd->clipboard['id'][0];
				
				// fetch old ticket and message
				$q = $db->prepare(
					"\n select " .
						HELPDESK_MSGCOLS.','.
						HELPDESK_TCKCOLS.
					"\n from `tl_helpdesk_messages` as `msg`" .
						"\n inner join `tl_helpdesk_tickets` as `tck`" .
							" on `msg`.`pid`=`tck`.`id`" .
					"\n where `msg`.`id`=?"
				)->limit(1)->execute($firstId);
				
				if ($q->next()) {
					// create ticket
					$be = $sup = false;
					HelpdeskMessage::decodePosterCd(intval($q->msg_poster_cd), $be, $sup);
					$ticketSet = array(
						'pid'			=> $hd->category->id,
						'tstamp'		=> time(),
						'client'		=> $q->msg_poster,
						'client_be'		=> $be,
						'supporter'		=> $hd->username,
						'supporter_be'	=> $hd->backend,
						'subject'		=> '* '.$q->tck_subject,
						'status'		=> 1,
						'published'		=> $q->tck_published
					);
					$objNewTicket = $db->prepare("INSERT INTO `tl_helpdesk_tickets` %s")->set($ticketSet)->execute();
					$ticketId = $objNewTicket->insertId;
					
					// move the messages
					$db->prepare(
						"update `tl_helpdesk_messages` set `pid`=? ".
						"where `id` in (" . $this->implodeInt($hd->clipboard['id'],-1) . ")"
					)->execute($ticketId);
					
					// mark the op
					$db->prepare(
						"update `tl_helpdesk_messages` set `reply`=0 where `id`=?"
					)->execute($firstId);
				} // if			
			} // if
			
			// synchronize
			$this->settings->syncAllCats();
		} // if
		$this->Session->set('HELPDESK_CLIPBOARD', null);
		$this->redirect($this->getReferer(ENCODE_AMPERSANDS));
	} // pasteCategory
	
	/**
	 * Cut a message
	 */
	private function cutMessage()
	{
		$this->compileMessageId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->message) && $hd->message->cutMessageLink) {
			if (is_array($hd->clipboard) && $hd->clipboard['mode']=='cutmessage')
				$hd->clipboard['id'][] = $hd->message->id;
			else
				$hd->clipboard = array('mode'=>'cutmessage', 'id'=>array($hd->message->id));
			$this->Session->set('HELPDESK_CLIPBOARD', $hd->clipboard);
			$this->redirect($this->getReferer(ENCODE_AMPERSANDS));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // cutMessage
	
	/**
	 * Paste tickets or messages into a ticket
	 */
	private function pasteTicket()
	{
		$this->compileTicketId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->ticket) && $hd->ticket->pasteLink) {
			$db = &$this->Database;
			if ($hd->clipboard['mode']=='cutmessage') {
				// just move the messages
				$db->prepare(
					"update `tl_helpdesk_messages` set `pid`=? ".
					"where `id` in (" . $this->implodeInt($hd->clipboard['id'],-1) . ")"
				)->execute($hd->ticket->id);
			} else {
				// remove id of the current ticket
				$ids = array();
				foreach($hd->clipboard['id'] as $id) if ($id != $hd->ticket->id) $ids[] = $id;
				if (count($ids)) {
					$ids = $this->implodeInt($ids,-1);
					
					// move messages of all tickets
					$db->prepare(
						"update `tl_helpdesk_messages` set `pid`=?, `reply`=1 ".
						"where `pid` in (" . $ids . ")"
					)->execute($hd->ticket->id);
					
					// drop the old tickets
					$db->execute(
						"delete from `tl_helpdesk_tickets` ".
						"where `id` in (" . $ids . ") "
					);
					$db->execute(
						"delete from `tl_helpdesk_comments` ".
						"where `ticket` in (" . $ids . ") "
					);
				} // if
			} // if
			
			// synchronize
			$this->settings->syncAllCats();
		} // if
		$this->Session->set('HELPDESK_CLIPBOARD', null);
		$this->redirect($this->getReferer(ENCODE_AMPERSANDS));
	} // pasteTicket
	
	/**
	 * Delete a message
	 */
	private function deleteMessage()
	{
		$this->compileMessageId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->message) && $hd->message->deleteMessageLink) {
		
			$this->Database->prepare(
				"delete from `tl_helpdesk_messages` where `id`=?"
			)->execute($hd->message->id);
			
			// synchronize
			$this->settings->syncCat($hd->category->id);
			if ($hd->message->backend)
				$this->settings->syncUser($hd->message->poster);
			else
				$this->settings->syncMember($hd->message->poster);
			$this->settings->syncTotals();
			
			$this->redirect($this->module->createUrl('topic', $hd->ticket->id));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // deleteMessage
	
	/**
	 * Unpublish a message
	 */
	private function unpublishMessage()
	{
		$this->compileMessageId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->message) && ($hd->message->unpublishMessageLink || $hd->message->deleteMessageLink)) {
		
			$this->Database->prepare(
				"update `tl_helpdesk_messages` set `published`='0' where `id`=?"
			)->execute($hd->message->id);
			
			// synchronize
			$this->settings->syncCat($hd->category->id);
			
			$this->redirect($this->module->createUrl('topic', $hd->ticket->id));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // unpublishMessage
	
	/**
	 * Publish a message
	 */
	private function publishMessage()
	{
		$this->compileMessageId();
		$hd = &$this->template->helpdesk;
		if (is_object($hd->message) && $hd->message->publishMessageLink) {

			$this->Database->prepare(
				"update `tl_helpdesk_messages` set `published`='1' where `id`=?"
			)->execute($hd->message->id);
			
			// synchronize
			$this->settings->syncCat($hd->category->id);
			
			$this->redirect($this->module->createUrl('topic', $hd->ticket->id));
		} else {
			// no access: fallback to overview
			$this->redirect($hd->listCategoriesLink);
		} // if
	} // publishMessage
	
	
	/**
	 * Initialize read status for new users
	 */
	private function initReadStatus()
	{
		$hd = &$this->template->helpdesk;
		$q = $this->Database
			->prepare("select count(*) as `cnt` from `tl_helpdesk_categorymarks` where `username`=? and `backend`=?")
			->execute($hd->username, $hd->backend ? '1' : '0');
		if ($q->next() && $q->cnt==0)
			$this->markCategoryRead('all');
	} // initReadStatus
	
	/**
	 * Mark as read
	 */
	private function markRead()
	{
		$hd = &$this->template->helpdesk;
		if (!strlen($hd->username))	return;
		$param = $this->Input->get('markread');
		if ($param == 'current') {
			$result = $this->Session->get('HELPDESK_UNREADRESULT');
			if (is_array($result)) {
				$cat_ids = array();
				foreach ($result as $rec)
					if (!$rec->read)
						if ($this->markTicketRead($rec->cat_id, $rec->tck_id, $rec->latestmessage))
							if (!in_array($rec->cat_id, $cat_ids))
								$cat_ids[] = $rec->cat_id;
				foreach ($cat_ids as $cat_id)
					$this->purgeTicketMarks($cat_id);
			} // if
		} else
			$this->markCategoryRead($param);
		$this->redirect($hd->listCategoriesLink);
	} // markRead
	
	/**
	 * Mark categories as read
	 */
	private function markCategoryRead($param)
	{
		$hd = &$this->template->helpdesk;
		$backend = $hd->backend ? '1' : '0';
		$db = &$this->Database;
		
		// get categories
		$cats = explode(',', $param);
		if (in_array('all', $cats) && !$hd->backend) 
			$cats = $this->module->allcategories();
						
		// process the cats
		$qcat = $db
			->prepare(
				"\n select" .
				"\n `cat`.`id` as `cat_id`," .
				"\n `cat`.`pub_latest` as `cat_pub_latest`," .
				"\n `cat`.`all_latest` as `cat_all_latest`," .
				"\n ifnull(`cmk`.`message`,-1) as `cmk_message`," .
				"\n ifnull(`cmk`.`id`,-1) as `cmk_id`" .
				"\n from `tl_helpdesk_categories` AS `cat`" .
				"\n left join `tl_helpdesk_categorymarks` as `cmk`" .
					" on `cat`.`id`=`cmk`.`pid` and `cmk`.`username`=? and `cmk`.`backend`=?" .
				(in_array('all', $cats) ? '' : "\n where `cat`.`id` in (" . $this->implodeInt($cats,-1) . ")")
			  )
			->execute($hd->username, $backend);
		while ($qcat->next()) {
			$latest = intval($hd->role<HELPDESK_SUPPORTER ? $qcat->cat_pub_latest : $qcat->cat_all_latest);
			if ($latest > $qcat->cmk_message) {
				if ($qcat->cmk_id < 0)
					// create new category mark
					$db	->prepare("insert into `tl_helpdesk_categorymarks` %s")
						->set(array(
							'pid'		=> $qcat->cat_id,
							'username'	=> $hd->username,
							'backend'	=> $backend,
							'message'	=> $latest
						  ))
						->execute();
				else
					// update category mark
					$db	->prepare("update `tl_helpdesk_categorymarks` set `message`=? where `id`=?")
						->execute($latest, $qcat->cmk_id);
				
				// delete ticket marks
				$db	->prepare(
						"\n delete from `tl_helpdesk_ticketmarks` " .
						"\n where `username`=?" .
						"\n and `backend`=? " .
						"\n and `pid` in (select `id` from `tl_helpdesk_tickets` where `pid`=?)"
					  )
					->execute($hd->username, $backend, $qcat->cat_id);
			} // while
		} // while
	} // markCategoryRead
	
	/**
	 * Mark a ticket as read if loggged in
	 */
	private function markTicketRead($cat_id, $tck_id, $latest)
	{
		$hd = &$this->template->helpdesk;
		$db = &$this->Database;
		$backend = $hd->backend ? '1' : '0';
		$q = $db	
			->prepare(
				"select `message` from `tl_helpdesk_categorymarks` " .
				"where `pid`=? and `username`=? and `backend`=?"
			  )
			->execute($cat_id, $hd->username, $backend);
		$catmark = $q->next() ? $q->message : -1;	

		if ($latest > $catmark) {
			$q = $db
				->prepare(
					"select `id`, `message` from `tl_helpdesk_ticketmarks` " .
					"where `pid`=? and `username`=? and `backend`=?"
				  )
				->execute($tck_id, $hd->username, $backend);
			if ($q->next()) {
				if ($latest > $q->message) {
					$db	->prepare("update `tl_helpdesk_ticketmarks` set `message`=? where `id`=?")
						->execute($latest, $q->id);
					return true;
				} // if
			} else {
				$db	->prepare("insert into `tl_helpdesk_ticketmarks` %s")
					->set(array(
						'pid'		=> $tck_id,
						'username'	=> $hd->username,
						'backend'	=> $backend,
						'message'	=> $latest
					  ))
					->execute();
				return true;
			} // if
		} // if
		return false;
	} // markTicketRead

	/**
	 * Check if all tickets read, and mark category as read in case.
	 */
	private function purgeTicketMarks($cat_id)
	{
		$hd = &$this->template->helpdesk;
		$backend = $hd->backend ? '1' : '0';
		$prefix = $hd->role<HELPDESK_SUPPORTER ? 'pub' : 'all';
		$db = &$this->Database;
		
		// get category info
		$qcat = $db
			->prepare(
				"\n select" .
					"\n `cat`.`" . $prefix . "_latest` as `cat_latest`," .
					"\n ifnull(`cmk`.`id`,-1) as `cmk_id`," .
					"\n ifnull(`cmk`.`message`,-1) as `cmk_message`" .
				"\n from `tl_helpdesk_categories` as `cat`" .
				"\n left join `tl_helpdesk_categorymarks` as `cmk`" .
					" on `cat`.`id`=`cmk`.`pid` and `cmk`.`username`=? and `cmk`.`backend`=?" .
				"\n where `cat`.`id`=?"
			  )
			->execute($hd->username, $backend, $cat_id);
		if (!$qcat->next()) return;
		
		// get any tickets not yet read
		$qtck = $db
			->prepare(
				"\n select `tck`.`id` as `tck_id`" .
				"\n from `tl_helpdesk_tickets` as `tck`" .
				"\n left join `tl_helpdesk_ticketmarks` as `tmk`" .
					" on `tck`.`id`=`tmk`.`pid` and `tmk`.`username`=? and `tmk`.`backend`=?" .
				"\n where `tck`.`pid`=?" .
				"\n and `tck`.`" . $prefix . "_latest`>ifnull(`tmk`.`message`,-1)" .
				"\n and `tck`.`" . $prefix . "_latest`>?"
			  )
			->limit(1)
			->execute($hd->username, $backend, $cat_id, $qcat->cmk_message);

		if (!$qtck->next()) {
			if ($qcat->cmk_id < 0) {
				// create new category mark
				$db	->prepare("insert into `tl_helpdesk_categorymarks` %s")
					->set(array(
						'pid'		=> $cat_id,
						'username'	=> $hd->username,
						'backend'	=> $backend,
						'message'	=> $qcat->cat_latest
					  ))
					->execute();
			} else {
				// update existing mark
				$db	->prepare("update `tl_helpdesk_categorymarks` set `message`=? where `id`=?")
					->execute($qcat->cat_latest, $qcat->cmk_id);
			} // if
			
			// delete old ticket marks
			$db	->prepare(
					"delete from `tl_helpdesk_ticketmarks` " .
					"where `username`=? and `backend`=? " .
					"and `pid` in (select `id` from `tl_helpdesk_tickets` where `pid`=?)"
				  )
				->execute($hd->username, $backend, $cat_id);
		} // if
	} // purgeTicketMarks

	/**
	 * Compile when a category id was passed in
	 */
	private function compileCategoryId()
	{
		$hd = &$this->template->helpdesk;
		$q = $this->Database->prepare(
			"\n select" .
				HELPDESK_CATCOLS.
			"\n from `tl_helpdesk_categories` as `cat`" .
			"\n where `cat`.`id`=?" .
			  ($hd->role<HELPDESK_ADMIN ? "\n and `cat`.`published`='1'" : "")
		)->limit(1)->execute(intval($this->ident));
		if ($q->next()) {
			$hd->authorize($q);
			if ($hd->hasCategoryAccess($q->cat_access))
				$hd->category = new HelpdeskCategory($this->module, $hd, $q);
		} // if
	} // compileCategoryId

	/**
	 * Compile when a ticket id was passed in
	 */
	private function compileTicketId()
	{
		$hd = &$this->template->helpdesk;
		$q = $this->Database->prepare(
			"\n select" .
				HELPDESK_TCKCOLS.','.
				HELPDESK_CATCOLS.
			"\n from `tl_helpdesk_tickets` as `tck`" .
			"\n inner join `tl_helpdesk_categories` as `cat`" .
				" on `tck`.`pid`=`cat`.`id`" .
				($hd->role<HELPDESK_ADMIN ? " and `cat`.`published`='1'" : "") .
			"\n where `tck`.`id`=?"
		)->limit(1)->execute(intval($this->ident));
		if ($q->next()) {
			$hd->authorize($q);
			if ($hd->hasTicketAccess($q->cat_access, $q)) {
				$hd->category = new HelpdeskCategory($this->module, $hd, $q, true);
				$hd->ticket	= new HelpdeskTicket($this->module, $hd, $q);
			} // if
		} // if
	} // compileTicketId

	/**
	 * Compile when a message id was passed in
	 */
	private function compileMessageId()
	{
		$hd = &$this->template->helpdesk;
		$q = $this->Database->prepare(
			"\n select " .
				HELPDESK_MSGCOLS.','.
				HELPDESK_TCKCOLS.','.
				HELPDESK_CATCOLS.
			"\n from `tl_helpdesk_messages` as `msg`" .
				"\n inner join `tl_helpdesk_tickets` as `tck`" .
					" on `msg`.`pid`=`tck`.`id`" .
				"\n inner join `tl_helpdesk_categories` as `cat`" .
					" on `tck`.`pid`=`cat`.`id`" .
					($hd->role<HELPDESK_ADMIN ? " and `cat`.`published`='1'" : "") .
			"\n where `msg`.`id`=?"
		)->limit(1)->execute(intval($this->ident));
		if ($q->next()) {
			$hd->authorize($q);
			if ($hd->hasTicketAccess($q->cat_access, $q) && ($hd->role>=HELPDESK_SUPPORTER || intval($q->msg_published))) {
				$hd->category	= new HelpdeskCategory($this->module, $hd, $q, true);
				$hd->ticket		= new HelpdeskTicket($this->module, $hd, $q, true);
				$hd->message	= new HelpdeskMessage($this->module, $hd, $q);
			} // if
		} // if
	} // compileMessageId

	/**
	 * Get clients as options list
	 */
	private function getClientOptions()
	{
		$hd = &$this->template->helpdesk;
		$hd->clientOptions = array();
		
		if ($hd->client) 
			$hd->clientOption = $hd->clientBe ? '[B] '.$hd->client : '[F] '.$hd->client;

		// add backend clients
		$default = $hd->clientBe ? $hd->client : null;
		$q = $this->Database->execute(
			"SELECT `id`, `username`, `groups` FROM `tl_user` ORDER BY `username`"
		);
		while ($q->next()) {
			$g = $hd->unpackArray($q->groups);
			if ($hd->matchGroups($g, $hd->category->be_clients) &&
				!$hd->matchGroups($g, $hd->category->be_supporters)) {
				if ($default) {
					if (mb_strtolower($default) < mb_strtolower($q->username)) {
						$hd->clientOptions[] = '[B] '.$default;
						$default = null;
					} // if
					if ($default == $q->username) $default = null;
				} // if
				$hd->clientOptions[] = '[B] '.$q->username;
			} // if
		} // while
		if ($default) $hd->clientOptions[] = $default;

		// add frontend clients
		$default = $hd->clientBe ? null : $hd->client;
		$q = $this->Database->execute(
			"SELECT `id`, `username`, `groups` FROM `tl_member` ORDER BY `username`"
		);
		while ($q->next()) {
			$g = $hd->unpackArray($q->groups);
			if ($hd->matchGroups($g, $hd->category->fe_clients) &&
				!$hd->matchGroups($g, $hd->category->fe_supporters)) {
				if ($default) {
					if (mb_strtolower($default) < mb_strtolower($q->username)) {
						$hd->clientOptions[] = '[F] '.$default;
						$default = null;
					} // if
					if ($default == $q->username) $default = null;
				} // if
				$hd->clientOptions[] = '[F] '.$q->username;
			} // if
		} // while
		if ($default) $hd->clientOptions[] = $default;
	} // getClientOptions

	/**
	 * Test for flood/DOS protection
	 */
	private function floodCheck($aAction)
	{
		switch ($aAction) {
			case 'post':	$delay = $this->settings->postdelay; break;
			case 'search':	$delay = $this->settings->searchdelay; break;
			default: return true;
		} // switch
		$ok = true;
		$db = &$this->Database;
		$now = time();
		$lim = $now - $delay;
		$ip = $this->Environment->ip;
		if ($delay > 0) {
			// find existing record
			$q = $db
				->prepare("select `id`, `tstamp` from `tl_helpdesk_floodcontrol` where `ip`=? and `action`=?")
				->execute($ip, $aAction);
			if ($q->next()) {
				if ($q->tstamp >= $lim) $ok = false;
				// update time stamp
				$db	->prepare("update `tl_helpdesk_floodcontrol` set `tstamp`=? where `id`=?")
					->execute($now, $q->id);
			} else {
				// insert new record
				$db	->prepare("insert into `tl_helpdesk_floodcontrol` (`ip`, `action`, `tstamp`) values(?, ?, ?)")
					->execute($ip, $aAction, $now);
			} // if
		} // if
		
		// purge outdated records
		$db	->prepare("delete from `tl_helpdesk_floodcontrol` where `tstamp`<? and `action`=?")
			->execute($lim, $aAction);
		return $ok;
	} // floodCheck

	/**
	 * Test for post flood protection
	 */
	private function postFloodCheck()
	{
		if (!$this->floodCheck('post')) { 
			$hd = &$this->template->helpdesk;
			$this->Session->set(
				'HELPDESK_MESSAGE_TEXT', 
				sprintf($hd->text['warnpostflood'], $this->settings->postdelay)
			);
			$this->Session->set(
				'HELPDESK_MESSAGE_BUTTONS', 
				array(array(
					'link'	=> 'javascript:history.go(-1)',
					'label'	=> $hd->text['continue']
				))
			);
			$this->redirect($this->module->createUrl('show', 'warning'));
		} // if
	} // postFloodCheck

	/**
	 * Implode integer array into a sorted and comma separated list.
	 * Removes duplicates.
	 */
	private function implodeInt($aArr, $aDef = null)
	{
		$arr = array();
		foreach ($aArr as $id)
			if (is_numeric($id)) {
				$id = intval($id);
				if (!in_array($id, $arr)) $arr[] = $id;
			} // if
		if (count($arr)>0) 
			sort($arr);
		else
			if (is_int($aDef))
				$arr[] = $aDef;
		return implode(',',$arr);
	} // inmplodeInt

	/**
	 * Set the breadcrumb
	 */
	private function setBreadcrumb($aItems = null)
	{
		$hd = &$this->template->helpdesk;
		$hd->breadcrumb = '';
		$n = count($aItems);
		foreach ($aItems as $i => $item) {
			list($text, $link) = $item;
			if ($i > 0) $hd->breadcrumb .= ' &gt; ';
			if ($link != '' && $i < $n-1)
				$hd->breadcrumb .= '<a href="'.$link.'">'.$text.'</a>';
			else
				$hd->breadcrumb .= '<span>'.$text.'</span>';
		} // for
	} // makeBreadcrumb

} // class HelpdeskController

?>
