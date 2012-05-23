<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Class HelpdeskImport
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

define('TL_MODE', 'BE');
require_once('../../initialize.php');
require_once('HelpdeskConstants.php');

class HelpdeskImport extends Backend
{
	private $loglevel;
	protected $settings;

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
			$lines = preg_split("\n", str_replace("\r", "", $message));
			foreach ($lines as $line) 
				error_log(
					sprintf("[%s] %s%s\n", date('Y-m-d H:i:s'), str_repeat('  ', $this->loglevel), $line), 
					3, 
					TL_ROOT . '/system/logs/HelpdeskImport.log'
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
		
		if (!function_exists('imap_open')) {
		    $this->hlog(
				HELPDESK_BRIEFLOG,
				"Error: IMAP functions are not available.\n" .
				"The IMAP function are required for importing email."
			);
			$this->log(
				'IMAP functions are not available.<br/>The IMAP function are required for importing email.', 
				'HelpdeskImport run()', 
				TL_ERROR
			);
			return;
		} // if
		
		$this->hlog(HELPDESK_DEBUGLOG, '+run', 1);
		
		ob_start();
		$e = error_reporting(E_ALL);
		$qcat = $this->Database->prepare(
			"\n select " .
				HELPDESK_CATCOLS .
			"\n from `tl_helpdesk_categories` as `cat`" .
			"\n where `import`='1'" .
			"\n order by `cat`.`access`, `cat`.`title`"
		)->execute();
		while ($qcat->next()) {
			if (time() >= $cronJob['endtime']) {
				$cronJob['completed'] = false;
				$this->hlog(HELPDESK_DEBUGLOG, 'Processing suspended because of time limit.');
				$this->hlog(HELPDESK_DEBUGLOG, '-run', -1);
				return;
			} // if
			$this->importCategory($qcat);
		} // while
		error_reporting($e);
		$output = trim(preg_replace('#<\s*br\s*/?\s*>#i', "\n", ob_get_flush()));
		if ($output != '') $this->hlog(HELPDESK_BRIEFLOG, $output);
		$this->hlog(HELPDESK_DEBUGLOG, '-run', -1);
	} // run
	
	private function importCategory(&$qcat)
	{
		global  $cronJob;
		
		// connect to mailbox
		$this->hlog(HELPDESK_DEBUGLOG, '+importCategory: '.$qcat->cat_title, 1);
		$mx = new HelpdeskMailbox();
		$mx->server		= $qcat->cat_import_server;
		if (intval($qcat->cat_import_port)) $mx->port = $qcat->cat_import_port;
		$mx->type		= intval($qcat->cat_import_type) ? 'imap' : 'pop';
		$mx->username	= $qcat->cat_import_username;
		$mx->password	= $qcat->cat_import_password;
		switch (intval($qcat->cat_import_tls)) {
			case 6:	$mx->tls = 'ssl_nocert'; break;
			case 5: $mx->tls = 'ssl_cert'; break;
			case 4: $mx->tls = 'tls_nocert'; break;
			case 3: $mx->tls = 'tls_cert'; break;
			case 2: $mx->tls = 'enable_nocert'; break;
			case 1: $mx->tls = 'enable_cert'; break;
			default: $mx->tls = 'disable';
		} // switch
		if (!$mx->open()) {
			$this->log(
				'Connect to mailbox for category <em>'.$qcat->cat_title.'</em> failed.',
				'HelpdeskImport run()', TL_ERROR);
			$this->hlog(HELPDESK_BRIEFLOG, 'Connect to mailbox for category '.$qcat->cat_title.' failed.');
			$this->hlog(HELPDESK_DEBUGLOG, '-importCategory: '.$qcat->cat_title, -1);
			return;
		} // if
		
		// loop through the available mails
		$count = $mx->count();
		for ($m = 1; $m <= $count; $m++) { 
			if (time() >= $cronJob['endtime']) break;
			
			$header = $mx->header($m);
			
			// skip deleted mails
			if ($header->Deleted == 'D') continue;
			
			// skip mails not addressed to this category
			if ($qcat->cat_import_email!='') {
				$ok = false;
				if (is_array($header->to))
					foreach ($header->to as $to) {
						$addr = $to->mailbox.'@'.$to->host;
						if ($qcat->cat_import_email == $addr) {
							$ok = true;
							break;
						} else
							$this->hlog(HELPDESK_DEBUGLOG, "To address not matched: $addr");
					} // foreach
				if (!$ok) continue;
			} // if
				
			// purge mails without a sender
			if (!is_array($header->from)) {
				$mx->delete($m);
				$this->log('Purged email where sender name was missing.', 'HelpdeskImport run()', TL_ERROR);
				$this->hlog(HELPDESK_BRIEFLOG, 'Purged email where sender name was missing.');
				continue;
			} // if
			
			// new ticker or reply?
			$qtck = null;
			$pattern = '~\[\s*replyto\s*#\s*(\d+)\s*\]~i';
			if (preg_match($pattern, $header->subject, $matches)) {
				$qtck = $this->Database->prepare(
					"\n select " .
						HELPDESK_TCKCOLS .
					"\n from `tl_helpdesk_tickets` as `tck`" .
					"\n where `tck`.`id`=?"
				)->execute($matches[1]);
				if (!$qtck->next()) { // ticket was deleted
					$mx->delete($m);
					$this->log(
						'Purged email reply to #'.$matches[1].' because topic/ticket was not found.',
						'HelpdeskImport run()', TL_ERROR);
					$this->hlog(HELPDESK_BRIEFLOG,
						'Purged email reply to #'.$matches[1].' because topic/ticket was not found.');
					continue;
				} // if
				$header->subject = preg_replace($pattern, '', $header->subject);
			} // if
			
			// get sender email address(es)
			$username = null;
			$email = null;
			$backend = $supporter = false;
			foreach ($header->from as $f) {
				$email = $f->mailbox.'@'.$f->host;
				
				// try frontend members first
				$backend = false;
				$username = $this->findMember($qcat, $qtck, $email, $supporter);			
				if ($username) { $backend = false; break; }
				
				// no frontend member found, so try backend users
				$backend = true;
				$username = $this->findUser($qcat, $qtck, $email, $supporter);
				if ($username) { $backend = true; break; }
			} // foreach

			$content = $mx->content($m, $qcat->cat_atch_size, $qcat->cat_atch_types);
			if ($username) {
				if ($qtck) 
					$this->replyTicket($qcat, $qtck, $header, $content, $username, $backend, $supporter);
				else
					$this->createTicket($qcat, $header, $content, $username, $backend, $supporter);
			} else {
				$this->log(
					"Purged email from $email: not authorized for category <em>$qcat->cat_title</em>.",
					'HelpdeskImport run()', TL_ERROR);
				$this->hlog(HELPDESK_BRIEFLOG,
					"Purged email from $email: not authorized for category $qcat->cat_title.");
			} // if
			$mx->delete($m);
		} // for
		$mx->close();
		$this->hlog(HELPDESK_DEBUGLOG, '-importCategory: '.$qcat->cat_title, -1);
	} // importCategory

	/**
	 * Find frontend member with this email and access to this category/ticket
	 */
	private function findMember(&$qcat, &$qtck, $email, &$supporter)
	{
		$this->hlog(HELPDESK_DEBUGLOG, '+findMember: '.$email, 1);
		$cli = Helpdesk::unpackArray($qcat->cat_fe_clients);
		$sup = Helpdesk::unpackArray($qcat->cat_fe_supporters);
		if (!count($cli) && !count($sup)) {
			$this->hlog(HELPDESK_DEBUGLOG, '-findMember: no frontend client or supporter groups', -1);
			return null;
		} // if
		$q = $this->Database->prepare(
			"\n select `username`, `groups`" .
			"\n from `tl_member`" .
			"\n where lower(`email`)=lower(?)" .
			"\n order by `username`"
		)->execute($email);
		while ($q->next()) {
			$g = Helpdesk::unpackArray($q->groups);
			if (Helpdesk::matchGroups($g, $sup)) { 
				$supporter = true; 
				$this->hlog(HELPDESK_DEBUGLOG, '-findMember: supporter '.$q->username, -1);
				return $q->username; 
			} // if
			if ($qcat->cat_published && Helpdesk::matchGroups($g, $cli)) {
				$supporter = false;
				if (!$qtck) {
					$this->hlog(HELPDESK_DEBUGLOG, '-findMember: '.$q->username, -1);
					return $q->username;
				} // if
				if (intval($qtck->tck_published)) {
					if (intval($qcat->cat_access)!=HELPDESK_PRIVATE_SUPPORT) {
						$this->hlog(HELPDESK_DEBUGLOG, '-findMember: '.$q->username, -1);
						return $q->username;
					} // if
					if ($qtck->tck_client==$q->username && !intval($qtck->tck_client_be)) { 
						$this->hlog(HELPDESK_DEBUGLOG, '-findMember: '.$q->username, -1);
						return $q->username;
					} // if
				} // if
			} // if
		} // while	
		$this->hlog(HELPDESK_DEBUGLOG, '-findMember: not found', -1);
		return null;
	} // findMember
	
	/**
	 * Find backend user with this email and access to this category
	 */
	private function findUser(&$qcat, &$qtck, $email, &$supporter)
	{
		$this->hlog(HELPDESK_DEBUGLOG, '+findUser: '.$email, 1);
		$cli = Helpdesk::unpackArray($qcat->cat_be_clients);
		$sup = Helpdesk::unpackArray($qcat->cat_be_supporters);
		if (!count($cli) && !count($sup)) {
			$this->hlog(HELPDESK_DEBUGLOG, '-findUser: no backend client or supporter groups', -1);
			return null;
		} // if
		$q = $this->Database->prepare(
			"\n select `username`, `admin`, `groups`" .
			"\n from `tl_user`" .
			"\n where lower(`email`)=lower(?)" .
			"\n order by `admin`, `username`" // admin as last
		)->execute($email);
		while ($q->next()) {
			if (intval($q->admin)) { 
				$supporter = true; 
				$this->hlog(HELPDESK_DEBUGLOG, '-findUser: supporter '.$q->username, -1);
				return $q->username; 
			} // if
			$g = Helpdesk::unpackArray($q->groups);
			if (Helpdesk::matchGroups($g, $sup)) { 
				$supporter = true; 
				$this->hlog(HELPDESK_DEBUGLOG, '-findUser: supporter '.$q->username, -1);
				return $q->username; 
			} // if
			if ($qcat->cat_published && Helpdesk::matchGroups($g, $cli)) {
				$supporter = false;
				if (!$qtck) {
					$this->hlog(HELPDESK_DEBUGLOG, '-findUser: '.$q->username, -1);
					return $q->username;
				} // if
				if (intval($qtck->tck_published)) {
					if (intval($qcat->cat_access)!=HELPDESK_PRIVATE_SUPPORT) {
						$this->hlog(HELPDESK_DEBUGLOG, '-findUser: '.$q->username, -1);
						return $q->username;
					} // if
					if ($qtck->tck_client==$q->username && intval($qtck->tck_client_be)) {
						$this->hlog(HELPDESK_DEBUGLOG, '-findUser: '.$q->username, -1);
						return $q->username;
					} // if
				} // if
			} // if
		} // while	
		$this->hlog(HELPDESK_DEBUGLOG, '-findUser: not found', -1);
		return null;
	} // findUser
	
	/**
	 * Create a new ticket
	 */
	private function createTicket(&$qcat, &$header, &$content, $username, $backend, $supporter)
	{
		global  $cronJob;
		
		$this->hlog(HELPDESK_DEBUGLOG, '+createTicket', 1);
		// create ticket
		$subject = strip_tags($header->subject);
		$ticketSet = array(
			'pid'			=> $qcat->cat_id,
			'tstamp'		=> time(),
			'client'		=> $username,
			'client_be'		=> $backend ? '1' : '0',
			'supporter'		=> $supporter ? $username : '',
			'supporter_be'	=> $backend ? '1' : '0',
			'subject'		=> $subject,
			'status'		=> intval($qcat->cat_access)<=HELPDESK_PUBLIC_SUPPORT ? ($supporter ? '1' : '0') : '1',
			'published'		=> '1'
		);
		$objNewTicket = $this->Database->prepare("INSERT INTO `tl_helpdesk_tickets` %s")->set($ticketSet)->execute();
		$ticketId = $objNewTicket->insertId;
		
		$this->createMessage($qcat, $content, $ticketId, '0', $username, $backend, $supporter);
		
		// synchronize
		$this->settings->syncCat($qcat->cat_id);
		if ($backend)
			$this->settings->syncUser($username);
		else
			$this->settings->syncMember($username);
		$this->settings->syncTotals();				
		
		if ($cronJob['logging']) {
			$what = intval($qcat->cat_access)<=HELPDESK_SUPPORT ? 'ticket' : 'topic';
			$this->log(
				"Created new $what <em>$subject</em> for <em>$username</em> in <em>$qcat->cat_title</em>.",
				'HelpdeskImport run()', TL_GENERAL);
			$this->hlog(HELPDESK_BRIEFLOG,
				"Created new $what $subject for $username in $qcat->cat_title.");
		} // if
		$this->hlog(HELPDESK_DEBUGLOG, '-createTicket', -1);
	} // createTicket

	/**
	 * Reply to a ticket
	 */
	private function replyTicket(&$qcat, &$qtck, &$header, &$content, $username, $backend, $supporter)
	{
		global  $cronJob;
		
		$this->hlog(HELPDESK_DEBUGLOG, '+replyTicket('.$qcat->cat_title.','.$header->subject.','.$username.')', 1);
		// update ticket
		$ticketSet = array('tstamp'	=> time());
		if (intval($qcat->cat_access)<=HELPDESK_PUBLIC_SUPPORT) 
			$ticketSet['status'] = $supporter ? '1' : '0';
		if ($supporter) {
			$ticketSet['supporter'] = $username;
			$ticketSet['supporter_be'] = $backend ? '1' : '0';
		} // if
		$this->Database->prepare("UPDATE `tl_helpdesk_tickets` %s WHERE id=?")
			->set($ticketSet)
			->execute($qtck->tck_id);

		$this->createMessage($qcat, $content, $qtck->tck_id, '1', $username, $backend, $supporter);
		
		// synchronize
		$this->settings->syncCat($qcat->cat_id);
		if ($backend)
			$this->settings->syncUser($username);
		else
			$this->settings->syncMember($username);
		$this->settings->syncTotals();	
		
		$subject = strip_tags($header->subject);
		if ($cronJob['logging'])
			$this->log(
				"Created reply to <em>$subject</em> for <em>$username</em> in <em>$qcat->cat_title</em>.",
				'HelpdeskImport run()', TL_GENERAL);
		$this->hlog(HELPDESK_DEBUGLOG, '-replyTicket', -1);
	} // replyTicket

	/**
	 * Create new message
	 */
	private function createMessage(&$qcat, &$content, $ticketId, $reply, $username, $backend, $supporter)
	{
		$this->hlog(HELPDESK_DEBUGLOG, '+createMessage', 1);
		// create message
		$messageSet = array(
			'pid'		=> $ticketId,
			'tstamp'	=> time(),
			'reply'		=> $reply,
			'by_email'	=> '1',
			'poster'	=> $username,
			'poster_cd'	=> $supporter 
								? ($backend ? '3' : '2') 
								: ($backend ? '1' : '0'),
			'message'	=> $this->parseMessage($content),
			'published'	=> '1'
		);
		if (intval($qrec->cat_import_atch)) 
			$this->addAttachments($content, $messageSet);
		$objNewMessage = $this->Database->prepare("INSERT INTO `tl_helpdesk_messages` %s")->set($messageSet)->execute();
		$messageId = $objNewMessage->insertId;
		if (intval($qrec->cat_import_atch)) 
			$this->saveAttachments($qcat, $content, $messageId);

		// queue notification
		if (intval($qcat->cat_notify))
			$this->Database->prepare("INSERT INTO `tl_helpdesk_notifys` %s")
				->set(array('pid' => $messageId))
				->execute();
		
		$this->markTicketRead($ticketId, $messageId, $username, $backend);
		$this->hlog(HELPDESK_DEBUGLOG, '-createMessage', -1);
	} // createMessage

	/**
	 * Add attachment names to message record set
	 */
	private function addAttachments(&$content, &$messageSet)
	{
		$this->hlog(HELPDESK_DEBUGLOG, '+addAttachments', 1);
		$cnt = count($content['attachments']);
		$id = 1;
		for ($a = 0; $a < $cnt && $id <= 5; $a++) {
			$messageSet['atch'.$id.'name'] = $content['attachments'][$a]['filename'];
			$id++;
		} // for
		$this->hlog(HELPDESK_DEBUGLOG, '-addAttachments', -1);
	} // addAttachments
	
	private function saveAttachments(&$qcat, &$content, $messageId)
	{
		$this->hlog(HELPDESK_DEBUGLOG, '+saveAttachments', 1);
		$this->import('Files');
		$files = &$this->Files;
		$parts = explode('/',str_replace('\\', '/', $qcat->cat_atch_dir));
		$dir = '';
		$messageSet = array();
		foreach ($parts as $part)
			if (strlen($part)) {
				if (strlen($dir)) $dir .= '/';
				$dir .= $part;
				$this->Files->mkdir($dir);
			} // if
		$cnt = count($content['attachments']);
		for ($a = 0; $a < $cnt && $a < 5; $a++) {
			$name = $messageId.'.'.($a+1);
			$files->delete($dir.'/'.$name);
			$fh = $files->fopen($dir.'/'.$name, 'wb');
			$files->fputs($fh, $content['attachments'][$a]['data']);
			$files->fclose($fh);
			$files->chmod($dir.'/'.$name, 0644);
		} // for
		$this->hlog(HELPDESK_DEBUGLOG, '-saveAttachments', -1);
	} // saveAttachments
	
	private function parseMessage(&$content)
	{
		$this->hlog(HELPDESK_DEBUGLOG, '+parseMessage', 1);
		if ($content['html']) {
			// html: remove all cr; remove all lf EXCEPT between pre tags
			$src = str_replace("\r", '', $content['html']);
			$text = '';
			while ($src) {
				if (preg_match('#<\s*pre(\s*|\s+[^>]*)>#i', $src, $matches, PREG_OFFSET_CAPTURE)) {
					$offs = $matches[0][1];
					$text .= str_replace("\n", '', substr($src, 0, $offs));
					$src = substr($src, $offs);
					if (preg_match('#<\s*/\s*pre\s*>#i', $src, $matches, PREG_OFFSET_CAPTURE)) {
						$offs = $matches[0][1]+strlen($matches[0][0]);
						$text .= substr($src, 0, $offs);
						$src = substr($src, $offs);
					} else {
						$text .= $src;
						$src = '';
					} // if
				} else {
					$text .= str_replace("\n", '', $src);
					$src = '';
				} // if
			} // while
		} else
			$text = $content['plain'];

		// replace some html tags
		$text = preg_replace(
			array(
				'#&nbsp;#',

				'#<\s*br\s*/?\s*>#i', 
				'#<\s*div(\s*|\s+[^>]*)>#i',
				'#<\s*p(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*p\s*>#i',

				'#<\s*h1(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*h1\s*>#i', 

				'#<\s*h2(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*h2\s*>#i', 

				'#<\s*h3(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*h3\s*>#i', 

				'#<\s*h4(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*h4\s*>#i', 
				
				'#<\s*h5(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*h5\s*>#i', 

				'#<\s*blockquote(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*blockquote\s*>#i', 

				'#<\s*pre(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*pre\s*>#i', 

				'#<\s*b(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*b\s*>#i',

				'#<\s*strong(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*strong\s*>#i', 

				'#<\s*em(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*em\s*>#i', 

				'#<\s*i(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*i\s*>#i',

				'#<\s*u(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*u\s*>#i', 

				'#<\s*ol(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*ol\s*>#i',

				'#<\s*ul(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*ul\s*>#i',

				'#<\s*li(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*li\s*>#i',

				'#<\s*table(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*table\s*>#i',

				'#<\s*tr(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*tr\s*>#i',

				'#<\s*th(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*th\s*>#i',

				'#<\s*td(\s*|\s+[^>]*)>#i',
				'#<\s*/\s*td\s*>#i'
			),
			array(
				" ",

				"\n",
				"\n",
				"",
				"\n",

				"\n[h1]",
				"[/h1]\n",

				"\n[h2]",
				"[/h2]\n",

				"\n[h3]",
				"[/h3]\n",

				"\n[h4]",
				"[/h4]\n",

				"\n[h5]",
				"[/h5]\n",

				"\n[box]",
				"[/box]\n",

				"\n[pre]",
				"[/pre]\n",

				"[b]",
				"[/b]",

				"\n[b]",
				"[/b]\n",

				"[i]",
				"[/i]",

				"[i]",
				"[/i]",

				"[u]",
				"[/u]",

				"\n[list=1]",
				"\n[/list]\n",

				"\n[list]",
				"\n[/list]\n",

				"\n[li]",
				"[/li]",

				"\n[table=1]",
				"\n[/table]\n",

				"\n[tr]",
				" [/tr]",

				" [th]",
				"[/th]",

				" [td]",
				"[/td]"
			),
			$text
		);
		
		// remove residual html tags
		$this->hlog(HELPDESK_DEBUGLOG, '-parseMessage', -1);
		return htmlspecialchars_decode(strip_tags($text),ENT_QUOTES);
	} // parseMessage
	
	/**
	 * Mark a ticket as read
	 */
	private function markTicketRead($ticketId, $messageId, $username, $backend)
	{
		$this->hlog(HELPDESK_DEBUGLOG, '+markTicketRead('.$ticketId.','.$messageId.','.$username.','.$backend.')', 1);
		$backend = $backend ? '1' : '0';
		$this->Database->prepare(
			"delete from `tl_helpdesk_ticketmarks` where `pid`=? and `username`=? and `backend`=?"
		)->execute($ticketId, $username, $backend);
		$this->Database->prepare(
			"insert into `tl_helpdesk_ticketmarks` %s"
		 )->set(array(
			'pid'		=> $ticketId,
			'username'	=> $username,
			'backend'	=> $backend,
			'message'	=> $messageId
		 ))->execute();
		$this->hlog(HELPDESK_DEBUGLOG, '-markTicketRead', -1);
	} // markTicketRead
			
} // class HelpdeskImport

/**
 * Instantiate controller
 */
$objImport = new HelpdeskImport();
$objImport->run();
?>
