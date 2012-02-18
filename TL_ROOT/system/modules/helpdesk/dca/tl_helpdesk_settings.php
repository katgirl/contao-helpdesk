<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Data container array for table tl_helpdesk_settings
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

require_once(dirname(dirname(__FILE__)).'/HelpdeskConstants.php');

$text = &$GLOBALS['TL_LANG']['tl_helpdesk_settings'];

/**
 * Table tl_helpdesk_settings
 */
$GLOBALS['TL_DCA']['tl_helpdesk_settings'] = array(

	// Config
	'config' => array(
		'dataContainer'			=> 'HelpdeskTable',
		'enableVersioning'		=> true,
		'closed'				=> true,
		'onload_callback'		=> array(array('tl_helpdesk_settings', 'initialize'))
	),
	
	// List
	'list' => array(
		'sorting' => array(
			'mode'				=>	0
		),
		'label' => array(
			'fields'			=>	array(0),
			'label_callback'	=>	array('tl_helpdesk_settings', 'listSettings')
		),
		'global_operations' => array(
			'synchronize' => array
			(
				'button_callback' => array('tl_helpdesk_settings', 'synchronizeButton')
			),
		),
		'operations' => array(
			'edit' => array(
				'label'			=>	&$text['edit'],
				'href'			=> 'act=edit',
				'icon'			=>	'edit.gif'
			)
		)
	),

	// Palettes
	'palettes' => array(
		'__selector__'	=>	array('feeds'),
		'default'		=>	'tpage,mpage,spage,pagenavsize,pagenavctl,edits,editswait,postdelay,searchdelay,searchmax,tlsearch,recenthours,images,feeds,logging'
	),

	// Subpalettes
	'subpalettes' => array (
		'feeds'			=> 'feedmax,feedlimit,feedlink,feedtitle,feeddescription'
	),

	// Fields
	'fields' => array(	
		'tpage' => array(
			'label'		=>	&$text['tpage'],
			'default'	=>	'30',
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('rgxp'=>'digit', 'maxlength'=>6)
		),

		'mpage' => array(
			'label'		=>	&$text['mpage'],
			'default'	=>	'15',
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('rgxp'=>'digit', 'maxlength'=>6)
		),

		'spage' => array(
			'label'		=>	&$text['spage'],
			'default'	=>	'10',
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('rgxp'=>'digit', 'maxlength'=>6)
		),

		'pagenavctl' => array(
			'label'		=>	&$text['pagenavctl'],
			'exclude'	=>	true,
			'filter'	=>	true,
			'inputType'	=>	'select',
			'options'	=>	array(0, 1),
			'reference' =>	&$text['pagenavoptions'],
			'eval'		=>	array('mandatory'=>true)
		),
		
		'pagenavsize' => array(
			'label'		=>	&$text['pagenavsize'],
			'default'	=>	'7',
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('rgxp'=>'digit', 'maxlength'=>6)
		),

		'edits' => array(
			'label'		=>	&$text['edits'],
			'exclude'	=>	true,
			'filter'	=>	true,
			'inputType'	=>	'select',
			'options'	=>	array(2, 1, 0),
			'reference' =>	&$text['editoptions'],
			'eval'		=>	array('mandatory'=>true)
		),
		
		'editswait' => array(
			'label'		=>	&$text['editswait'],
			'default'	=>	'300',
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('rgxp'=>'digit', 'maxlength'=>6)
		),

		'postdelay' => array(
			'label'		=>	&$text['postdelay'],
			'default'	=>	'15',
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('rgxp'=>'digit', 'maxlength'=>6)
		),

		'searchdelay' => array(
			'label'		=>	&$text['searchdelay'],
			'default'	=>	'30',
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('rgxp'=>'digit', 'maxlength'=>6)
		),

		'searchmax' => array(
			'label'		=>	&$text['searchmax'],
			'default'	=>	'100',
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('rgxp'=>'digit', 'maxlength'=>6)
		),

		'tlsearch' => array(
			'label'		=> &$text['tlsearch'],
			'exclude'	=>	true,
			'inputType'	=> 'checkbox',
		),

		'recenthours' => array(
			'label'		=>	&$text['recenthours'],
			'default'	=>	'24',
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('rgxp'=>'digit', 'maxlength'=>6)
		),

		'images' => array(
			'label'		=>	&$text['images'],
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('maxlength'=>255)
		),

		'feeds' => array(
			'label'		=> &$text['feeds'],
			'exclude'	=>	true,
			'inputType'	=> 'checkbox',
			'eval'		=> array('submitOnChange'=>true)
		),

		'feedmax' => array(
			'label'		=> &$text['feedmax'],
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('mandatory'=>true, 'nospace'=>true,'rgxp'=>'digit','maxlength'=>6)
		),

		'feedlimit' => array(
			'label'		=> &$text['feedlimit'],
			'exclude'	=>	true,
			'inputType'	=> 'text',
			'eval'		=> array('mandatory'=>true, 'nospace'=>true,'rgxp'=>'digit','maxlength'=>6)
		),

		'feedlink' => array(
			'label'		=> &$text['feedlink'],
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('mandatory'=>true, 'rgxp'=>'extnd', 'maxlength'=>64)
		),

		'feedtitle' => array(
			'label'		=> &$text['feedtitle'],
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('mandatory'=>true, 'rgxp'=>'extnd', 'maxlength'=>64)
		),

		'feeddescription' => array(
			'label'		=> &$text['feeddescription'],
			'exclude'	=>	true,
			'inputType'	=>	'text',
			'eval'		=>	array('mandatory'=>true, 'rgxp'=>'extnd', 'maxlength'=>256)
		),

		'logging' => array(
			'label'		=> &$text['logging'],
			'exclude'	=> true,
			'inputType'	=> 'select',
			'options'	=> array(0, 1, 2, 3),
			'reference' => &$text['loglevel']
		)
	)
);

class tl_helpdesk_settings extends Backend
{
	protected $settings;
	
	private static $mStatusName = 
		array(
			'alpha1', 'alpha2', 'alpha3',
			'beta1', 'beta2', 'beta3',
			'rc1', 'rc2', 'rc3',
			'stable'
		);
		
	public function initialize()
	{
		$this->import('HelpdeskSettings', 'settings');
	} // initialize
	
	public function listSettings($row)
	{
		$this->loadLanguageFile('helpdesk_timezone');
		$text = &$GLOBALS['TL_LANG']['tl_helpdesk_settings'];
		$feeds = intval($row['feeds'])>0;
		$tlsearch = intval($row['tlsearch'])>0;
		$html = 
			'<div class="helpdesk-settings-title">' . $text['title'] . "</div>\n".
			'<table class="helpdesk-settings">'."\n".
			"<tr><th>" . $text['version'] . "</th><td>" . $this->formatVersion($row['version']) . "</td></tr>\n".
			"<tr><th>" . $text['tpage'][0] . "</th><td>" . $row['tpage'] . "</td></tr>\n".
			"<tr><th>" . $text['mpage'][0] . "</th><td>" . $row['mpage'] . "</td></tr>\n".
			"<tr><th>" . $text['spage'][0] . "</th><td>" . $row['spage'] . "</td></tr>\n".
			"<tr><th>" . $text['pagenavsize'][0] . "</th><td>" . $row['pagenavsize'] . "</td></tr>\n".
			"<tr><th>" . $text['pagenavctl'][0] . "</th><td>" . $text['pagenavoptions'][$row['pagenavctl']] . "</td></tr>\n".
			"<tr><th>" . $text['edits'][0] . "</th><td>" . $text['editoptions'][$row['edits']] . "</td></tr>\n".
			"<tr><th>" . $text['editswait'][0] . "</th><td>" . $row['editswait'] . "</td></tr>\n".
			"<tr><th>" . $text['postdelay'][0] . "</th><td>" . $row['postdelay'] . "</td></tr>\n".
			"<tr><th>" . $text['searchdelay'][0] . "</th><td>" . $row['searchdelay'] . "</td></tr>\n".
			"<tr><th>" . $text['searchmax'][0] . "</th><td>" . $row['searchmax'] . "</td></tr>\n".
			"<tr><th>" . $text['tlsearch'][0] . "</th><td>" . $text[$tlsearch?'yes':'no'] . "</td></tr>\n";
			"<tr><th>" . $text['recenthours'][0] . "</th><td>" . $row['recenthours'] . "</td></tr>\n".
			"<tr><th>" . $text['images'][0] . "</th><td>" . $row['images'] . "</td></tr>\n".
			"<tr><th>" . $text['feeds'][0] . "</th><td>" . $text[$feeds?'yes':'no'] . "</td></tr>\n";
		if ($feeds) $html .=
			"<tr><th>" . $text['feedmax'][0] . "</th><td>" . $row['feedmax'] . "</td></tr>\n".
			"<tr><th>" . $text['feedlimit'][0] . "</th><td>" . $row['feedlimit'] . "</td></tr>\n".
			"<tr><th>" . $text['feedlink'][0] . "</th><td>" . $row['feedlink'] . "</td></tr>\n".
			"<tr><th>" . $text['feedtitle'][0] . "</th><td>" . $row['feedtitle'] . "</td></tr>\n".
			"<tr><th>" . $text['feeddescription'][0] . "</th><td>" . $row['feeddescription'] . "</td></tr>\n";
		$html .=
			"<tr><th>" . $text['logging'][0] . "</th><td>" . $text['loglevel'][$row['logging']] . "</td></tr>\n".
			"</table>\n";
		return $html;
	} // listCategories
	
	public static function formatVersion($aVersion)
	{
		$aVersion	= (int)$aVersion;
		if (!$aVersion) return '';
		$status		= $aVersion % 10;
		$aVersion	= (int)($aVersion / 10);
		$micro		= $aVersion % 1000;
		$aVersion	= (int)($aVersion / 1000);
		$minor		= $aVersion % 1000;
		$major		= (int)($aVersion / 1000);
		return "$major.$minor.$micro ".self::$mStatusName[$status];
	} // formatVersion
	
	/**
	 * Create the synchronize button
	 */
	public function synchronizeButton($href, $label, $title, $icon, $attributes, $table, $root)
	{
		$href = 'act=synchronize';
		$label = &$GLOBALS['TL_LANG']['tl_helpdesk_settings']['synchronize'];
		$icon = 'sync16';
		return 
			'<a href="' . $this->addToUrl($href) . '"' .
				' title="' . specialchars($label[1]) . '"' . 
				' class="helpdesk-global-button"' . 
				' onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_helpdesk_settings']['confsync'] . '\')) return false; Backend.getScrollOffset();"' .				
			'>' . 
			 HelpdeskTheme::createImage('sync16', $label[0]) . ' ' . $label[0] . 
			 '</a> ';
	} // synchronizeButton

} // tl_helpdesk_settings

?>
