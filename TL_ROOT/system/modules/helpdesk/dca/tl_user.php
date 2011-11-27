<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Data container array for table tl_user
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */
class helpdesk_user extends Backend
{
	/**
	 *	Add helpdesk subscriptions to a palette
	 */
	public function addToPalette($pal, $before=null)
	{
		if ($before)
			$GLOBALS['TL_DCA']['tl_user']['palettes'][$pal] = 
				str_replace(
					$before, 
					';{helpdesk_legend:hide},helpdesk_timezone,helpdesk_role,helpdesk_showrealname,helpdesk_location,helpdesk_showlocation,helpdesk_signature,helpdesk_subscriptions'.$before, 
					$GLOBALS['TL_DCA']['tl_user']['palettes'][$pal]
				);
		else
			$GLOBALS['TL_DCA']['tl_user']['palettes'][$pal] .= ';{helpdesk_legend:hide},helpdesk_timezone,helpdesk_showrealname,helpdesk_location,helpdesk_showlocation,helpdesk_signature,helpdesk_subscriptions';
	} // addToPalette

	/**
	 * Get options list for subscriptions
	 */
	public function getSubscriptionOptions($objUser)
	{
		$options = array();
		$options[999998] = &$GLOBALS['TL_LANG']['tl_user']['helpdesk_notifymytickets'];
		$options[999999] = &$GLOBALS['TL_LANG']['tl_user']['helpdesk_notifymyreplies'];
		
		// get this members groups
		if ($objUser instanceof DataContainer) {
			// If called from the back end, the second argument is a DataContainer object
			$objUser = $this->Database->prepare("SELECT * FROM `tl_user` WHERE `id`=?")
						->limit(1)->execute($objUser->id);
			if ($objUser->numRows < 1) return $options;
		} // if
		$groups = is_array($objUser->groups) ? $objUser->groups : Helpdesk::unpackArray($objUser->groups);
		
		// get list of categories this users groups are authorized for
		$q = $this->Database->prepare(
			"SELECT `id`, `title`, `be_clients`, `be_supporters` FROM `tl_helpdesk_categories` ORDER BY `sorting`, `access`, `title`"
		)->execute();
		while ($q->next()) {
			if ($objUser->admin)
				$app = true;
			else
				$app  = Helpdesk::matchGroupsP($q->be_clients, $groups) || 
						Helpdesk::matchGroupsP($q->be_supporters, $groups); 
			if ($app) 
				$options[$q->id] = 
					sprintf(
						$GLOBALS['TL_LANG']['tl_user']['helpdesk_allincategory'], 
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

} // helpdesk_user

/**
 * Add palettes
 */
$this->import('helpdesk_user');
$this->helpdesk_user->addToPalette('login');
foreach (array('admin','default','group','extend','custom') as $pal) 
	$this->helpdesk_user->addToPalette($pal, ';{account_legend},disable,start,stop');

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['helpdesk_timezone'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_user']['helpdesk_timezone'],
	'exclude'			=> true,
	'inputType'			=> 'select',
	'options_callback'	=> array('HelpdeskSettings','getTimezoneOptions')
);

$GLOBALS['TL_DCA']['tl_user']['fields']['helpdesk_role'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_user']['helpdesk_role'],
	'exclude'			=> true,
	'search'			=> true,
	'sorting'			=> true,
	'inputType'			=> 'text',
	'eval'				=> array('maxlength'=>64)
);

$GLOBALS['TL_DCA']['tl_user']['fields']['helpdesk_location'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_user']['helpdesk_location'],
	'exclude'			=> true,
	'search'			=> true,
	'sorting'			=> true,
	'inputType'			=> 'text',
	'eval'				=> array('maxlength'=>64)
);

$GLOBALS['TL_DCA']['tl_user']['fields']['helpdesk_showrealname'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_user']['helpdesk_showrealname'],
	'exclude'			=> true,
	'inputType'			=> 'select',
	'options'           => array(0, 1),
	'reference' 		=> &$GLOBALS['TL_LANG']['tl_user']['helpdesk_noyes']
);

$GLOBALS['TL_DCA']['tl_user']['fields']['helpdesk_showlocation'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_user']['helpdesk_showlocation'],
	'exclude'			=> true,
	'inputType'			=> 'select',
	'options'           => array(0, 1),
	'reference' 		=> &$GLOBALS['TL_LANG']['tl_user']['helpdesk_noyes']
);

$GLOBALS['TL_DCA']['tl_user']['fields']['helpdesk_signature'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_user']['helpdesk_signature'],
	'exclude'			=> true,
	'inputType'			=> 'textarea',
	'eval'				=> array('decodeEntities'=>'true', 'style'=>'height:60px;width:400px;')
);

$GLOBALS['TL_DCA']['tl_user']['fields']['helpdesk_subscriptions'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_user']['helpdesk_subscriptions'],
	'exclude'			=> true,
	'inputType'			=> 'checkbox',
	'options_callback'	=> array('helpdesk_user','getSubscriptionOptions'),
	'save_callback'		=> array(array('helpdesk_user', 'saveSubscriptions')),
	'eval'				=> array('multiple'=>true)
);


?>