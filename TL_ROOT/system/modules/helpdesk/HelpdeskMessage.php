<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Class HelpdeskMessage
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

/**
 * Class HelpdeskMessage
 */
class HelpdeskMessage extends System
{
	// data
	public	$id;
	public	$pid;
	public	$tstamp;
	public	$tstampraw;
	public	$reply;
	public	$by_email;
	public	$poster;
	public	$poster_cd;
	public	$bbmessage;
	public	$attachment;
	public	$published;
	
	// summaries
	public	$backend;
	public	$supporter;
	public	$isPoster;
	public	$jsquote;
	public	$message;
	public	$signature;
	public	$postcount;
	public	$location;
	public	$realname;
	public	$posterObj;
	
	// links
	public	$showMessageLink;
	public	$editMessageLink;
	public	$deleteMessageLink;
	public	$quoteMessageLink;
	public	$publishMessageLink;
	public	$unpublishMessageLink;
	public	$cutMessageLink;
	public	$viewProfileLink;
	
	protected static $posterInfo = array();
	
	/**
	 * Constructor
	 */
	public function __construct(&$hm, &$hd, &$q)
	{
		$this->import('Database');
		
		// set message data
		$this->id			= $q->msg_id;
		$this->pid			= $q->msg_pid;
		$this->tstampraw	= $q->msg_tstamp;
		$this->tstamp		= $hd->localDate(trim($q->msg_tstamp));
		$this->reply		= intval($q->msg_reply)>0;
		$this->by_email		= intval($q->msg_by_email)>0;
		$this->poster		= $q->msg_poster;
		$this->poster_cd	= intval($q->msg_poster_cd);
		$this->bbmessage	= $q->msg_message;
		$this->published	= intval($q->msg_published)>0;
		if (trim($q->msg_editor)!='') {
			$this->edited		= $hd->localDate(trim($q->msg_edited));
			$this->editor		= $q->msg_editor;
			$this->editor_cd	= intval($q->msg_editor_cd);
		} // if
		$this->attachment	= array();
		$this->addAttachment(1, $q->msg_atch1name);
		$this->addAttachment(2, $q->msg_atch2name);
		$this->addAttachment(3, $q->msg_atch3name);
		$this->addAttachment(4, $q->msg_atch4name);
		$this->addAttachment(5, $q->msg_atch5name);

		// load bbcode parser
		if (is_null($hd->parser)) {
			$hd->parser = new HelpdeskBbcodeParser($hd->theme);
			$hd->parser->module = &$hm;
		} // if

		// summaries
		$this->decodePosterCd($this->poster_cd, $this->backend, $this->supporter);

		$posterKey = $this->poster.' '.(int)$this->backend;
		if (!array_key_exists($posterKey, self::$posterInfo)) {
			$sql = $this->backend
				?	"select * from `tl_user` where `username`=?"
				:	"select * from `tl_member` where `username`=?";
			$q1 = $this->Database->prepare($sql)->execute($this->poster);
			$info = (object)array(
				'avatar'	=> null, 
				'role'		=> null, 
				'location'	=> null, 
				'signature' => null, 
				'postcount'	=> 0
			);		
			if ($q1->next()) {
				self::$posterInfo[$posterKey] = (object)$q1->row();
			} else
				self::$posterInfo[$posterKey] = new stdClass();
		} // if

		$this->posterObj = self::$posterInfo[$posterKey];
		
		// create location
		if (intval($this->posterObj->helpdesk_showlocation)) {
			if ($this->backend)
				$this->location = trim($this->posterObj->location);
			else {
				$this->location	 = trim($this->posterObj->city);
				if ($this->location != '' && trim($this->posterObj->country) != '') 
					$this->location .= ', ';
				$this->location .= $GLOBALS['TL_LANG']['CNT'][trim($this->posterObj->country)];
			} // if
		} // if
		
		// create realname
		if (intval($this->posterObj->helpdesk_showrealname)) {
			$this->realname	 = $backend 
				? trim($this->posterObj->name)
				: trim(trim($this->posterObj->firstname).' '.trim($this->posterObj->lastname));
		} // if

		// other stuff
		$this->message	 = trim($hd->parser->parse($this->bbmessage."\n",$this->id));
		$this->signature = trim($hd->parser->parse($this->posterObj->helpdesk_signature."\n",$this->id));
		$this->jsquote	 = $this->expJsString('[quote='.$this->poster.']'.$this->bbmessage.'[/quote]');
		$this->isPoster	 = $hd->role>=HELPDESK_CLIENT && $this->backend==$hd->backend && $this->poster==$hd->username;

		// create message links
		$this->showMessageLink = $hm->createUrl('message', $this->id);
		if (!$hd->ticket->isClosed && $hd->role>=HELPDESK_CLIENT) {
			if ($this->isPoster || $hd->role>=HELPDESK_SUPPORTER) {
				$this->editMessageLink = $hm->createUrl('edit', $this->id);
				if ($this->reply) $this->deleteMessageLink = $hm->createUrl('unpublish', $this->id);
			} // if
			$this->quoteMessageLink = $hm->createUrl('quote', $this->id);
		} // if
		if ($hd->role>=HELPDESK_SUPPORTER) {
			if ($this->reply) {
				if (!is_array($hd->clipboard) ||
					$hd->clipboard['mode']!='cutmessage' ||
					!in_array($this->id, $hd->clipboard['id']))
					$this->cutMessageLink = $hm->createUrl('cutmsg', $this->id);
				$this->deleteMessageLink = $hm->createUrl('delete', $this->id);
				if ($this->published) 
					$this->unpublishMessageLink = $hm->createUrl('unpublish', $this->id);
				else
					$this->publishMessageLink = $hm->createUrl('publish', $this->id);
			} // if
		} // if
		if (!$this->backend && $hm->helpdesk_profpage!='') {
			switch ($hm->helpdesk_profmode) {
				case 3: // everybody
					$ok = true;
					break;
				case 2: // clients
					$ok = $hd->role >= HELPDESK_CLIENT; 
					break;
				case 1: // supporters 
					$ok = $hd->role >= HELPDESK_SUPPORTER; 
					break;
				default: // nobody
					$ok = false;
			} // switch
			if ($ok)
				$this->viewProfileLink = 
					$hm->helpdesk_profpage . 
					(strpos($hm->helpdesk_profpage,'?')===false ? '?' : '&') . 
					'show=' . 
					$this->posterObj->id;
		} // if
	} // __construct
	
	/**
	 * Decode poster_cd
	 */
	public static function decodePosterCd($poster_cd, &$backend, &$supporter)
	{
		$backend   = $poster_cd==1 || $poster_cd==3;
		$supporter = $poster_cd>=2;
	} // decodePosterCd
	
	/**
	 * Encode poster_cd
	 */
	public static function encodePosterCd($backend, $supporter)
	{
		$poster_cd = $backend ? 1 : 0;
		if ($supporter) $poster_cd += 2;
		return $poster_cd;
	} // encodePosterCd
	
	/**
	 * Export a string as javascript literal
	 */
	private function expJsString($text)
	{
		return
			"'" .
			str_replace(
				array("\\", "'", "\"", "\r", "<", "\n"),
				array("\\\\", "\\'", "\\042", "\\r", "\\074", "\\n"),
				$text
			) .
			"'";
	} // expJsString

	/**
	 * Add attachment record
	 */
	private function addAttachment($id, $name)
	{
		if (!strlen($name)) return;
		$dld = (TL_MODE=='BE') ? 'HelpdeskBackendDownload.php' : 'HelpdeskFrontendDownload.php';
		$this->attachment[$id] = array(
			'name' => $name,
			'href' => 'system/modules/helpdesk/'.$dld.'?msg='.$this->id.'&id='.$id,
			'icon' => Helpdesk::getFileIcon($name)
		);
	} // addAttachment
	
	/**
	 * Create a download token
	 */
	private function createDownloadToken($strFile)
	{
		$strToken = md5(microtime(true));
		if (!in_array($strFile, $_SESSION['downloadFiles'])) $_SESSION['downloadFiles'][] = $strFile;
		if (!in_array($strToken, $_SESSION['downloadToken'])) $_SESSION['downloadToken'][] = $strToken;
		return $strToken;
	} // createDownloadToken
	
} // class HelpdeskMessage

?>