<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Insert tags
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskTags extends System
{

	public function replaceTags($content, $template)
	{
		if (TL_MODE == 'BE') return $content;
		$tags = array();
		preg_match_all('#{{helpdesk::(\w+)}}#i', $content, $tags, PREG_SET_ORDER);
		$done = array();
		foreach ($tags as $tag) {
			$t = $tag[0];
			if (in_array($t, $done)) continue;
			$n = count($tag);
			$replace = '';
			if ($n==2) {
				$this->import('HelpdeskSettings');
				switch ($tag[1]) {
					case 'tot_tickets':
						$replace = $this->HelpdeskSettings->tot_tickets;
						break;
					case 'tot_messages':
						$replace = $this->HelpdeskSettings->tot_messages;
						break;
					case 'tot_members':
						$replace = $this->HelpdeskSettings->tot_members;
						break;
				} // switch
			} // if
			$content = str_replace($t, $replace, $content);
			$done[] = $t;
		} // foreach
		return $content;
	} // replacetags
	
} // HelpdeskTags

?>
