<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: BBCode button bars
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

/**
 * Class HelpdeskBbcodeButtons
 *
 * Provide methods to handle ubb textareas.
 */
class HelpdeskBbcodeButtons extends System
{
	private static $javascriptLoaded = false;
	private $formId;
	private $elementId;
	private $theme;
	
	/**
	 * Generate the html code and return it as string
	 */
	public function generate($formId, $elementId, $theme, $buttons)
	{
		if (trim($buttons)=='') return '';
		
		$this->formId = $formId;
		$this->elementId = $elementId;
		$this->theme = $theme;
		
		$buttondefs = array(
			'bold'			=> array('bold24',			'[b]',		'[/b]'	),
			'italics'		=> array('italics24',		'[i]',		'[/i]'	),
			'underlined'	=> array('underlined24',	'[u]',		'[/u]'	),
			'superscript'	=> array('superscript24',	'[sup]',	'[/sup]'),
			'subscript'		=> array('subscript24',		'[sub]',	'[/sub]'),
			'centered'		=> array('centered24',		'[c]',		'[/c]'	),
			'rightaligned'	=> array('rightaligned24',	'[r]',		'[/r]'	),
			'justified'		=> array('justified24',		'[j]',		'[/j]'	),
			
			'list'			=> array('bulletlist24',	'[list]\n[li]',		'[/li]\n[li][/li]\n[li][/li]\n[/list]'),
			'numberedlist'	=> array('numberlist24', 	'[list=1]\n[li]',	'[/li]\n[li][/li]\n[li][/li]\n[/list]'),
			'romanlist'		=> array('romanlist24',		'[list=I]\n[li]',	'[/li]\n[li][/li]\n[li][/li]\n[/list]'),
			'alphalist'		=> array('alphalist24',		'[list=a]\n[li]',	'[/li]\n[li][/li]\n[li][/li]\n[/list]'),
			'listitem'		=> array('listitem24',		'[li]', 			'[/li]'),
			
			'table'			=> array('table24', '[table=1]\n[tr][th]', '  [/th][th]  [/th][/tr]\n[tr][td]  [/td][td]  [/td][/tr]\n[tr][td]  [/td][td]  [/td][/tr]\n[/table]'),
			'tablerow'		=> array('tablerow24',	'[tr]',	'[/tr]'),
			'tablecell'		=> array('tablecell24',	'[td]',	'[/td]'),
			
			'code'			=> array('code24',		'[code]',				'[/code]'),
			'php'			=> array('code_php24',	'[code=php]',			'[/code]'),
			'js'			=> array('code_js24',	'[code=javascript]',	'[/code]'),
			'xml'			=> array('code_xml24',	'[code=xml]',			'[/code]'),
			'html'			=> array('code_html24',	'[code=html4strict]',	'[/code]'),
			'css'			=> array('code_css24',	'[code=css]',			'[/code]'),
			'c++'			=> array('code_cpp24',	'[code=cpp]',			'[/code]'),
			'qt'			=> array('code_qt24',	'[code=cpp-qt]',		'[/code]'),
				
			'quote'			=> array('quote24',			'[quote]',	'[/quote]'	),
			'information'	=> array('information24',	'[note]',	'[/note]'	),
			'warning'		=> array('warning24',	 	'[warn]',	'[/warn]'	),
			'hyperlink'		=> array('hyperlink24',		'[url]',	'[/url]'	),
			'image'			=> array('image24',			'[img]',	'[/img]'	),
				
			'smile'			=> array('smile', 		':) '			),
			'rolleyes'		=> array('rolleyes', 	':rolleyes: '	),
			'laugh'			=> array('laugh', 		':D '			),
			'lol'			=> array('lol',			':lol: '		),
			'w00t'			=> array('w00t',		':w00t: '		),
			'wink'			=> array('wink',		';) '			),
			'bored'			=> array('bored',		':| '			),
			'tongue'		=> array('tongue',		':P '			),
			'cool'			=> array('cool',		'B) '			),
			'unsure'		=> array('unsure',		':-/ '			),
			'blush'			=> array('blush',		':blush: '		),
			'ohmy'			=> array('ohmy',		':O '			),
			'scared'		=> array('scared',		':scared: '		),
			'huh'			=> array('huh',			':huh: '		),
			'blink'			=> array('blink',		':blink: '		),
			'confused'		=> array('confused',	':confused: '	),
			'sad'			=> array('sad',			':( '			),
			'cry'			=> array('cry',			':cry: '		),
			'sneaky'		=> array('sneaky',		':sneaky: '		),
			'mad'			=> array('mad',			':mad: '		),
			'love'			=> array('love',		':love: '		),
			'sleep'			=> array('sleep',		':sleep: '		),
			'thumbdown'		=> array('thumbdown',	':thumbdown: '	),
			'thumbup'		=> array('thumbup',		':thumbup: '	)
		);
		
		$this->loadLanguageFile('tl_helpdesk_bbcode');
		$html = '';
		foreach (explode("\n", $buttons) as $bline) {
			$gcode = '';
			foreach (explode(';', $bline) as $bgroup) {
				$bcode = '';
				foreach (explode(',',$bgroup) as $bkey) {
					if ($bkey == 'preview')
						$bcode .= $this->previewButton();
					else if ($bkey == 'help')
						$bcode .= $this->helpButton();
					else {
						$bdef = $buttondefs[$bkey];
						if (is_array($bdef) && count($bdef >= 2))
							$bcode .= $this->imgButton($bkey, $bdef);
					} // if
				} // foreach
				if ($bcode!='') $gcode .= '<span class="buttongroup">' . "\n" . $bcode . '</span>' . "\n";
			} // foreach
			if ($gcode!='') $html .= '<div class="functionbar">' . "\n" . $gcode . '</div>' . "\n";
		} // foreach
		if ($html=='') return '';
		$html = "\n" . '<div id="helpdeskbbbuttons" style="display:none;">' . "\n" . $html . '</div>' . "\n";
		if (!self::$javascriptLoaded) {
			$html .= '<script type="text/javascript" src="system/modules/helpdesk/HelpdeskBbcodeButtons.js"></script>'."\n";
			self::$javascriptLoaded = true;
		} // if
		return $html;
	} // generate
	
	private function imgButton($key, &$bdef)
	{
		$text = $GLOBALS['TL_LANG']['tl_helpdesk_bbcode']['fnc'][$key];
		if ($text=='') $text = $key;
		$closetag = count($bdef >= 3) ? $bdef[2] : '';
		return 
			'<span class="imgbutton">' .
			'<a href="#' . $key . '" title="' . $text . '" onclick="helpdeskBbcodeInsert(' .
			'\'' . $this->formId . '\',\'' . $this->elementId . '\',\'' . $bdef[1] .'\',\'' . $closetag . '\'); return false;">' .
			$this->theme->createImage($bdef[0], $text) .
			'</a>' . 
			'</span>' . "\n";
	} // imgButton
	
	private function previewButton()
	{
		$text = $GLOBALS['TL_LANG']['tl_helpdesk_bbcode']['fnc']['preview'];
		return 
			'<span class="imgbutton">' .
			'<a href="system/modules/helpdesk/HelpdeskPreview.php" title="' . $text . '"' . 
			' onclick="helpdeskBbcodePreview(this,\'' .  $this->formId . '\',\'' . $this->elementId . '\'); return false;">' .
			$this->theme->createImage('preview24', $text) .
			'</a>' .
			'</span>' . "\n";
	} // previewButton
	
	private function helpButton()
	{
		$text = $GLOBALS['TL_LANG']['tl_helpdesk_bbcode']['fnc']['help'];
		return 
			'<span class="imgbutton">' .
			'<a href="system/modules/helpdesk/HelpdeskBbcodeHelp.php" title="' . $text . '"' .
			 ' onclick="helpdeskBbcodeHelp(this); return false;">' .
			$this->theme->createImage('help24', $text) .
			'</a>' .
			'</span>' . "\n";
	} // helpButton
	
} // class HelpdeskBbcodeButtons 

?>