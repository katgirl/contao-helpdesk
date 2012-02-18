<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Configuration file
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

/**
 * CONTENT ELEMENTS
 */
array_insert($GLOBALS['TL_CTE']['includes'], 0, array(
	'helpdesk_comments'		=> 'HelpdeskComments'
));

/**
 * BACK END MODULES
 */
array_insert($GLOBALS['BE_MOD'], 0, array(
	'helpdesk' => array(
		'helpdesk_discuss' => array(
			'callback'		=>	'HelpdeskBackendModule',
			'icon'			=>	HelpdeskTheme::image('helpdesk16'),
			'stylesheet'	=>	HelpdeskTheme::file('frontend.css')
		),
		'helpdesk_structure' => array(
			'tables'		=>	array('tl_helpdesk_categories'),
			'icon'			=>	HelpdeskTheme::image('structure16'),
			'stylesheet'	=>	HelpdeskTheme::file('backend.css')
		),
		'helpdesk_settings' => array(
			'tables'		=>	array('tl_helpdesk_settings'),
			'icon'			=>	HelpdeskTheme::image('settings16'),
			'stylesheet'	=>	HelpdeskTheme::file('backend.css')
		)
	)
));

/**
 * FRONT END MODULES
 */
$GLOBALS['FE_MOD']['application'] += array('helpdesktitle' => 'HelpdeskFrontendTitle');
$GLOBALS['FE_MOD']['application'] += array('helpdesk' => 'HelpdeskFrontendModule');

/**
 * HOOKS
 */
$GLOBALS['TL_HOOKS'][(VERSION=='2.5' && (int)BUILD<10) ? 'outputTemplate' : 'outputFrontendTemplate'][] = array('HelpdeskTags', 'replaceTags');
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('HelpdeskFrontend', 'getSearchablePages');

?>
