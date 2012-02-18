<?php
/**
 * Contao Helpdesk :: Manually run the notification batch.
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

define('TL_MODE', 'BE');
require_once('../../initialize.php');

global $cronJob;

$cronJob = array(
	'id'		=> 1,
	'title'		=> 'Debug Notification',
	'lastrun'	=> time()-100,
	'endtime'	=> time()+100,
	'runonce'	=> false,
	'logging'	=> true,
	'completed'	=> true
);

require_once('HelpdeskNotify.php');
?>
