<?php
/**
 * TYPOlight Helpdesk :: Download file attachments from backend
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once('../../initialize.php');

/**
 * Class HelpdeskBackendDownload
 */
class HelpdeskBackendDownload extends Backend
{
	public	$username;
	public	$role;
	public	$backend;
	
	public function __construct()
	{
		$this->backend = true;
		$this->import('BackendUser', 'User');
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
		if ($this->User->isAdmin) {
			$this->role = HELPDESK_ADMIN;
			return;
		} // if
		$this->role = HELPDESK_GUEST;
		if (!is_array($this->User->groups)) return;
		if (Helpdesk::matchGroupsP($q->cat_be_supporters, $this->User->groups)) {
			$this->role = HELPDESK_SUPPORTER;
			return;
		} // if
		if (Helpdesk::matchGroupsP($q->cat_be_clients, $this->User->groups))
			$this->role = HELPDESK_CLIENT;
	} // authorize
		
} // class HelpdeskBackendDownload

/**
 * Instantiate controller
 */
$objDownload = new HelpdeskBackendDownload();
$objDownload->run();

?>