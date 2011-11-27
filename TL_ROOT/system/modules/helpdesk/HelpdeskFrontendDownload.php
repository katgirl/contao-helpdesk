<?php
/**
 * TYPOlight Helpdesk :: Class HelpdeskFrontendDownload
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

define('TL_MODE', 'FE');
require_once('../../initialize.php');

class HelpdeskFrontendDownload extends Frontend
{
	public	$username;
	public	$role;
	public	$backend;
	
	public function __construct()
	{
		$this->backend = false;
		$this->import('FrontendUser', 'User');
		parent::__construct();
		$this->User->authenticate();
	} // __construct

	public function run()
	{
		$dl = new HelpdeskDownload($this);
		$dl->run();
	} // run
	
	/**
	 * Autorize user
	 */
	public function authorize(&$q)
	{
		$this->username = $this->User->username;
		$this->role = HELPDESK_GUEST;
		if (!is_array($this->User->groups)) return;
		if (Helpdesk::matchGroupsP($q->cat_fe_supporters, $this->User->groups)) {
			$this->role = HELPDESK_SUPPORTER;
			return;
		} // if
		if (Helpdesk::matchGroupsP($q->cat_fe_clients, $this->User->groups))
			$this->role = HELPDESK_CLIENT;
	} // authorize
		
} // class HelpdeskFrontendDownload

/**
 * Instantiate controller
 */
$objDownload = new HelpdeskFrontendDownload();
$objDownload->run();

?>