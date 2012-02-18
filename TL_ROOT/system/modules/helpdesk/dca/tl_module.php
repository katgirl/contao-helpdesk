<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Data container array for table tl_module
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['helpdesk'] = 'name,type,headline,helpdesk_text,helpdesk_links,helpdesk_categories,helpdesk_hideempty,helpdesk_profmode,helpdesk_profpage;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['helpdesktitle'] = 'name,type,headline,helpdesk_text,helpdesk_links;align,space,cssID';

/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['helpdesk_text'] = array(
	'label'			=>	&$GLOBALS['TL_LANG']['tl_module']['helpdesk_text'],
	'exclude'		=>	true,
	'inputType'		=>	'textarea',
	'eval'			=>	array('rte'=>'tinyMCE')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['helpdesk_categories'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['helpdesk_categories'],
	'exclude'       => true,
	'inputType'     => 'checkbox',
	'options_callback'	=> array('HelpdeskSettings','getCategoryOptions'),
	'eval'          => array('multiple'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['helpdesk_links'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['helpdesk_links'],
	'exclude'       => true,
	'inputType'     => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_module']['fields']['helpdesk_hideempty'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['helpdesk_hideempty'],
	'exclude'       => true,
	'inputType'     => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_module']['fields']['helpdesk_profmode'] = array
(
	'label'			=>	&$GLOBALS['TL_LANG']['tl_module']['helpdesk_profmode'],
	'exclude'		=>	true,
	'inputType'		=>	'select',
	'options'		=>	array(0, 1, 2, 3),
	'reference' 	=>	&$GLOBALS['TL_LANG']['tl_module']['helpdesk_profmode_options']
);

$GLOBALS['TL_DCA']['tl_module']['fields']['helpdesk_profpage'] = array
(
	'label'			=>	&$GLOBALS['TL_LANG']['tl_module']['helpdesk_profpage'],
	'exclude'		=>	true,
	'inputType'		=>	'text',
	'eval'			=>	array('maxlength'=>100)
);

?>
