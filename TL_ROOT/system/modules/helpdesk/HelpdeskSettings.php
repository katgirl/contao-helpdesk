<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Settings
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskSettings extends System
{
	protected static $objInstance;
	protected static $tzNames;
	protected $arrCache = array();

	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final private function __clone() {}


	/**
	 * Prevent direct instantiation (Singleton)
	 */
	protected function __construct() 
	{
		parent::__construct();		
		$this->import('Database');
		$this->loadLanguageFile('helpdesk');
		try {	
			$q = $this->Database->execute("select * from `tl_helpdesk_settings` where `id`='0'");
			if ($q->next() && intval($q->version)>0)
				$this->arrCache = $q->row();
			$this->arrCache['updaterequired'] = false;
		} // try
		catch (Exception $exc) {
			$this->arrCache['updaterequired'] = true;
		} // catch
		$this->setInt('version');
		$this->setInt('tpage', 30);
		$this->setInt('mpage', 15);
		$this->setInt('spage', 10);
		$this->setInt('edits', 2);
		$this->setInt('postdelay', 15);
		$this->setInt('searchdelay', 30);
		$this->setInt('searchmax', 100);
		$this->setInt('recenthours', 24);
		$this->setInt('pagenavctl', 1);
		$this->setInt('pagenavsize', 7);
		$this->setText('images');
		$this->setBoolean('feeds');
		$this->setInt('feedmax', 10);
		$this->setInt('feedlimit', 150);
		$this->setText('feedlink', 'helpdesk');
		$this->setText('feedtitle', 'TYPOlight Syndication');
		$this->setText('feeddescription', 'RSS feeds from the forums and helpdesks');
		$this->setInt('logging', HELPDESK_NOLOG);
		$this->migrate();
	} // construct
	
	public function __get($aKey)
	{
		return $this->arrCache[$aKey];
	} // __get
	
	public function __set($aKey, $aValue)
	{
		$this->arrCache[$aKey] = $aValue;
	} // __set
	
	private function setInt($aName, $aDef = 0)
	{
		$a = &$this->arrCache;
		$a[$aName] = 
			isset($a[$aName]) 
				? intval($a[$aName]) 
				: (isset($GLOBALS['TL_CONFIG']['helpdesk_'.$aName])
					? intval($GLOBALS['TL_CONFIG']['helpdesk_'.$aName])
					: $aDef);
	} // setInt
	
	private function setBoolean($aName, $aDef = false)
	{
		$a = &$this->arrCache;
		$a[$aName] = 
			isset($a[$aName]) 
				? intval($a[$aName])!=0 
				: (isset($GLOBALS['TL_CONFIG']['helpdesk_'.$aName])
					? intval($GLOBALS['TL_CONFIG']['helpdesk_'.$aName])!=0
					: $aDef);
	} // setBoolean
	
	private function setText($aName, $aDef = '')
	{
		$a = &$this->arrCache;
		$a[$aName] = 
			isset($a[$aName]) 
				? trim($a[$aName]) 
				: (isset($GLOBALS['TL_CONFIG']['helpdesk_'.$aName])
					? trim($GLOBALS['TL_CONFIG']['helpdesk_'.$aName])
					: $aDef);
	} // setText
	
	private function migrate()
	{
		$a = &$this->arrCache;
		if ($a['updaterequired'] || $a['version']>=HELPDESK_RELEASE) return;
		$db = &$this->Database;
		
		if ($a['version'] == 0) { // before 0.7.1
			// column changes in 0.4.0
			if (!$db->fieldExists('fe_clients', 'tl_helpdesk_categories'))
				$db->execute("ALTER TABLE `tl_helpdesk_categories` CHANGE `clients` `fe_clients` blob NULL");
			
			if (!$db->fieldExists('be_supporters', 'tl_helpdesk_categories'))
				$db->execute("ALTER TABLE `tl_helpdesk_categories` CHANGE `supporters` `be_supporters` blob NULL");
			
			if (!$db->fieldExists('poster_cd', 'tl_helpdesk_messages')) {
				$db->execute("ALTER TABLE `tl_helpdesk_messages` CHANGE `answer` `poster_cd` char(1) NOT NULL default '0'");
				$db->execute("UPDATE `tl_helpdesk_messages` SET `poster_cd`='3' WHERE `poster_cd`='1'");
			} // if
			
			// column changes in 0.4.1
			if ($db->fieldExists('atch', 'tl_helpdesk_categories'))
				$db->execute("UPDATE `tl_helpdesk_categories` SET `atch`='' WHERE `atch`='0'");
				
			if ($db->fieldExists('notify', 'tl_helpdesk_categories'))
				$db->execute("UPDATE `tl_helpdesk_categories` SET `notify`='' WHERE `notify`='0'");
				
			if ($db->fieldExists('import', 'tl_helpdesk_categories'))
				$db->execute("UPDATE `tl_helpdesk_categories` SET `import`='' WHERE `import`='0'");

			// configurable buttons in 0.7.0
			if ($db->fieldExists('buttons', 'tl_helpdesk_categories')) {
				$db	->prepare("UPDATE `tl_helpdesk_categories` SET `buttons`=?")
					->execute(HELPDESK_DEFAULTEDITBUTTONS);
			} else {
				$a['updaterequired'] = true;
				return;
			} // if
		} // if
		
		// save current version in db settings
		$a['version'] = HELPDESK_RELEASE;
		$q = $db->prepare("update `tl_helpdesk_settings` %s where `id`='0'")
				->set(array(
					'tstamp'	=> time(),
					'version'	=> $a['version']
				  ))
				->execute();
		if ($q->affectedRows == 0) {
			// create new settings
			$db	->prepare("insert into `tl_helpdesk_settings` %s")
				->set(array(
					'id'			=> 0,
					'tstamp'		=> time(),
					'version'		=> $a['version'],
					'tpage'			=> $a['tpage'],
					'mpage'			=> $a['mpage'],
					'spage'			=> $a['spage'],
					'edits'			=> $a['edits'],
					'postdelay'		=> $a['postdelay'],
					'searchdelay'	=> $a['searchdelay'],
					'searchmax'		=> $a['searchmax'],
					'recenthours'	=> $a['recenthours'],
					'pagenavctl'	=> $a['pagenavctl'],
					'pagenavsize'	=> $a['pagenavsize'],
					'images'		=> $a['images'],
					'feeds'			=> $a['feeds'] ? '1' : '',
					'feedmax'		=> $a['feedmax'],
					'feedlimit'		=> $a['feedlimit'],
					'feedlink'		=> $a['feedlink'],
					'feedtitle'		=> $a['feedtitle'],
					'feeddescription' => $a['feeddescription'],
					'logging'		=> $a['logging']
				  ))
				->execute();
				
			// drop old settings from localconfig.php
			$this->import('Config');
			$this->Config->delete("\$GLOBALS['TL_CONFIG']['helpdesk_tpage']");
			$this->Config->delete("\$GLOBALS['TL_CONFIG']['helpdesk_mpage']");
			$this->Config->delete("\$GLOBALS['TL_CONFIG']['helpdesk_spage']");
			$this->Config->delete("\$GLOBALS['TL_CONFIG']['helpdesk_images']");
			$this->Config->delete("\$GLOBALS['TL_CONFIG']['helpdesk_feeds']");
			$this->Config->delete("\$GLOBALS['TL_CONFIG']['helpdesk_feedmax']");
			$this->Config->delete("\$GLOBALS['TL_CONFIG']['helpdesk_feedlink']");
			$this->Config->delete("\$GLOBALS['TL_CONFIG']['helpdesk_feedtitle']");
			$this->Config->delete("\$GLOBALS['TL_CONFIG']['helpdesk_feeddescription']");
			$this->Config->delete("\$GLOBALS['TL_CONFIG']['helpdesk_logging']");
		} // if
	} // migrate
	 
	
	/**
	 * Return the current object instance (Singleton)
	 * @return object
	 */
	public static function getInstance()
	{
		if (!is_object(self::$objInstance))
			self::$objInstance = new HelpdeskSettings();
		return self::$objInstance;
	} // getInstance
	
	/**
	 * Synchronize a category
	 */
	public function syncCat($aCatId)
	{
		$db = &$this->Database;

		$db	->prepare(
				"\n update `tl_helpdesk_tickets` as `tck` set" .
				"\n `pub_replies`=(" .
						"\n select count(*) from `tl_helpdesk_messages`" .
						"\n where `tck`.`published`='1'" .
						"\n and `tck`.`id`=`pid`" .
						"\n and `published`='1'" .
						"\n and `reply`='1')," .
				"\n `pub_latest`=(" .
						"\n select ifnull(max(`id`),0) from `tl_helpdesk_messages`" .
						"\n where `tck`.`published`='1'" .
						"\n and `tck`.`id`=`pid`" .
						"\n and `published`='1')," .
				"\n `all_replies`=(" .
						"\n select count(*) from `tl_helpdesk_messages`" .
						"\n where `tck`.`id`=`pid` and `reply`='1')," .
				"\n `all_latest`=(" .
						"\n select ifnull(max(`id`),0) from `tl_helpdesk_messages`" .
						"\n where `tck`.`id`=`pid`)," .
				"\n `tstamp`=(" .
						"\n select ifnull(max(`tstamp`),0) from `tl_helpdesk_messages`" .
						"\n where `tck`.`id`=`pid`)" .
				"\n where `tck`.`pid`=?"
			  )
			->execute($aCatId);
		
		$db ->prepare(
				"\n update `tl_helpdesk_categories` as `cat` set" .
				"\n `pub_tickets`=(" .
						"\n select count(*) from `tl_helpdesk_tickets`" .
						"\n where `cat`.`id`=`pid` and `published`='1')," .
				"\n `pub_replies`=(" .
						"\n select ifnull(sum(`pub_replies`),0) from `tl_helpdesk_tickets`" .
						"\n where `cat`.`id`=`pid`)," .
				"\n `pub_latest`=(" .
						"\n select ifnull(max(`pub_latest`),0) from `tl_helpdesk_tickets`" .
						"\n where `cat`.`id`=`pid`)," .
				"\n `all_tickets`=(" .
						"\n select count(*) from `tl_helpdesk_tickets`" .
						"\n where `cat`.`id`=`pid`)," .
				"\n `all_replies`=(" .
						"\n select ifnull(sum(`all_replies`),0) from `tl_helpdesk_tickets`" .
						"\n where `cat`.`id`=`pid`)," .
				"\n `all_latest`=(" .
						"\n select ifnull(max(`all_latest`),0) from `tl_helpdesk_tickets`" .
						"\n where `cat`.`id`=`pid`)" .
				"\n where `cat`.`id`=?"
			  )
			->execute($aCatId);
	} // syncCat
	
	/**
	 * Synchronize all categories
	 */
	public function syncAllCats()
	{
		$db = &$this->Database;

		$db->execute(
			"\n update `tl_helpdesk_tickets` as `tck` set" .
			"\n `pub_replies`=(" .
					"\n select count(*) from `tl_helpdesk_messages`" .
					"\n where `tck`.`published`='1'" .
					"\n and `tck`.`id`=`pid`" .
					"\n and `published`='1'" .
					"\n and `reply`='1')," .
			"\n `pub_latest`=(" .
					"\n select ifnull(max(`id`),0) from `tl_helpdesk_messages`" .
					"\n where `tck`.`published`='1'" .
					"\n and `tck`.`id`=`pid`" .
					"\n and `published`='1')," .
			"\n `all_replies`=(" .
					"\n select count(*) from `tl_helpdesk_messages`" .
					"\n where `tck`.`id`=`pid` and `reply`='1')," .
			"\n `all_latest`=(" .
					"\n select ifnull(max(`id`),0) from `tl_helpdesk_messages`" .
					"\n where `tck`.`id`=`pid`)," .
			"\n `tstamp`=(" .
					"\n select ifnull(max(`tstamp`),0) from `tl_helpdesk_messages`" .
					"\n where `tck`.`id`=`pid`)"
		);
		
		$db->execute(
			"\n update `tl_helpdesk_categories` as `cat` set" .
			"\n `pub_tickets`=(" .
					"\n select count(*) from `tl_helpdesk_tickets`" .
					"\n where `cat`.`id`=`pid` and `published`='1')," .
			"\n `pub_replies`=(" .
					"\n select ifnull(sum(`pub_replies`),0) from `tl_helpdesk_tickets`" .
					"\n where `cat`.`id`=`pid`)," .
			"\n `pub_latest`=(" .
					"\n select ifnull(max(`pub_latest`),0) from `tl_helpdesk_tickets`" .
					"\n where `cat`.`id`=`pid`)," .
			"\n `all_tickets`=(" .
					"\n select count(*) from `tl_helpdesk_tickets`" .
					"\n where `cat`.`id`=`pid`)," .
			"\n `all_replies`=(" .
					"\n select ifnull(sum(`all_replies`),0) from `tl_helpdesk_tickets`" .
					"\n where `cat`.`id`=`pid`)," .
			"\n `all_latest`=(" .
					"\n select ifnull(max(`all_latest`),0) from `tl_helpdesk_tickets`" .
					"\n where `cat`.`id`=`pid`)"
		);
	} // syncAllCats
	
	/**
	 * Synchronize a member
	 */
	public function syncMember($aUsername)
	{
		$db = &$this->Database;
		$db	->prepare(
				"\n update `tl_member` as `mbr` set" .
				"\n `helpdesk_postcount`=(" .
					"\n select count(*) from `tl_helpdesk_messages`" .
					"\n where `mbr`.`username`=`poster` and `poster_cd` in ('0','2'))" .
				"\n where `mbr`.`username`=?"
			  )
			->execute($aUsername);
	} // syncMember
			
	/**
	 * Synchronize all members
	 */
	public function syncAllMembers()
	{
		$db = &$this->Database;
		$db->execute(
			"\n update `tl_member` as `mbr` set" .
			"\n `helpdesk_postcount`=(" .
				"\n select count(*) from `tl_helpdesk_messages`" .
				"\n where `mbr`.`username`=`poster` and `poster_cd` in ('0','2'))"
		);
	} // syncAllMembers
			
	/**
	 * Synchronize a user
	 */
	public function syncUser($aUsername)
	{
		$db = &$this->Database;
		$db	->prepare(
				"\n update `tl_user` as `usr` set" .
				"\n `helpdesk_postcount`=(" .
					"\n select count(*) from `tl_helpdesk_messages`" .
					"\n where `usr`.`username`=`poster` and `poster_cd` in ('1','3'))" .
				"\n where `usr`.`username`=?"
			  )
			->execute($aUsername);
	} // syncUser
	
	/**
	 * Synchronize all users
	 */
	public function syncAllUsers()
	{
		$db = &$this->Database;
		$db->execute(
			"\n update `tl_user` as `usr` set" .
			"\n `helpdesk_postcount`=(" .
				"\n select count(*) from `tl_helpdesk_messages`" .
				"\n where `usr`.`username`=`poster` and `poster_cd` in ('1','3'))"
		);
	} // syncAllUsers
	
	/**
	 * Synchronize overall summaries
	 */
	public function syncTotals()
	{
		$db = &$this->Database;
		$db->execute(
			"\n update `tl_helpdesk_settings` as `stg` set" .
			"\n `tot_tickets`=(select count(*) from `tl_helpdesk_tickets`)," .
			"\n `tot_messages`=(select count(*) from `tl_helpdesk_messages`)," .
			"\n `tot_members`=(select count(*) from `tl_member`)" .
			"\n where `stg`.`id`=0"
		);
	} // syncTotals
	
	/**
	 * Synchronize everything
	 */
	public function syncAll()
	{
		$this->syncAllCats();
		$this->syncAllMembers();
		$this->syncAllUsers();
		$this->syncTotals();
	} // syncAll

	public function isValidTimezone($aName = null)
	{
		if (is_null(self::$tzNames))
			self::$tzNames = timezone_identifiers_list();
		return in_array($aName, self::$tzNames);
	} // isValidTimezone
	
	public function getTimezoneOptions()
	{
		$this->isValidTimezone();
		$lst = array('' => $GLOBALS['TL_LANG']['helpdesk']['useserverdef']);
		foreach (self::$tzNames as $tz) $lst[$tz] = $tz;
		return $lst;
	} // getTimezoneOptions

	public function getCategoryOptions()
	{
		$lst = array();
		$grpname = $GLOBALS['TL_LANG']['helpdesk']['ungrouped'];
		$db = &$this->Database;
		$q = $db->execute("select `id`, `header`, `title` from `tl_helpdesk_categories` order by `sorting`, `access`, `title`");
		while ($q->next()) {
			if ($q->header) $grpname = $q->header;
			$lst[$grpname][$q->id] = $q->title;
		} // while
		return $lst;
	} // getCategoryOptions

} // HelpdeskSettings

?>
