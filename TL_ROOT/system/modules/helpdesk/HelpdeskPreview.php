<?php
/**
 * Contao Helpdesk :: Class HelpdeskPreview
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

define('TL_MODE', 'BE');
require_once('../../initialize.php');

class HelpdeskPreview extends Controller
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
	} // __construct

	/**
	 * Run controller and parse the template
	 */
	public function run()
	{
		$bbcode = html_entity_decode($_GET['bbcode'], ENT_COMPAT, $GLOBALS['TL_CONFIG']['characterSet']);
		if (get_magic_quotes_gpc()) $bbcode = stripslashes($bbcode);
		$this->Template = new BackendTemplate('helpdesk_preview');
		$ubbParser = new HelpdeskBbcodeParser(new HelpdeskTheme());
		$this->Template->content = $ubbParser->parse($bbcode."\n");
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
	
} // class HelpdeskPreview


/**
 * Instantiate controller
 */
$objPreview = new HelpdeskPreview();
$objPreview->run();

?>
