<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Class HelpdeskTheme
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskTheme
{
	const themepath = 'system/modules/helpdesk/themes/';
	
	public static function file($file)
	{
		$theme = $GLOBALS['TL_CONFIG']['backendTheme'];
		if (strlen($theme) && $theme!='default') {
			$f = self::themepath.$theme.'/'.$file;
			if (is_file(TL_ROOT.'/'.$f)) return $f;
		} // if
		return self::themepath.'default/'. $file;
	} // if
	
	public static function image($file, &$png)
	{
		$theme = $GLOBALS['TL_CONFIG']['backendTheme'];
		if (strlen($theme) && $theme!='default') {
			$url = self::themepath.$theme.'/images/';
			if (is_file(TL_ROOT.'/'.$url.$file.'.png')) return $url.$file.'.png';
			if (is_file(TL_ROOT.'/'.$url.$file.'.gif')) { $png = false; return $url.$file.'.gif'; }
		} // if
		$url = self::themepath.'default/images/';
		if (is_file(TL_ROOT.'/'.$url.$file.'.png')) return $url.$file.'.png';
		if (is_file(TL_ROOT.'/'.$url.$file.'.gif')) { $png = false; return $url.$file.'.gif'; }
		return $url.'default.png';
	} // image
	
	public static function createImage($file, $alt='', $attributes='')
	{
		if ($alt=='') $alt = 'icon';
		$png = true;
		$img = self::image($file, $png);
		$size = getimagesize(TL_ROOT.'/'.$img);
		return '<img'.($png ? ' class="pngfix"' : '').' src="'.$img.'" '.$size[3].' alt="'.specialchars($alt).'"'.(strlen($attributes) ? ' '.$attributes : '').' />';
	} // createImage
	
} // class HelpdeskTheme

?>