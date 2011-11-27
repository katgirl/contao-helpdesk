<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Class HelpdeskFrontendTitle
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskFrontendTitle extends Module
{
	protected	$strTemplate = 'helpdesk_title';
	public		$theme;
	
	/**
	 * Generate module:
	 *   - Display a wildcard in the back end
	 *   - Select the template and compiler in the front end
	 */
	public function generate()
	{
		if (TL_MODE == 'BE') {
			$tpl = new BackendTemplate('be_wildcard');
			$tpl->wildcard = '### ' . $this->name . ' ###';
			return $tpl->parse();
		} // if	
		return parent::generate();
	} // generate
	
	/**
	 * Compile module
	 */
	protected function compile()
	{
		$this->loadLanguageFile('tl_helpdesk_frontend');
		$isIndex = count($_GET)==0; 
		// create helpdesk object
		$tpl = &$this->Template;
		$tpl->helpdesk = new Helpdesk(
			$this, 
			$this->createUrl(),
			$isIndex
		);
		$hd = &$tpl->helpdesk;

		// create index page objects
		if ($isIndex) {
			$tpl->headertext = $this->helpdesk_text;
			$tpl->links = intval($this->helpdesk_links)>0;
			if ($tpl->links) {
				// create feed link
				if ($hd->settings->feeds)
					$hd->feedLink = $hd->settings->feedlink.'0.xml';
					
				// create unread and mark links
				if (strlen($hd->username)) {
					$hd->markReadLink = $this->createUrl('markread', 'all');
					$hd->unreadLink = $this->createUrl('unread', 'all');
					$hd->mineLink = $this->createUrl('mine', 'all');
				} // if
				
				$hd->recentLink = $this->createUrl('recent', 'all');
				$hd->unansweredLink = $this->createUrl('unanswered', 'all');
			} // if
		} // if
	} // compile

	/**
	 * Dummy categories function to create search link in Helpdesk
	 */
	public function categories()
	{
		return array('all');
	} // categories
	
	/**
	 * Create url for hyperlink
	 */
	public function createUrl()
	{
		$params = func_get_args();
		if (isset($params[0]) && is_array($params[0])) $params = array_values($params[0]);
		$url =
			($GLOBALS['TL_CONFIG']['rewriteURL'] ? '' : 'index.php/') . 
			$this->getPageIdFromUrl();
		foreach ($params as $param) $url .= '/' . $param;
		return $url . $GLOBALS['TL_CONFIG']['urlSuffix'];
	} // createUrl
	
} // class HelpdeskFrontendTitle

?>