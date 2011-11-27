<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Language file for explanations (en)
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

$GLOBALS['TL_LANG']['XPL']['helpdesk_categories_access'] = array(
	array(
		'Private Support', 
		'Ticket support for individuals: The tickets can only be seen by the ticket creator ' .
		'himself in the frontend. Use this type of category for example for formal support of ' .
		'individuals. There is no need to create a category for each client in this case.'
	),
	array(
		'Shared Support', 
		'Ticket support for groups: In this category the clients can see (and in case reply to) ' .
		'each others tickets. A use case for this category type is formal support for a company ' .
		'where multiple staff members will need to see all activities. You would in this case ' .
		'create a distinct shared category for every company.'
	),
	array(
		'Public Support', 
		'Public ticket supports: All tickets in this category type can be seen by anybody on the ' .
		'frontend, no matter if logged-in at all ot what groups they belong to. Only registred ' .
		'clients of this category can however open or reply to tickets.'
	),
	array(
		'Restricted Forum', 
		'A common forum which is only visible to registred members, and hidden from the public.'
	),
	array(
		'Public Forum',
		'A common forum which can be seen by everybody, no matter if logged-in or what member groups '.
		'he/she belongs to. Only registred members of the category can however create or reply to topics.'
	)
);

?>