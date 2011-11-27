<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Helpdesk class
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class Helpdesk extends System
{
	// data
	public	$isIndex;
	public	$backend;
	public	$role;
	public	$username;
	public	$groups;
	public	$theme;
	public	$text;
	public	$totrecs;
	public	$pagesize;
	public	$pages;
	public	$page;
	public	$parser;
	public	$target;
	public	$settings;
	public	$timezone;
	
	// links
	public	$listCategoriesLink;
	public	$firstPageLink;
	public	$prevPageLink;
	public	$nextPageLink;
	public	$lastPageLink;
	public	$searchLink;
	public	$feedLink;
	public	$markReadLink;
	public	$unreadLink;
	public	$nopasteLink;
	public	$mineLink;
	public	$recentLink;
	public	$unansweredLink;
	
	// objects
	public	$category;
	public	$ticket;
	public	$message;
	public	$clipboard;
	
	// form elements
	public	$formMode;
	public	$formLink;
	public	$formAction;
	public	$client;
	public	$clientBe;
	public	$clientOption;
	public	$clientOptions;
	public	$subject;
	public	$subjectMissing;
	public	$msgtext;
	public	$messageMissing;
	public	$published;
	public	$editSubject;
	public	$editPublished;
	public	$editorButtons;
	public	$submitText;
	public	$atcherrs;
	public	$atchfiles;
	public	$attachment;
	public	$attachments;
	public	$searchterms;
	public	$searchtermsMissing;
	public	$poster;
	public	$advanced;
	public	$noPartsChecked;
	public	$noCategoriesChecked;
	public	$totmatches;
	public	$pageNavigation;
	
	// search results
	public	$result;
	public	$pageResult;
	static	$tzNames;

	/**
	 * Constructor
	 */
	public function __construct(&$hm, $catlink, $isIndex)
	{
		parent::__construct();
		$this->import('HelpdeskSettings', 'settings');
		$this->isIndex	= $isIndex;
		$this->text		= $GLOBALS['TL_LANG']['tl_helpdesk_support'];
		$this->backend	= (TL_MODE=='BE') ? 1 : 0;
		$this->role		= HELPDESK_GUEST;
		$this->attachment = array();
		if ($this->backend) {
			$this->import('BackendUser');
			$this->timezone = $this->BackendUser->helpdesk_timezone;
			$this->username	= $this->BackendUser->username;
			$this->groups	= $this->BackendUser->groups;
			if ($this->BackendUser->isAdmin) $this->role = HELPDESK_ADMIN;
		} else {
			if (FE_USER_LOGGED_IN) {
				$this->import('FrontendUser');
				$this->timezone = $this->FrontendUser->helpdesk_timezone;
				$this->username	= $this->FrontendUser->username;
				$this->groups	= $this->FrontendUser->groups;
			} // if
		} // if

		if ($this->timezone) {
			if ($this->settings->isValidTimezone($this->timezone))
				$this->timezone = new DateTimeZone($this->timezone);
			else
				$this->timezone = null;
		} // if

		$this->listCategoriesLink = $catlink;
		$this->searchLink = $hm->createUrl('search', $this->backend ? 'all' : implode(',', $hm->categories()));
		
		$this->published = true;
		$this->theme = $this->backend ? new HelpdeskTheme() : new HelpdeskFrontendTheme();
		$this->clipboard = $this->Session->get('HELPDESK_CLIPBOARD');
		$this->lastsearch = $this->Session->get('HELPDESK_LASTSEARCH');
		if (is_array($this->clipboard))
			$this->nopasteLink = $hm->createUrl('paste', 'no');
	} // __construct
	
	/**
	 * Format timestamp to local date
	 */
	public function localDate($tstamp)
	{
		if (!is_numeric($tstamp)) return '';
		$now = time();
		$df = 'Y-m-d H:i:s';
		$dt_today = new DateTime(date($df, $now));
		$dt_yesterday = new DateTime(date($df, $now-86400));
		$dt_then = new DateTime(date($df, $tstamp));
		if ($this->timezone) {
			$dt_today->setTimezone($this->timezone);
			$dt_yesterday->setTimezone($this->timezone);
			$dt_then->setTimezone($this->timezone);
		} // if
		$df = 'Y-m-d';
		$d_today = $dt_today->format($df);
		$d_yesterday = $dt_yesterday->format($df);
		$d_then = $dt_then->format($df);
		if ($d_then == $d_today)
			$dt = $GLOBALS['TL_LANG']['helpdesk']['today'];
		else
			if ($d_then == $d_yesterday)
				$dt = $GLOBALS['TL_LANG']['helpdesk']['yesterday'];
			else
				$dt = $dt_then->format($GLOBALS['TL_CONFIG']['dateFormat']);
		return $dt.' '.$dt_then->format($GLOBALS['TL_CONFIG']['timeFormat']);
	} // localDate
	
	/**
	 * Authorize depending on client- and supportergroups
	 */
	public function authorize(&$qcat)
	{
		if ($this->role==HELPDESK_ADMIN) return;
		$this->role = HELPDESK_GUEST;
		if (!is_array($this->groups)) return;
		if ($this->matchGroupsP($this->backend ? $qcat->cat_be_supporters : $qcat->cat_fe_supporters, $this->groups)) {
			$this->role = HELPDESK_SUPPORTER;
			return;
		} // if
		if ($this->matchGroupsP($this->backend ? $qcat->cat_be_clients : $qcat->cat_fe_clients, $this->groups))
			$this->role = HELPDESK_CLIENT;
	} // authorize

	/**
	 * Check category access authorization
	 */
	public function hasCategoryAccess($access)
	{
		return 
			intval($access)==HELPDESK_PUBLIC_FORUM		|| 
			intval($access)==HELPDESK_PUBLIC_SUPPORT	|| 
			$this->role >= HELPDESK_CLIENT;
	} // hasCategoryAccess
	
	/**
	 * Check ticket access authorization
	 */
	public function hasTicketAccess($access, &$qtck)
	{
		if ($this->role >= HELPDESK_SUPPORTER) return true;
		if (!intval($qtck->tck_published)) return false;
		switch (intval($access)) {
			case HELPDESK_PRIVATE_SUPPORT:
				return ($this->role == HELPDESK_CLIENT && 
						trim($qtck->tck_client)==$this->username && 
						intval($qtck->tck_client_be)==$this->backend);
			case HELPDESK_SHARED_SUPPORT:
			case HELPDESK_PROTECTED_FORUM:
				return ($this->role == HELPDESK_CLIENT);
			default:;
		} // switch
		return true;
	} // hasTicketAccess
	
	public function createPageNav(&$hm, $args)
	{
		$icons = $this->settings->pagenavctl == '1';
		if ($this->pagesize>0 && $this->pages>1) {
			$baselink = $hm->createUrl($args);
			$pagelink = self::addPage($baselink);
			if ($this->page>1) {
				$this->firstPageLink = $baselink;
				$this->prevPageLink = $pagelink.($this->page-1);
			} // if
			if ($this->page<$this->pages) {
				$this->nextPageLink = $pagelink.($this->page+1);
				$this->lastPageLink = $pagelink.$this->pages;
			} // if
			
			$nav = &$this->pageNavigation;
			$nav = '<span class="pagenav">';
		
			$text = sprintf($this->text['gotopage'], 1);
			if ($this->firstPageLink) {
				$nav .=	'<a class="pagenavicon" href="'.$this->firstPageLink.'" title="'.$text.'">'.
						($icons	? $this->createImage('firstpage116', $text, 'title="'.$text.'"')
								: $this->text['firstpage']) .
						'</a>';	
			} else {
				if ($icons)
					$nav .= '<span class="pagenavicon">'.
							$this->createImage('firstpage016', $text, 'title="'.$text.'"').
							'</span>';
			} // if

			$text = sprintf($this->text['gotopage'], $this->page-1);
			if ($this->prevPageLink) {
				$nav .= '<a class="pagenavicon" href="'.$this->prevPageLink.'" title="'.$text.'">'.
						($icons	? $this->createImage('prevpage116', $text, 'title="'.$text.'"')
								: $this->text['prevpage']) .
						'</a>';
			} else {
				if ($icons)
					$nav .= '<span class="pagenavicon">'.
							$this->createImage('prevpage016', $text, 'title="'.$text.'"').
							'</span>';
			} // if
			
			$size = $this->settings->pagenavsize;
			if ($size > 0) {
				$p1 = $p2 = $this->page;
				while ($size > 1 && ($p1 > 1 || $p2 < $this->pages)) {
					if ($p2 < $this->pages) { $p2++; $size--; }
					if ($size > 1 && $p1 > 1) { $p1--; $size--; }
				} // while
				for ($p = $p1; $p <= $p2; $p++) {
					if ($p == $this->page)
						$nav .= '<span class="pagenavactive">' . $p . '</span>';
					else {
						$text = sprintf($this->text['gotopage'], $p);
						$link = $pagelink.$p;
						$nav .= '<a class="pagenavlink" href="'.$link.'" title="'.$text.'">' . $p . "</a>";
					} // if
				} // for
			} else {
				$nav .= sprintf('<span class="pagenavtext">'.$this->text['page_n_of_m'].'</span>', $this->page, $this->pages);
			} // if
			
			$text = sprintf($this->text['gotopage'], $this->page+1);
			if ($this->nextPageLink) {
				$nav .= '<a class="pagenavicon" href="'.$this->nextPageLink.'" title="'.$text.'">'.
						($icons ? $this->createImage('nextpage116', $text, 'title="'.$text.'"')
								: $this->text['nextpage']) .
						'</a>';
			} else {
				if ($icons)
					$nav .= '<span class="pagenavicon">'.
							$this->createImage('nextpage016', $text, 'title="'.$text.'"').
							'</span>';
			} // if
			
			$text = sprintf($this->text['gotopage'], $this->pages);
			if ($this->lastPageLink) {
				$nav .= '<a class="pagenavicon" href="'.$this->lastPageLink.'" title="'.$text.'">'.
						($icons ? $this->createImage('lastpage116', $text, 'title="'.$text.'"')
								: $this->text['lastpage']) .
						'</a>';
			} else {
				if ($icons)
					$nav .= '<span class="pagenavicon">'.
							$this->createImage('lastpage016', $text, 'title="'.$text.'"').
							'</span>';
			} // if
			
			$nav .= '</span>';
		} else {
			$this->pageNavigation = 
				'<span class="pagenav"><span class="pagenavicon">' . 
				($icons ?  $this->createImage('blank16') : '&nbsp;') .
				'</span></span>';
		} // if
	} // createPageNav
	
	/**
	 * For convenience the themes createImage method is mirrored here
	 */
	public function createImage($file, $alt='', $attributes='')
	{
		return $this->theme->createImage($file, $alt, $attributes);
	} // createImage
	
	/**
	 * Unpack an array
	 */
	public static function &unpackArray($packed)
	{
		$arr = unserialize($packed);
		if (!is_array($arr)) $arr = array();
		return $arr;
	} // unpackArray
	
	/**
	 * Match 2 unpacked groups
	 */
	public static function matchGroups(&$g1, &$g2)
	{
		return count(array_intersect($g1, $g2)) > 0;
	} // matchGroups
	
	/**
	 * Match a packed with an unpacked array
	 */
	public static function matchGroupsP($g1p, &$g2)
	{
		return count(array_intersect(self::unpackArray($g1p), $g2));
	} // matchGroupsP
	
	/**
	 * Get the mime type of a file based on its extension
	 */
	public static function getMimeType($name)
	{
		$arrMimeTypes = array
		(
			'xl'    => 'application/excel',
			'hqx'   => 'application/mac-binhex40',
			'cpt'   => 'application/mac-compactpro',
			'doc'   => 'application/msword',
			'word'  => 'application/msword',
			'bin'   => 'application/macbinary',
			'dms'   => 'application/octet-stream',
			'lha'   => 'application/octet-stream',
			'lzh'   => 'application/octet-stream',
			'exe'   => 'application/octet-stream',
			'class' => 'application/octet-stream',
			'psd'   => 'application/x-photoshop',
			'so'    => 'application/octet-stream',
			'sea'   => 'application/octet-stream',
			'dll'   => 'application/octet-stream',
			'oda'   => 'application/oda',
			'pdf'   => 'application/pdf',
			'ai'    => 'application/postscript',
			'eps'   => 'application/postscript',
			'ps'    => 'application/postscript',
			'smi'   => 'application/smil',
			'smil'  => 'application/smil',
			'mif'   => 'application/vnd.mif',
			'xls'   => 'application/excel',
			'ppt'   => 'application/powerpoint',
			'wbxml' => 'application/wbxml',
			'wmlc'  => 'application/wmlc',
			'dcr'   => 'application/x-director',
			'dir'   => 'application/x-director',
			'dxr'   => 'application/x-director',
			'dvi'   => 'application/x-dvi',
			'gtar'  => 'application/x-gtar',
			'php'   => 'application/x-httpd-php',
			'php3'  => 'application/x-httpd-php',
			'php4'  => 'application/x-httpd-php',
			'php5'  => 'application/x-httpd-php',
			'phtml' => 'application/x-httpd-php',
			'phps'  => 'application/x-httpd-php-source',
			'js'    => 'application/x-javascript',
			'swf'   => 'application/x-shockwave-flash',
			'sit'   => 'application/x-stuffit',
			'tar'   => 'application/x-tar',
			'tgz'   => 'application/x-tar',
			'xhtml' => 'application/xhtml+xml',
			'xht'   => 'application/xhtml+xml',
			'zip'   => 'application/zip',
			'mid'   => 'audio/midi',
			'midi'  => 'audio/midi',
			'mpga'  => 'audio/mpeg',
			'mp2'   => 'audio/mpeg',
			'mp3'   => 'audio/mpeg',
			'wav'   => 'audio/x-wav',
			'aif'   => 'audio/x-aiff',
			'aiff'  => 'audio/x-aiff',
			'aifc'  => 'audio/x-aiff',
			'ram'   => 'audio/x-pn-realaudio',
			'rm'    => 'audio/x-pn-realaudio',
			'rpm'   => 'audio/x-pn-realaudio-plugin',
			'ra'    => 'audio/x-realaudio',
			'bmp'   => 'image/bmp',
			'gif'   => 'image/gif',
			'jpeg'  => 'image/jpeg',
			'jpg'   => 'image/jpeg',
			'jpe'   => 'image/jpeg',
			'png'   => 'image/png',
			'tiff'  => 'image/tiff',
			'tif'   => 'image/tiff',
			'eml'   => 'message/rfc822',
			'css'   => 'text/css',
			'html'  => 'text/html',
			'htm'   => 'text/html',
			'shtml' => 'text/html',
			'txt'   => 'text/plain',
			'text'  => 'text/plain',
			'log'   => 'text/plain',
			'rtx'   => 'text/richtext',
			'rtf'   => 'text/rtf',
			'xml'   => 'text/xml',
			'xsl'   => 'text/xml',
			'mpeg'  => 'video/mpeg',
			'mpg'   => 'video/mpeg',
			'mpe'   => 'video/mpeg',
			'qt'    => 'video/quicktime',
			'mov'   => 'video/quicktime',
			'avi'   => 'video/x-msvideo',
			'movie' => 'video/x-sgi-movie',
			'rv'    => 'video/vnd.rn-realvideo'
		);
		$parts = explode('.',$name);
		$mime = $arrMimeTypes[strtolower(end($parts))];
		if (strlen($mime)) return $mime;
		return 'application/octet-stream';
	} // getMimeType

	/**
	 * Get an icon for a file attachment
	 */
	public static function getFileIcon($name)
	{
		$parts = explode('.',$name);
		switch (strtolower(end($parts)))
		{
			// HTML
			case 'html':
			case 'htm':
				$icon = 'iconHTML.gif';
				break;

			// PHP
			case 'php':
			case 'php3':
			case 'php4':
			case 'php5':
			case 'inc':
				$icon = 'iconPHP.gif';
				break;

			// JavaScript
			case 'js':
				$icon = 'iconJS.gif';
				break;

			// Style sheets
			case 'css':
				$icon = 'iconCSS.gif';
				break;

			// Flash
			case 'swf':
			case 'fla':
				$icon = 'iconSWF.gif';
				break;

			// GIF
			case 'gif':
				$icon = 'iconGIF.gif';
				break;

			// JPG
			case 'jpg':
			case 'jpeg':
				$icon = 'iconJPG.gif';
				break;

			// TIF
			case 'png':
			case 'tif':
			case 'tiff':
				$icon = 'iconTIF.gif';
				break;

			// Bitmap
			case 'bmp':
				$icon = 'iconBMP.gif';
				break;

			// PDF
			case 'pdf':
				$icon = 'iconPDF.gif';
				break;

			// Archive
			case 'zip':
			case 'tar':
			case 'rar':
				$icon = 'iconRAR.gif';
				break;

			// ASP
			case 'jsp':
			case 'asp':
				$icon = 'iconJSP.gif';
				break;

			// Audio
			case 'mp3':
			case 'wav':
			case 'wma':
				$icon = 'iconAUDIO.gif';
				break;

			// Video
			case 'mov':
			case 'wmv':
			case 'avi':
			case 'ram':
			case 'rm':
				$icon = 'iconVIDEO.gif';
				break;

			// Office
			case 'doc':
			case 'xls':
			case 'ppt':
			case 'pps':
			case 'odt':
			case 'ods':
			case 'odp':
				$icon = 'iconOFFICE.gif';
				break;

			default:
				$icon = 'iconPLAIN.gif';
				break;
		} // switch
		$img = 'system/themes/'.$GLOBALS['TL_CONFIG']['backendTheme'].'/images/'.$icon;
		$size = getimagesize(TL_ROOT.'/'.$img);
		return '<img src="'.$img.'" '.$size[3].' alt="icon" />';
	} // getFileIcon
	
	public static function pageParam()
	{
		return version_compare(VERSION.'.'.BUILD, '2.8.0', '>=') ? 'page' : 'pageno';
	} // addPage

	public static function addPage($aUrl, $aPage = '')
	{
		return $aUrl . (strpos($aUrl,'?')===false ? '?' : '&') . self::pageParam() . '=' . $aPage;
	} // addPage

} // class Helpdesk

?>