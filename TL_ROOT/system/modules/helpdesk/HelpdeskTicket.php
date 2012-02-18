<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Class HelpdeskTicket
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskTicket
{
	// data
	public	$id;
	public	$pid;
	public	$tstamp;
	public	$client;
	public	$client_be;
	public	$supporter;
	public	$supporter_be;
	public	$subject;
	public	$status;
	public	$published;
	public	$pub_replies;
	public	$pub_latest;
	public	$all_replies;
	public	$all_latest;
	public	$views;

	// summaries
	public	$creator;
	public  $index;
	public	$isOwner;
	public	$isClosed;
	public	$replycount;
	public	$latestlink;
	public	$latesttstamp;
	public	$latestposter;
	public	$latestmessage;
	public	$read;
	public	$cat_title;

	// links
	public	$openTicketLink;
	public	$closeTicketLink;
	public	$replyTicketLink;
	public	$removeTicketLink;
	public	$publishTicketLink;
	public	$unpublishTicketLink;
	public	$pinupTicketLink;
	public	$unpinTicketLink;
	public	$cutTicketLink;
	public	$pasteLink;
	public	$listMessagesLink;
	public	$pageLinks;

	/**
	 * Constructor
	 */
	public function __construct(&$hm, &$hd, &$q, $message=false)
	{
		// data
		$this->id			= $q->tck_id;
		$this->pid			= $q->tck_pid;
		$this->tstamp		= $hd->localDate(trim($q->tck_tstamp));
		$this->client		= $q->tck_client;
		$this->client_be	= intval($q->tck_client_be)>0;
		$this->supporter	= $q->tck_supporter;
		$this->supporter_be	= intval($q->tck_supporter_be)>0;
		$this->subject		= $q->tck_subject;
		$this->status		= intval($q->tck_status);
		$this->published	= intval($q->tck_published)>0;
		$this->pub_replies	= $q->tck_pub_replies;
		$this->pub_latest	= $q->tck_pub_latest;
		$this->all_replies	= $q->tck_all_replies;
		$this->all_latest	= $q->tck_all_latest;
		$this->views		= $q->tck_views;
		
		// summaries
		$this->creator		= strlen($this->client) ? $this->client : $this->supporter;
		$this->index 		= $this->status . ($hd->category->isSupport ? '0' : '1');
		$this->isOwner		= $hd->role==HELPDESK_CLIENT && $hd->username==$this->client && $hd->backend==$this->client_be;
		$this->isClosed		= $this->status == 2;
		
		// links
		$this->listMessagesLink = 
			($message)
				? $hm->createUrl('message', $q->msg_id)
				: $hm->createUrl('topic', $this->id);
		if ($this->isClosed) {
			if ($hd->role>=HELPDESK_SUPPORTER || ($this->isOwner && $hd->category->isSupport)) 
				$this->openTicketLink = $hm->createUrl('open', $this->id);
		} else {
			// ticket is open
			if ($hd->role>=HELPDESK_CLIENT) {
				$this->replyTicketLink = $hm->createUrl('reply', $this->id);
				if ($this->isOwner && $hd->category->isSupport) {
					$this->closeTicketLink = $hm->createUrl('close', $this->id);
					$this->removeTicketLink = $hm->createUrl('unpublishticket', $this->id);
				} // if
				if ($hd->role>=HELPDESK_SUPPORTER) {
					$this->closeTicketLink = $hm->createUrl('close', $this->id);
					if ($hd->category->isForum) {
						if ($this->status)
							$this->pinupTicketLink = $hm->createUrl('pinup', $this->id);
						else
							$this->unpinTicketLink = $hm->createUrl('unpin', $this->id);
					} // if
				} // if
			} // if
		} // if
		if ($hd->role>=HELPDESK_SUPPORTER) {
			$this->removeTicketLink = $hm->createUrl('remove', $this->id);
			if ($this->published) 
				$this->unpublishTicketLink = $hm->createUrl('unpublishticket', $this->id);
			else
				$this->publishTicketLink = $hm->createUrl('publishticket', $this->id);
			if (is_array($hd->clipboard))
				$this->pasteLink = $hm->createUrl('pastetck', $this->id);
			if (!is_array($hd->clipboard) ||
				$hd->clipboard['mode']!='cutticket' ||
				!in_array($this->id, $hd->clipboard['id']))
				$this->cutTicketLink = $hm->createUrl('cut', $this->id);
		} // if
	} // __construct
	
} // class HelpdeskTicket

?>
