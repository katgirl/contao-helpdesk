<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Data container array for table tl_helpdesk_categories
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

require_once(dirname(dirname(__FILE__)).'/HelpdeskConstants.php');

/**
 * Table tl_helpdesk_categories
 */
$GLOBALS['TL_DCA']['tl_helpdesk_categories'] = array(

	// Config
	'config' => array(
		'dataContainer'			=>	'HelpdeskTable',
		'enableVersioning'		=> true
	),
	
	// List
	'list' => array(
		'sorting' => array(
			'mode'				=>	0,
			'fields'			=>	array('sorting','access','title'),
			'panelLayout'		=>	'filter,limit'
		),
		'label' => array(
			'fields'			=>	array('title', 'tstamp','client','supporter','status','subject'),
			'format'			=>	'%s',
			'label_callback'	=>	array('tl_helpdesk_categories', 'listCategories')
		),
		'operations' => array(
			'edit' => array(
				'label'			=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['edit'],
				'href'			=> 'act=edit',
				'icon'			=>	'edit.gif'
			),
			'copy' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['tl_helpdesk_categories']['copy'],
				'href'			=> 'act=copy',
				'icon'			=> 'copy.gif'
			),
			'delete' => array(
				'label'			=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['delete'],
				'href'			=>	'act=delete',
				'icon'			=>	'delete.gif',
				'attributes'	=>	'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'orderup' => array
			(
				'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['orderup'],
				'href'				=>	'act=orderup',
				'button_callback'	=>	array('tl_helpdesk_categories', 'orderupButton')
			),
			'orderdown' => array
			(
				'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['orderdown'],
				'href'				=>	'act=orderdown',
				'button_callback'	=>	array('tl_helpdesk_categories', 'orderdownButton')
			),
			'notify' => array
			(
				'button_callback'	=>	array('tl_helpdesk_categories', 'notifyButton')
			),
			'import' => array
			(
				'button_callback'	=>	array('tl_helpdesk_categories', 'importButton')
			),
			'publish' => array
			(
				'button_callback'	=>	array('tl_helpdesk_categories', 'publishButton')
			)
		)
	),

	// Palettes
	'palettes' => array(
		'__selector__'	=>	array('atch', 'notify', 'import'),
		'default'		=>	'header,title,description,buttons,access,replyonly,notify_fe_url,feed,published;'.
							'fe_clients,fe_supporters,be_clients,be_supporters;atch;notify;import'
	),

	// Subpalettes
	'subpalettes' => array (
		'atch'			=>	'atch_dir,atch_size,atch_types',
		'notify'		=>	'notify_astext,notify_atch,notify_name,notify_sender,notify_be_url,notify_newsubj,notify_newtext,notify_replysubj,notify_replytext',
		'import'		=>	'import_atch,import_type,import_server,import_port,import_tls,import_username,import_password,import_email'
	),

	// Fields
	'fields' => array(	
		'header' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['header'],
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('rgxp'=>'extnd', 'maxlength'=>100)
		),
		'title' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['title'],
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('mandatory'=>true, 'rgxp'=>'extnd', 'maxlength'=>100)
		),
		'description' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['description'],
			'exclude'			=>	true,
			'inputType'			=>	'textarea',
			'eval'				=>	array('rte'=>'tinyMCE')
		),
		'buttons' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['buttons'],
			'default'			=>	HELPDESK_DEFAULTEDITBUTTONS,
			'exclude'			=>	true,
			'inputType'			=>	'textarea',
			'eval'				=>	array('mandatory'=>true, 'allowHtml'=>true, 'style'=>'height:80px')
		),
		'access' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['access'],
			'exclude'			=>	true,
			'filter'			=>	true,
			'inputType'			=>	'select',
			'options'			=>	array(4, 3, 2, 1, 0),
			'reference' 		=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['access_options'],
			'explanation'		=>	'helpdesk_categories_access',
			'eval'				=>	array('mandatory'=>true, 'helpwizard'=>true)
		),
		'replyonly' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['replyonly'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox'
		),
		'published' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['published'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox'
		),
		'fe_clients' => array
		(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['fe_clients'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox',
			'foreignKey'		=>	'tl_member_group.name',
			'eval'				=>	array('multiple'=>true)
		),
		'fe_supporters' => array
		(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['fe_supporters'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox',
			'foreignKey'		=>	'tl_member_group.name',
			'eval'				=>	array('multiple'=>true)
		),
		'be_clients' => array
		(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['be_clients'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox',
			'foreignKey'		=>	'tl_user_group.name',
			'eval'				=>	array('multiple'=>true)
		),
		'be_supporters' => array
		(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['be_supporters'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox',
			'foreignKey'		=>	'tl_user_group.name',
			'eval'				=>	array('multiple'=>true)
		),
		'feed' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['feed'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox'
		),
		'atch' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['atch'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox',
			'eval'				=>	array('submitOnChange'=>true)
		),
		'atch_dir' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['atch_dir'],
			'default'			=>	'tl_files/helpdesk',
			'exclude'			=>	true,
			'inputType'			=>	'fileTree',
			'eval'				=>	array('fieldType'=>'radio', 'files'=>false, 'mandatory'=>true)
		),
		'atch_size' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['atch_size'],
			'default'			=>	'100000',
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('mandatory'=>true, 'rgxp'=>'digit', 'maxlength'=>10)
		),
		'atch_types' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['atch_types'],
			'default'			=>	'txt,zip,rar,pdf,jpg,png,gif',
			'load_callback'		=>	array(array('tl_helpdesk_categories', 'loadDefault')),
			'save_callback'		=>	array(array('tl_helpdesk_categories', 'loadDefault')),
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('mandatory'=>true, 'maxlength'=>255)
		),
		'notify' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox',
			'eval'				=>	array('submitOnChange'=>true)
		),
		'notify_astext' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_astext'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox'
		),
		'notify_atch' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['atch'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox'
		),
		'notify_name' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_name'],
			'default'			=>	'Contao Forum/Helpdesk Mailer',
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('maxlength'=>100)
		),
		'notify_sender' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_sender'],
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('rgxp'=>'extnd', 'maxlength'=>100)
		),
		'notify_fe_url' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_fe_url'],
			'default'			=>	'http://www.example.com/helpdesk',
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('maxlength'=>100)
		),
		'notify_be_url' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_be_url'],
			'default'			=>	'http://www.example.com/Contao/main.php?do=helpdesk',
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('maxlength'=>100)
		),
		'notify_newsubj' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_newsubj'],
			'default'			=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_subject']['new'],
			'load_callback'		=>	array(array('tl_helpdesk_categories', 'loadDefault')),
			'save_callback'		=>	array(array('tl_helpdesk_categories', 'loadDefault')),
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('mandatory'=>true, 'rgxp'=>'extnd', 'maxlength'=>100)
		),
		'notify_newtext' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_newtext'],
			'default'			=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_text']['new'],
			'load_callback'		=>	array(array('tl_helpdesk_categories', 'loadDefault')),
			'save_callback'		=>	array(array('tl_helpdesk_categories', 'loadDefault')),
			'exclude'			=>	true,
			'inputType'			=>	'textarea',
			'eval'				=>	array('mandatory'=>true, 'allowHtml'=>true, 'style'=>'height:150px')
		),
		'notify_replysubj' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_replysubj'],
			'default'			=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_subject']['reply'],
			'load_callback'		=>	array(array('tl_helpdesk_categories', 'loadDefault')),
			'save_callback'		=>	array(array('tl_helpdesk_categories', 'loadDefault')),
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('mandatory'=>true, 'rgxp'=>'extnd', 'maxlength'=>100)
		),
		'notify_replytext' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_replytext'],
			'default'			=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['notify_text']['reply'],
			'load_callback'		=>	array(array('tl_helpdesk_categories', 'loadDefault')),
			'save_callback'		=>	array(array('tl_helpdesk_categories', 'loadDefault')),
			'exclude'			=>	true,
			'inputType'			=>	'textarea',
			'eval'				=>	array('mandatory'=>true, 'allowHtml'=>true, 'style'=>'height:150px')
		),
		'import' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['import'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox',
			'eval'				=>	array('submitOnChange'=>true)
		),
		'import_atch' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['atch'],
			'exclude'			=>	true,
			'inputType'			=>	'checkbox'
		),
		'import_server' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['import_server'],
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('nospace'=>true,'maxlength'=>100)
		),
		'import_port' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['import_port'],
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('nospace'=>true,'rgxp'=>'digit','maxlength'=>5)
		),
		'import_type' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['import_type'],
			'exclude'			=>	true,
			'inputType'			=>	'select',
			'options'			=>	array(0, 1),
			'reference' 		=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['import_types']
		),
		'import_tls' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['import_tls'],
			'exclude'			=>	true,
			'inputType'			=>	'select',
			'options'			=>	array(0, 1, 2, 3, 4, 5, 6),
			'reference' 		=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['import_tlsopts']
		),
		'import_username' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['import_username'],
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('maxlength'=>100)
		),
		'import_password' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['import_password'],
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('maxlength'=>100)
		),
		'import_email' => array(
			'label'				=>	&$GLOBALS['TL_LANG']['tl_helpdesk_categories']['import_email'],
			'exclude'			=>	true,
			'inputType'			=>	'text',
			'eval'				=>	array('nospace'=>true,'maxlength'=>100)
		)
	)
);

/**
 * Class tl_helpdesk_categories
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_helpdesk_categories extends Backend
{
	private $firstId = null;
	private	$lastId = null;
	
	/**
	 * List a particular record
	 */
	public function listCategories($row)
	{
		$link = $this->Environment->script . '?do=helpdesk_structure&act=edit&id=' . $row['id'];
		return 
			'<a class="helpdesk-categorylist" href="'.$link.'"><div>' .
				'<div class="icon floatleft">' .
					HelpdeskTheme::createImage('category'.$row['access'].'16', 'access', 'class="icon"') .
				'</div>' .
				'<div class="main">'.$row['title'].'</div>' .
			'</div></a>';
	} // listCategories

	/**
	 * Create the notify on/off button
	 */
	public function notifyButton($row, $href, $label, $title, $icon, $attributes)
	{
		if ($row['notify']=='1') {		
			$href = 'act=dis_notify';
			$label = &$GLOBALS['TL_LANG']['tl_helpdesk_categories']['dis_notify'];
			$icon = 'notify116';
		} else {
			$href = 'act=ena_notify';
			$label = &$GLOBALS['TL_LANG']['tl_helpdesk_categories']['ena_notify'];
			$icon = 'notify016';
		} // if
		$title = sprintf($label[1], $row['id']);
		return 
			'<a href="' . $this->addToUrl($href.'&id='.$row['id']) . 
			 '" title="' . specialchars($title) . '"' . $attributes . '>' . 
			 HelpdeskTheme::createImage($icon, $label[0]) . '</a> ';
	} // notifyButton
	
	/**
	 * Create the import on/off button
	 */
	public function importButton($row, $href, $label, $title, $icon, $attributes)
	{
		if ($row['import']=='1') {		
			$href = 'act=dis_import';
			$label = &$GLOBALS['TL_LANG']['tl_helpdesk_categories']['dis_import'];
			$icon = 'import116';
		} else {
			$href = 'act=ena_import';
			$label = &$GLOBALS['TL_LANG']['tl_helpdesk_categories']['ena_import'];
			$icon = 'import016';
		} // if
		$title = sprintf($label[1], $row['id']);
		return 
			'<a href="' . $this->addToUrl($href.'&id='.$row['id']) . 
			 '" title="' . specialchars($title) . '"' . $attributes . '>' . 
			 HelpdeskTheme::createImage($icon, $label[0]) . '</a> ';
	} // importButton
	
	/**
	 * Create the publish/unpublish button
	 */
	public function publishButton($row, $href, $label, $title, $icon, $attributes)
	{
		if ($row['published']=='1') {		
			$href = 'act=unpublish';
			$label = &$GLOBALS['TL_LANG']['tl_helpdesk_categories']['unpublish'];
			$icon = 'published16';
		} else {
			$href = 'act=publish';
			$label = &$GLOBALS['TL_LANG']['tl_helpdesk_categories']['publish'];
			$icon = 'unpublished16';
		} // if
		$title = sprintf($label[1], $row['id']);
		return 
			'<a href="' . $this->addToUrl($href.'&id='.$row['id']) . 
			 '" title="' . specialchars($title) . '"' . $attributes . '>' . 
			 HelpdeskTheme::createImage($icon, $label[0]) . '</a> ';
	} // publishButton

	/**
	 * Order down button
	 */
	public function orderdownButton($row, $href, $label, $title, $icon, $attributes)
	{
		if (is_null($this->lastId)) {
			$q = $this->Database->prepare(
				"select `id` from `tl_helpdesk_categories` order by `sorting` desc, `access` desc, `title` desc"
			)->limit(1)->execute();
			if ($q->next()) $this->lastId = $q->id;
		} // if
		if ($row['id'] == $this->lastId) return HelpdeskTheme::createImage('orderdown016', $label).' ';
		return 
			'<a href="' . $this->addToUrl($href.'&id='.$row['id']) . 
			 '" title="' . specialchars($title) . '"' . $attributes . '>' . 
			 HelpdeskTheme::createImage('orderdown116', $label) . '</a> ';
	} // publishButton

	/**
	 * Order up button
	 */
	public function orderupButton($row, $href, $label, $title, $icon, $attributes)
	{
		if (is_null($this->firstId)) {
			$q = $this->Database->prepare(
				"select `id` from `tl_helpdesk_categories` order by `sorting`, `access`, `title`"
			)->limit(1)->execute();
			if ($q->next()) $this->firstId = $q->id;
		} // if
		if ($row['id'] == $this->firstId) return HelpdeskTheme::createImage('orderup016', $label).' ';
		return 
			'<a href="' . $this->addToUrl($href.'&id='.$row['id']) . 
			 '" title="' . specialchars($title) . '"' . $attributes . '>' . 
			 HelpdeskTheme::createImage('orderup116', $label) . '</a> ';
	} // orderupButton

	/**
	 * Load default value if empty
	 */
	public function loadDefault($varValue, DataContainer $dc)
	{
		if (!strlen(trim($varValue))) 
			return $GLOBALS['TL_DCA']['tl_helpdesk_categories']['fields'][$dc->inputName]['default'];
		return $varValue;
	} // loadDefault
	
} // class tl_helpdesk_categories

?>
