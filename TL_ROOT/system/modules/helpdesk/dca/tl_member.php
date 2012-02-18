<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Data container array for table tl_member
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */
require_once(dirname(dirname(__FILE__)).'/HelpdeskConstants.php');

/**
 * Add palette
 */
$GLOBALS['TL_DCA']['tl_member']['palettes']['default'] = 
	str_replace(
		';{account_legend},disable,start,stop', 
		';{helpdesk_legend:hide},helpdesk_timezone,helpdesk_role,helpdesk_showrealname,helpdesk_showlocation,helpdesk_signature,helpdesk_subscriptions;{account_legend},disable,start,stop', 
		$GLOBALS['TL_DCA']['tl_member']['palettes']['default']
	);

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['helpdesk_timezone'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_member']['helpdesk_timezone'],
	'exclude'			=> true,
	'inputType'			=> 'select',
	'options_callback'	=> array('HelpdeskSettings','getTimezoneOptions'),
	'eval'				=> array('feEditable'=>true, 'feGroup'=>'helpdesk')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['helpdesk_role'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_member']['helpdesk_role'],
	'exclude'			=> true,
	'search'			=> true,
	'sorting'			=> true,
	'inputType'			=> 'text',
	'eval'				=> array('feEditable'=>true, 'feGroup'=>'helpdesk', 'maxlength'=>64, 'insertTag'=>true)
);

$GLOBALS['TL_DCA']['tl_member']['fields']['helpdesk_showrealname'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_member']['helpdesk_showrealname'],
	'exclude'			=> true,
	'inputType'			=> 'select',
	'options'           => array(0, 1),
	'reference' 		=> &$GLOBALS['TL_LANG']['tl_member']['helpdesk_noyes'],
	'eval'				=> array('feEditable'=>true, 'feGroup'=>'helpdesk')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['helpdesk_showlocation'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_member']['helpdesk_showlocation'],
	'exclude'			=> true,
	'inputType'			=> 'select',
	'options'           => array(0, 1),
	'reference' 		=> &$GLOBALS['TL_LANG']['tl_member']['helpdesk_noyes'],
	'eval'				=> array('feEditable'=>true, 'feGroup'=>'helpdesk')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['helpdesk_signature'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_member']['helpdesk_signature'],
	'exclude'			=> true,
	'inputType'			=> 'textarea',
	'eval'				=> array('feEditable'=>true, 'feGroup'=>'helpdesk', 'decodeEntities'=>'true', 'style'=>'height:60px;width:400px;')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['helpdesk_subscriptions'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_member']['helpdesk_subscriptions'],
	'exclude'			=> true,
	'inputType'			=> 'checkbox',
	'options_callback'	=> array('helpdesk_member','getSubscriptionOptions'),
	'save_callback'		=> array(array('helpdesk_member', 'saveSubscriptions')),
	'eval'				=> array('multiple'=>true, 'feEditable'=>true, 'feGroup'=>'helpdesk')
);

class helpdesk_member extends Backend
{

	/**
	 * Get options list for subscriptions
	 */
	public function getSubscriptionOptions($objUser)
	{
		// echo '<pre>'; print_r($objUser);
		
		$options = array();
		$options[999998] = &$GLOBALS['TL_LANG']['tl_member']['helpdesk_notifymytickets'];
		$options[999999] = &$GLOBALS['TL_LANG']['tl_member']['helpdesk_notifymyreplies'];
		
		// get this members groups
		if ($objUser instanceof ModulePersonalData) {
			$this->import('FrontendUser', 'User');
			$objUser = &$this->User;
		} else
		if ($objUser instanceof DataContainer) {
			// If called from the back end, the second argument is a DataContainer object
			$objUser = $this->Database->prepare("SELECT * FROM `tl_member` WHERE `id`=?")
						->limit(1)->execute($objUser->id);
			if ($objUser->numRows < 1) return $options;
		} // if
 
		$groups = is_array($objUser->groups) ? $objUser->groups : Helpdesk::unpackArray($objUser->groups);
		
		// get list of categories this members groups are authorized for
		$q = $this->Database->prepare(
			"SELECT `id`, `title`, `access`, `fe_clients`, `fe_supporters` FROM `tl_helpdesk_categories` ORDER BY `sorting`, `access`, `title`"
		)->execute();
		while ($q->next()) {
			if (intval($q->access)==HELPDESK_PUBLIC_FORUM	|| 
				intval($q->access)==HELPDESK_PUBLIC_SUPPORT	|| 
				Helpdesk::matchGroupsP($q->fe_clients, $groups) || 
				Helpdesk::matchGroupsP($q->fe_supporters, $groups)) 
				$options[$q->id] = 
					sprintf(
						$GLOBALS['TL_LANG']['tl_member']['helpdesk_allincategory'], 
						(strlen($q->title) > 30) ? (substr($q->title,0,28).'...') : $q->title
					);
		} // while
		return $options;
	} // getSubscriptionOptions
	
	public function saveSubscriptions($varValue, $objUser)
	{
		if (is_array($varValue)) return serialize($varValue);
		return $varValue;
	} // saveSubscriptions

} // helpdesk_member

?>
