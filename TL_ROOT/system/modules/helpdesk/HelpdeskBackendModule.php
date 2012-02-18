<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Class HelpdeskBackendModule
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskBackendModule extends BackendModule
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
		$this->controller = new HelpdeskController($this);
		$this->controller->generate($this->strTemplate);
		return str_replace('{{', '{&lrm;{', parent::generate());
	} // generate
	
	/**
	 * Compile module
	 */
	protected function compile()
	{
		$this->Template->class = 'mod_helpdesk';
		$this->controller->compile($this->Template);
	} // compile
	
	/**
	 * Hide this module when no categories were found
	 */
	public function hideWhenEmpty()
	{
		// dummy for backend
	} // hideWhenEmpty

	/**
	 * Set the module title according to the category id
	 */
	public function setModuleTitle($id)
	{
		$this->Template->links = true;
	} // setModuleTitle
	
	/**
	 * Create backend url for hyperlink.
	 * For odd # of parameters, the first argument is taken as base.
	 * For even # of parameters, tha base is the current page.
	 */
	public function createUrl()
	{
		$params = func_get_args();
		if (isset($params[0]) && is_array($params[0])) 
			$params = array_values($params[0]);
		$url = '';
		if (count($params) & 1)
			$url = array_shift($params);
		else
			$url = $this->Environment->script . '?do=helpdesk_discuss';
		for($i = 0; $i < count($params); $i += 2)
			$url .= '&' . $params[$i] . '=' . $params[$i+1];
		return $url;
	} // createUrl

} // class HelpdeskBackendModule

?>
