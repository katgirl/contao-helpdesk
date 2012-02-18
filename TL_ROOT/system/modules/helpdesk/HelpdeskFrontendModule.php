<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Class HelpdeskFrontendModule
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskFrontendModule extends Module
{
	protected	$strTemplate;
	private		$controller;
	
	/**
	 * Generate module:
	 *   - Display a wildcard in the back end
	 *   - Select the template and compiler in the front end
	 */
	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . $this->name . ' ###';
			return $objTemplate->parse();
		} // if	
		$this->controller = new HelpdeskController($this);
		$this->controller->generate($this->strTemplate);
		return str_replace('{{', '{‎{', parent::generate());
	} // generate
	
	/**
	 * Compile module
	 */
	protected function compile()
	{
		global $objPage;
		$GLOBALS['TL_LANGUAGE'] = $objPage->language;
		$this->controller->compile($this->Template);
	} // compile

	/**
	 * Get array of category id's to display by this module
	 */
	public function categories()
	{
		$cats = unserialize($this->helpdesk_categories);
		return is_array($cats) ? $cats : array();
	} // categories
	
	/**
	 * Get array of all category id's on the current page
	 */
	public function allcategories()
	{
		global $objPage;
		$q = $this->Database->prepare(
			"\n select `mdl`.`helpdesk_categories` as `categories` " .
			"\n from `tl_article` as `art`" .
			"\n inner join `tl_content` as `cnt` on `art`.`id`=`cnt`.`pid` and `cnt`.`type`='module'".
			"\n inner join `tl_module` as `mdl` on `cnt`.`module`=`mdl`.`id` and `mdl`.`type`='helpdesk'".
			"\n where `art`.`pid`=? " 
		)->execute($objPage->id);
		$cats = $this->categories();
		while ($q->next()) {
			$c = unserialize($q->categories);
			if (is_array($c)) 
				foreach ($c as $x) 
					if (!in_array($x, $cats))
						$cats[] = $x;
		} // while
		sort($cats);
		return $cats;
	} // allcategories

	/**
	 * Hide this module when no categories were found
	 */
	public function hideWhenEmpty()
	{
		if (intval($this->helpdesk_hideempty))
			$this->Template = new FrontendTemplate('helpdesk_empty');
	} // hideWhenEmpty

	/**
	 * Set the module title according to the category id
	 */
	public function setModuleTitle($id, $isIndex)
	{
		if (in_array($id, $this->categories())) {
			if ($isIndex) { 
				$this->Template->headertext = $this->helpdesk_text;
				$this->Template->links = intval($this->helpdesk_links)>0;
			} // if
			return;
		} // if
		$q = $this->Database->prepare(
			"select `headline`, `helpdesk_links`, `helpdesk_text`, `helpdesk_categories` from `tl_module` " .
			"where `type`='helpdesk' order by `id`"
		)->execute();
		while ($q->next()) {
			$cats = unserialize($q->helpdesk_categories);
			if (is_array($cats) && in_array($id, $cats)) {
				$headline = unserialize($q->headline);
				if (is_array($headline)) 
					$this->Template->headline = $headline['value'];
				if ($isIndex) {
					$this->Template->headertext = $q->helpdesk_text;
					$this->Template->links = intval($q->helpdesk_links)>0;
				} // if
				break;
			} // if
		} // while
	} // setModuleTitle
	
	/**
	 * Create frontend url for hyperlink.
	 * For odd # of parameters, the first argument is taken as base.
	 * For even # of parameters, tha base is the current page.
	 */
	public function createUrl()
	{
		$params = func_get_args();
		if (isset($params[0]) && is_array($params[0])) 
			$params = array_values($params[0]);
		$rewr = $GLOBALS['TL_CONFIG']['rewriteURL'];
		$disa = $GLOBALS['TL_CONFIG']['disableAlias'];
		$url = '';
		if (count($params) & 1)
			$url = array_shift($params);
		else {
			if ($disa) 
				$url = 'index.php?id=';
			else
				if (!$rewr) $url = 'index.php/';
			$url .= $this->getPageIdFromUrl();
		} // if
		if ($disa) {
			for($i = 0; $i < count($params); $i += 2)
				$url .= '&' . $params[$i] . '=' . $params[$i+1];
		} else {
			foreach ($params as $param) $url .= '/' . $param;
			$url .= $GLOBALS['TL_CONFIG']['urlSuffix'];
		} // if
		return $url;
	} // createUrl

} // class HelpdeskFrontendModule

?>
