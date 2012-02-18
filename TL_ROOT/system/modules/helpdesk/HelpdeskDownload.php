<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Class HelpdeskDownload
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

require_once('HelpdeskConstants.php');

class HelpdeskDownload extends System
{
	protected $pp;
	protected $db;
	
	public function __construct(&$pp)
	{
		$this->pp = $pp;
		parent::__construct();
		$this->import('Database', 'db');
	} // __construct

	public function run()
	{
		$pp = &$this->pp;
		
		// get parameters
		$msg_id	= intval($this->Input->get('msg'));
		$id		= intval($this->Input->get('id'));
		if (!$msg_id || !$id) die('Missing mandatory parameters');

		// load data
		$q = $this->db->prepare(
			"\n select " .
			HELPDESK_MSGCOLS.','.
			HELPDESK_TCKCOLS.','.
			HELPDESK_CATCOLS.
			"\n from `tl_helpdesk_messages` as `msg`" .
			"\n inner join `tl_helpdesk_tickets` as `tck` on `msg`.`pid`=`tck`.`id`" .
			"\n inner join `tl_helpdesk_categories` as `cat` on `tck`.`pid`=`cat`.`id`" .
			"\n where `msg`.`id`=?"
		)->limit(1)->execute($msg_id);
		if (!$q->next()) die('Message not found.');

		// check access rights
		$pp->authorize($q);
		if ($pp->role < HELPDESK_SUPPORTER) {
			if (!intval($q->cat_published) || !intval($q->tck_published) || !intval($q->msg_published))
				die('Message not available.');
			$access = intval($q->cat_access);
			if ($pp->role==HELPDESK_GUEST && 
				($access==HELPDESK_PRIVATE_SUPPORT || $access==HELPDESK_SHARED_SUPPORT || $access==HELPDESK_PROTECTED_FORUM))
				die('Access denied.');
			if ($access==HELPDESK_PRIVATE_SUPPORT &&
				(intval($q->tck_client_be)!=$pp->backend || $q->tck_client!=$pp->username))
				die('Private access denied.');
		} // if

		// get & check filename
		$name = '';
		switch ($id) {
			case 1: $name = trim($q->msg_atch1name); break;
			case 2: $name = trim($q->msg_atch2name); break;
			case 3: $name = trim($q->msg_atch3name); break;
			case 4: $name = trim($q->msg_atch4name); break;
			case 5: $name = trim($q->msg_atch5name); break;
			default:;
		} // switch
		if (!strlen($name)) die('Invalid attachment index.');
		
		// get file path
		$parts = explode('/',str_replace('\\', '/', $q->cat_atch_dir));
		$path = TL_ROOT.'/';
		foreach ($parts as $part) if (strlen($part)) $path .= $part.'/';
		$path .= $msg_id.'.'.$id;
		if (!file_exists($path)) die(sprintf('File not found (%s).', $path));
		
		// all ready now
		header('Content-Type: '.Helpdesk::getMimeType($name));
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="'.$name.'"');
		header('Content-Length: '.filesize($path));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
		header('Pragma: public');
		header('Expires: 0');
		readfile($path);
	} // run
	
} // class HelpdeskDownload

?>
