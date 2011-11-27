<?php
/**
 * TYPOlight Helpdesk :: Class DC_HelpdeskTable
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

require_once 'DC_Table.php';

/**
 * Class DC_HelpdeskTable
 *
 * Provide methods to access and modify data stored in a table.
 * @copyright  Acenes 2007
 * @author     Acenes
 * @package    Helpdesk
 */
class DC_HelpdeskTable extends DC_Table
{

    /**
	 * Initialize the object
	 * @param string
	 */
    public function __construct($strTable)
    {
		parent::__construct($strTable);
    }
	
	/**
	 * Return an object property
	 */
	public function __get($strKey)
	{
		switch ($strKey) {
			case 'firstOrderBy':
				return $this->firstOrderBy;
				break;
			case 'inputName':
				return $this->strInputName;
				break;
			default:
				return parent::__get($strKey);
		} // switch
	} // __get

	public function wasEdited()
	{
		return $this->blnCreateNewVersion;
	} // wasEdited
	
	public function publish()
	{
		if ($this->intId) 
			$this->Database->prepare(
				"UPDATE " . $this->strTable . " SET `published`='1' WHERE id=?"
			)->execute($this->intId);
		$this->redirect($this->getReferer());
	} // publish
	
	public function unpublish()
	{
		if ($this->intId) 
			$this->Database->prepare(
				"UPDATE " . $this->strTable . " SET `published`='0' WHERE id=?"
			)->execute($this->intId);
		$this->redirect($this->getReferer());
	} // unpublish
	
	public function ena_notify()
	{
		if ($this->intId) 
			$this->Database->prepare(
				"UPDATE " . $this->strTable . " SET `notify`='1' WHERE id=?"
			)->execute($this->intId);
		$this->redirect($this->getReferer());
	} // ena_notify
	
	public function dis_notify()
	{
		if ($this->intId) 
			$this->Database->prepare(
				"UPDATE " . $this->strTable . " SET `notify`='0' WHERE id=?"
			)->execute($this->intId);
		$this->redirect($this->getReferer());
	} // dis_notify
	
	public function ena_import()
	{
		if ($this->intId) 
			$this->Database->prepare(
				"UPDATE " . $this->strTable . " SET `import`='1' WHERE id=?"
			)->execute($this->intId);
		$this->redirect($this->getReferer());
	} // ena_import
	
	public function dis_import()
	{
		if ($this->intId) 
			$this->Database->prepare(
				"UPDATE " . $this->strTable . " SET `import`='0' WHERE id=?"
			)->execute($this->intId);
		$this->redirect($this->getReferer());
	} // dis_import
	
	public function orderdown()
	{
		if ($this->intId) {
			// find current position
			$q = $this->Database->prepare(
				"select `id`, `sorting` from " . $this->strTable . " order by `sorting`, `access`, `title`"
			)->execute();
			$pos = $next = 1;
			$sortings = array();
			while ($q->next()) {
				if ($q->id == $this->intId) {
					$curr = ++$pos;
				} else {
					$curr = $next;
					$next = ++$pos;
				} // if
				if ($q->sorting != $curr) $sortings[$q->id] = $curr;
			} // while
			foreach ($sortings as $id => $sorting)
				$this->Database->prepare(
					"UPDATE " . $this->strTable . " SET `sorting`=$sorting WHERE id=?"
				)->execute($id);
		} // if
		$this->redirect($this->getReferer());
	} // orderdown
	
	public function orderup()
	{
		if ($this->intId) {
			// find current position
			$q = $this->Database->prepare(
				"select `id`, `sorting` from " . $this->strTable . " order by `sorting` desc, `access` desc, `title` desc"
			)->execute();
			$pos = $next = 1;
			$sortings = array();
			while ($q->next()) {
				if ($q->id == $this->intId) {
					$curr = ++$pos;
				} else {
					$curr = $next;
					$next = ++$pos;
				} // if
				$sortings[] = array($q->id, $q->sorting, $curr);
			} // while
			foreach ($sortings as $s) {
				$sorting = $pos-$s[2];
				if ($s[1] != $sorting) 
					$this->Database->prepare(
						"UPDATE " . $this->strTable . " SET `sorting`=$sorting WHERE id=?"
					)->execute($s[0]);
			} // foreach
		} // if
		$this->redirect($this->getReferer());
	} // orderup
	
	public function synchronize()
	{
		$this->import('HelpdeskSettings');
		$this->HelpdeskSettings->syncAll();
		$this->redirect($this->getReferer());
	} // synchronize
	
} // class DC_HelpdeskTable

?>