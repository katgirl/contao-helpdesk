<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Class HelpdeskFrontendTheme
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskFrontendTheme extends System
{
	const defaultImagePath = 'system/modules/helpdesk/themes/default/images/';
	private $imagePath	= '';
	protected $settings;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->import('HelpdeskSettings', 'settings');
		$this->setPath($this->settings->images);
	} // __construct
	
	/**
	 * return image url
	 */
	public function image($file, &$png)
	{
		if ($this->imagePath) {
			if (is_file(TL_ROOT.'/'.$this->imagePath.$file.'.png')) return $this->imagePath.$file.'.png';
			if (is_file(TL_ROOT.'/'.$this->imagePath.$file.'.gif')) { $png = false; return $this->imagePath.$file.'.gif'; }
		} // if
		if (is_file(TL_ROOT.'/'.self::defaultImagePath.$file.'.png')) return self::defaultImagePath.$file.'.png';
		if (is_file(TL_ROOT.'/'.self::defaultImagePath.$file.'.gif')) { $png = false; return self::defaultImagePath.$file.'.gif'; }
		return self::defaultImagePath.'default.png';
	} // image
	
	/**
	 * Create img html
	 */
	public function createImage($file, $alt='', $attributes='')
	{
		$png = true;
		if ($alt=='') $alt = 'icon';
		$img = $this->image($file, $png);
		$size = getimagesize(TL_ROOT.'/'.$img);
		return '<img'.($png ? ' class="pngfix"' : '').' src="'.$img.'" '.$size[3].' alt="'.specialchars($alt).'"'.(strlen($attributes) ? ' '.$attributes : '').' />';
	} // createImage
	
	/**
	 * Set the image folder path
	 */
	private function setPath($path = '')
	{
		$this->imagePath = '';
		$i = str_replace('\\', '/', trim($path));
		if ($i) {
			if (substr($i,-1)!='/') $i .= '/';
			$ii = (substr($i,0,1)=='/') ? $i : TL_ROOT.'/'.$i;
			if ($i!=self::defaultImagePath && is_dir($ii)) $this->imagePath = $i;
		} // if
	} // setPath
	
} // class HelpdeskFrontendTheme

?>