<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Class HelpdeskFrontend
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskFrontend extends Frontend
{
	/**
	 * Add messages to the indexer
	 * @param array
	 * @param integer
	 * @return array
	 */
	public function getSearchablePages($arrPages, $intRoot=0)
	{
		$this->import('HelpdeskSettings', 'settings');
		if (!$this->settings->tlsearch) return $arrPages;

		$db = &$this->Database;	
		
		// get all helpdesk block modules with theire categories
		$s =
			"select `id`, `helpdesk_categories`" .
			"\n from `tl_module`" .
			"\n where `type`='helpdesk'";
		$q = $db->prepare($s)->execute();
		$hcats = array();
		while ($q->next()) {
			$cats = deserialize($q->helpdesk_categories, true);
			if (count($cats) > 0) $hcats[$q->id] = $cats; 
		} // while
		if (count($hcats) < 1) return $arrPages;

		$hmods = implode(',',array_keys($hcats));
		$ppages = $intRoot > 0 ? $this->getChildRecords($intRoot, 'tl_page', true) : array();
		$hpages = array();
		
		// process all pages/categories where helpdesk blocks are in an article
		$s =
			"select distinct `pg`.`id` as `page`, `pg`.`alias` as `alias`, `ct`.`module` as `module`" .
			"\n from `tl_page` as `pg`" .
			"\n inner join `tl_article` as `art` on `pg`.`id`=`art`.`pid`" .
			"\n inner join `tl_content` as `ct` on `art`.`id`=`ct`.`pid` and `ct`.`type`='module' and `ct`.`module` in (".$hmods.")";
		if (count($ppages) > 0) $s .= "\n where `pg`.`id` in (".implode(',',$ppages).")";
		$q = $db->prepare($s)->execute();
		while ($q->next()) {
			$p = $q->page;
			if (array_key_exists($p, $hpages)) {
				$cats = $hpages[$p]['cats'];
				foreach ($hcats[$q->module] as $cat)
					if (!in_array($cats, $cat))
						$cats[] = $cat;
				$hpages[$p]['cats'] = $cats;
			} else
				$hpages[$p] = array('alias' => $q->alias, 'cats' => $hcats[$q->module]);
		} // while

		// process all pages where helpdesk blocks are in the layout
		$s =
			"select `pg`.`id` as `page`, `pg`.`alias` as `alias`, `ly`.`modules` as `modules`" .
			"\n from `tl_page` as `pg`" .
			"\n inner join `tl_layout` as `ly` on `pg`.`layout`=`ly`.`id`";
		if (count($ppages) > 0) $s .= "\n where `pg`.`id` in (".implode(',',$ppages).")";
		$q = $db->prepare($s)->execute();
		while ($q->next()) {
			foreach (deserialize($q->modules, true) as $module) {
				$newcats = $hcats[$module['mod']];
				if (!is_null($newcats)) {
					$p = $q->page;
					if (array_key_exists($p, $hpages)) {
						$cats = $hpages[$p]['cats'];
						foreach ($newcats as $cat)
							if (!in_array($cats, $cat))
								$cats[] = $cat;
						$hpages[$p]['cats'] = $cats;
					} else
						$hpages[$p] = array('alias' => $q->alias, 'cats' => $newcats);
				} // if
			} // foreach
		} // while

		if (count($hpages) < 1) return $arrPages;
		
		// get domain
		$domain = $this->Environment->base;
		$objParent = $this->getPageDetails($aPage);
		if (strlen($objParent->domain))
			$domain = ($this->Environment->ssl ? 'https://' : 'http://') . $objParent->domain . TL_PATH . '/';
			
		// get helpers
		$rewr = $GLOBALS['TL_CONFIG']['rewriteURL'];
		$noal = $GLOBALS['TL_CONFIG']['disableAlias'];
		$mpage = $this->settings->mpage;
		foreach ($hpages as $id => $hpage) {
			$alias = (!$noal && strlen($hpage['alias'])>0) ? $hpage['alias'] : $id; 
			$url = $domain;
			if ($noal) 
				$url .= 'index.php?id='; 
			else 
				if (!$rewr) 
					$url .= 'index.php/'; 
			$url .= $alias;
			$s = 
				"select `tck`.`id` as `id`, `tck`.`pub_replies` as `replies`".
				"\n from `tl_helpdesk_categories` as `cat`".
				"\n inner join `tl_helpdesk_tickets` as `tck` on `cat`.`id`=`tck`.`pid` and `tck`.`published`='1'".
				"\n where `cat`.`id` in (".implode(',',$hpage['cats']).") and `cat`.`published`='1'";
			$q = $db->prepare($s)->execute();
			while ($q->next()) {
				$u = $noal
					? $url.'&topic='.$q->id
					: $url.'/topic/'.$q->id.$GLOBALS['TL_CONFIG']['urlSuffix'];
				$arrPages[] = $u;
				$mcnt = 1 + $q->replies;
				if ($mpage > 0 && $mcnt > $mpage) {
					$u = Helpdesk::addPage($u);
					$pg = 2;
					while ($mcnt > $mpage) {
						$arrPages[] = $u . $pg;
						$pg++;
						$mcnt -= $mpage;
					} // while
				} // if
			} // while
		} // foreach
		return $arrPages;
	} // getSearchablePages

} // class HelpdeskFrontendModule

?>
