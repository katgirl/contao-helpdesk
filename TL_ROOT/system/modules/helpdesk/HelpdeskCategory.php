<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Class HelpdeskCategory
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskCategory
{
	// data
	public	$id;
	public	$tstamp;
	public	$header;
	public	$title;
	public	$description;
	public	$buttons;
	public	$access;
	public	$replyonly;
	public	$fe_clients;
	public	$be_clients;
	public	$fe_supporters;
	public	$be_supporters;
	public	$published;
	public	$feed;
	public	$atch;
	public	$atch_dir;
	public	$atch_size;
	public	$atch_types;
	public	$notify;
	public	$notify_astext;
	public	$notify_name;
	public	$notify_sender;
	public	$notify_fe_url;
	public	$notify_be_url;
	public	$notify_newsubj;
	public	$notify_newtext;
	public	$notify_replysubj;
	public	$notify_replytext;
	public	$import;
	public	$import_server;
	public	$import_port;
	public	$import_type;
	public	$import_tls;
	public	$import_username;
	public	$import_password;
	public	$import_email;
	public	$pub_tickets;
	public	$pub_replies;
	public	$pub_latest;
	public	$all_tickets;
	public	$all_replies;
	public	$all_latest;

	// summaries
	public	$isSupport;
	public	$isForum;
	public	$isPublic;
	public	$isPrivate;
	public	$ticketcount;
	public	$replycount;
	public	$latestmessage;
	public	$latestlink;
	public	$latesttstamp;
	public	$latestposter;
	public	$read;
	public	$checked;

	// links
	public	$createTicketLink;
	public	$listTicketsLink;
	public	$markReadLink;
	public	$searchLink;
	public	$feedLink;
	public	$pasteLink;

	/**
	 * Constructor
	 */
	public function __construct(&$hm, &$hd, &$q, $ticket=false)
	{
		// data
		$this->id				= $q->cat_id;
		$this->tstamp			= $hd->localDate($q->cat_tstamp);
		$this->header			= $q->cat_header;
		$this->title			= $q->cat_title;
		$this->description		= $q->cat_description;
		$this->buttons			= $q->cat_buttons;
		$this->access			= intval($q->cat_access);
		$this->replyonly		= intval($q->cat_replyonly)>0;
		$this->fe_clients		= $hd->unpackArray($q->cat_fe_clients);		
		$this->be_clients		= $hd->unpackArray($q->cat_be_clients);	
		$this->fe_supporters	= $hd->unpackArray($q->cat_fe_supporters);	
		$this->be_supporters	= $hd->unpackArray($q->cat_be_supporters);	
		$this->published		= intval($q->cat_published)>0;
		$this->feed				= intval($q->cat_feed)>0;
		$this->atch				= intval($q->cat_atch)>0;
		$this->atch_dir			= $q->cat_atch_dir;
		$this->atch_size		= $q->cat_atch_size;
		$this->atch_types		= explode(',', strtolower(trim(str_replace(' ','',$q->cat_atch_types),',')));
		$this->notify			= intval($q->cat_notify)>0;
		$this->notify_astext	= intval($q->cat_notify_astext)>0;
		$this->notify_name		= $q->cat_notify_name;
		$this->notify_sender	= $q->cat_notify_sender;
		$this->notify_fe_url	= $q->cat_notify_fe_url;
		$this->notify_be_url	= $q->cat_notify_be_url;
		$this->notify_newsubj	= $q->cat_notify_newsubj;
		$this->notify_newtext	= $q->cat_notify_newtext;
		$this->notify_replysubj	= $q->cat_notify_replysubj;
		$this->notify_replytext	= $q->cat_notify_replytext;
		$this->import			= intval($q->cat_import)>0;
		$this->import_server	= $q->cat_import_server;
		$this->import_port		= $q->cat_import_port;
		$this->import_type		= $q->cat_import_type;
		$this->import_tls		= $q->cat_import_tls;
		$this->import_username	= $q->cat_import_username;
		$this->import_password	= $q->cat_import_password;
		$this->import_email		= $q->cat_import_email;
		$this->pub_tickets		= $q->cat_pub_tickets;
		$this->pub_replies		= $q->cat_pub_replies;
		$this->pub_latest		= $q->cat_pub_latest;
		$this->all_tickets		= $q->cat_all_tickets;
		$this->all_replies		= $q->cat_all_replies;
		$this->all_latest		= $q->cat_all_latest;
		
		// summaries
		$this->isSupport	= $this->access<=HELPDESK_PUBLIC_SUPPORT;
		$this->isForum		= $this->access>=HELPDESK_PROTECTED_FORUM;
		$this->isPublic		= $this->access==HELPDESK_PUBLIC_SUPPORT || $this->access==HELPDESK_PUBLIC_FORUM;
		$this->isPrivate	= $this->access==HELPDESK_PRIVATE_SUPPORT;

		// update texts for forums
		if ($this->isForum) {
			foreach ($GLOBALS['TL_LANG']['tl_helpdesk_forum'] as $id => $val)
				$hd->text[$id] = &$GLOBALS['TL_LANG']['tl_helpdesk_forum'][$id];
		} // if

		// links
		$this->listTicketsLink = $hm->createUrl('category', $this->id);
		if ($ticket) 
			$this->listTicketsLink .= 
				(strpos($this->listTicketsLink,'?')===false ? '?' : '&') .
				'pageof=' . $q->tck_id;
				
		if ($hd->role>=HELPDESK_SUPPORTER)
			if (is_array($hd->clipboard))
				$this->pasteLink = $hm->createUrl('paste', $this->id);

		if ($hd->role>=HELPDESK_SUPPORTER || ($hd->role>=HELPDESK_CLIENT && !$this->replyonly))
			$this->createTicketLink = $hm->createUrl('create', $this->id);
			
		if (strlen($hd->username))
			$this->markReadLink = $hm->createUrl('markread', $this->id);
		
		$this->searchLink = $hm->createUrl('search', $this->id);
		
		if ($hd->settings->feeds>0 && $this->feed)
			$this->feedLink = $hd->settings->feedlink . $this->id . '.xml';
		
		// update template title
		$hm->setModuleTitle($this->id, $hd->isIndex);
	} // __construct
	
} // class HelpdeskCategory

?>
