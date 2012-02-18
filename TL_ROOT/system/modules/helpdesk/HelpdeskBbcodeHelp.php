<?php
/**
 * Contao Helpdesk :: BBCode help popup
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once('../../initialize.php');


/**
 * Class Help
 *
 * Back end help wizard.
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@Contao.org>
 * @package    Controller
 */
class HelpdeskBbcodeHelp extends Controller
{

	/**
	 * Initialize the controller
	 */
	public function __construct()
	{
		parent::__construct();
		$this->loadLanguageFile('default');
		$this->loadLanguageFile('modules');
		$this->loadLanguageFile('tl_helpdesk_bbcode');
		$this->loadLanguageFile('tl_helpdesk_bbhelp');
	} // __construct

	/**
	 * Run controller and parse the template
	 */
	public function run()
	{
		$this->Template = new BackendTemplate('helpdesk_preview');
		$parser = new HelpdeskBbcodeParser(new HelpdeskTheme());
		$this->Template->content = $parser->parse($GLOBALS['TL_LANG']['tl_helpdesk_bbhelp']['help']."\n");
		$this->output();
	} // run

	/**
	 * Output the template file
	 */
	private function output()
	{
		$this->Template->theme = $this->getTheme();
		$this->Template->base = $this->Environment->base;
		$this->Template->language = $GLOBALS['TL_LANGUAGE'];
		$this->Template->title = $GLOBALS['TL_CONFIG']['websiteTitle'];
		$this->Template->charset = $GLOBALS['TL_CONFIG']['characterSet'];
		$this->Template->output();
	} // output
}


/**
 * Instantiate controller
 */
$objBbcodeHelp = new HelpdeskBbcodeHelp();
$objBbcodeHelp->run();

?>
