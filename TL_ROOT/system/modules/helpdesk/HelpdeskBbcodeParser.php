<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: BBCode parser
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

define('BBCODE_LOOKDOWN', 3);					// # of elements the parser descends to find a matching closing element
  
/**
 * Class HelpdeskBbcodeParser 
 *
 * This class builds an tree of HelpdeskBbcodeStackItem objects and from there derives 
 * an appropriate html structure based upon code generation methods. Each code 
 * generation method is named parse_[bbcode], as where [bbcode] is an bbcode tag which is
 * supported by the parser. After adding an additional method, the parser will 
 * recognize the code generation method and apply this method when encountering a 
 * matching bbcode-tag while parsing.
 */
class HelpdeskBbcodeParser
{
	public $theme;
	public $module;
	private $ident;
	protected $usedTags;
	protected $textTags;
	protected $smileyText;
	protected $smileyFulltext;
	protected $smileyIcon;

	function HelpdeskBbcodeParser($theme)
	{
		$strPluginPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/plugins/geshi';
		include_once($strPluginPath . '/geshi.php');
		
		$this->usedTags = array();
		$this->textTags = array();
		$this->username = '';
		$this->theme = $theme;
		$methods = get_class_methods(get_class($this));
		foreach ($methods as $m) {
			if (substr($m, 0, 6) == 'parse_') {
				$tag = substr($m, 6);
				$this->usedTags[$tag] = $m;
			} // if
		} // foreach
		$this->smileyText = array(
			':)',		
			':rolleyes:',
			':D',
			':lol:',
			':w00t:',
			';)',	
			':|',
			':P',
			'B)',
			':-/',
			':blush:',
			':O',
			':scared:',
			':huh:',
			':blink:',	
			':confused:',
			':(',
			':cry:',
			':sneaky:',
			':mad:',
			':love:',
			':sleep:',
			':thumbdown:',
			':thumbup:'
		);
		$this->smileyFulltext = array(
			':smile:',		
			':rolleyes:',
			':laugh:',
			':lol:',
			':w00t:',
			':wink:',	
			':bored:',
			':tongue:',
			':cool:',
			':unsure:',
			':blush:',
			':ohmy:',
			':scared:',
			':huh:',
			':blink:',	
			':confused:',
			':sad:',
			':cry:',
			':sneaky:',
			':mad:',
			':love:',
			':sleep:',
			':thumbdown:',
			':thumbup:'
		);
		$this->smileyIcon = array(
			$theme->createImage('smile',	':)'),
			$theme->createImage('rolleyes',	':rolleyes:'),
			$theme->createImage('laugh',	':D'),
			$theme->createImage('lol',		':lol:'),
			$theme->createImage('w00t',		':w00t:'),
			$theme->createImage('wink',		';)'),
			$theme->createImage('bored',	':|'),
			$theme->createImage('tongue',	':P'),
			$theme->createImage('cool',		'B)'),
			$theme->createImage('unsure',	':-/'),
			$theme->createImage('blush',	':blush:'),
			$theme->createImage('ohmy',		':O'),
			$theme->createImage('scared',	':scared:'),
			$theme->createImage('huh',		':huh:'),
			$theme->createImage('blink',	':blink:'),
			$theme->createImage('confused',	':confused:'),
			$theme->createImage('sad',		':('),
			$theme->createImage('cry',		':cry:'),
			$theme->createImage('sneaky',	':sneaky:'),
			$theme->createImage('mad',		':mad:'),
			$theme->createImage('love',		':love:'),
			$theme->createImage('sleep',	':sleep:'),
			$theme->createImage('thumbdown',':thumbdown:'),
			$theme->createImage('thumbup',	':thumbup:')
		);                                             
	} // HelpdeskBbcodeParser

	function parse($text, $ident = null)
	{
		$this->ident = $ident;
		$text = str_replace(
					array('[*]', '[/*]'),
					array('[li]', '[/li]'), 
					$text
				);
		$basetree = new HelpdeskBbcodeStackItem();
		$basetree->build(' '.trim($text));
		return $basetree->parse($this, $this->usedTags);
	} // parse

	/* 
	 * Auxilary method which calls upon the HelpdeskBbcodeTextHandler
     * method, or does noting when not found 
	 */
	function replace_text($text)
	{
		return str_replace($this->smileyFulltext, $this->smileyIcon, nl2br(htmlspecialchars(str_replace($this->smileyText, $this->smileyFulltext, $text))));
	} // replace_text
  
	/* 
	 * base function to convert a [*]text[*] to <**>text</**> 
	 */
	function simple_parse($tree, $html_pre, $html_post, $parseInner = true, $htmlspecialchars = true, $nl2br = true)
	{
		$text = $parseInner ? $tree->innerToHtml($this, $this->usedTags) : $tree->toText();
		if (!$htmlspecialchars) $text=htmlspecialchars($text);
		$text = strlen($text) > 0 ? $html_pre.$text.$html_post : '';
		if (!$nl2br) $text = str_replace ("<br />", "", $text);
		return $text;
	} // simple_parse
  
	// simple code generation methods
	function parse_h1($tree)   {return $this->simple_parse($tree, '<h1>', '</h1>');}
	function parse_h2($tree)   {return $this->simple_parse($tree, '<h2>', '</h2>');}
	function parse_h3($tree)   {return $this->simple_parse($tree, '<h3>', '</h3>');}
	function parse_h4($tree)   {return $this->simple_parse($tree, '<h4>', '</h4>');}
	function parse_h5($tree)   {return $this->simple_parse($tree, '<h5>', '</h5>');}
	function parse_x($tree)    {return $this->simple_parse($tree, '[', ']');}
	function parse_raw($tree)  {return $this->simple_parse($tree, '<pre class="raw">', '</pre>', false);}
	function parse_div($tree)  {return $this->simple_parse($tree, '<div class="div">', '</div>');}
	function parse_c($tree)	   {return $this->simple_parse($tree, '<div class="centered">', '</div>');}
	function parse_r($tree)    {return $this->simple_parse($tree, '<div class="rightaligned">', '</div>');}
	function parse_j($tree)    {return $this->simple_parse($tree, '<div class="justified">', '</div>');}
	function parse_i($tree)    {return $this->simple_parse($tree, '<i>', '</i>');}
	function parse_u($tree)    {return $this->simple_parse($tree, '<span class="underlined">', '</span>');}
	function parse_s($tree)    {return $this->simple_parse($tree, '<span class="striked">', '</span>');}
	function parse_b($tree)    {return $this->simple_parse($tree, '<b>', '</b>');}
	function parse_sub($tree)  {return $this->simple_parse($tree, '<sub>', '</sub>');}
	function parse_sup($tree)  {return $this->simple_parse($tree, '<sup>', '</sup>');}
	function parse_small($tree){return $this->simple_parse($tree, '<small>', '</small>');}
	function parse_big($tree)  {return $this->simple_parse($tree, '<big>', '</big>');}
	function parse_li($tree)   {return $this->simple_parse($tree, '<li>', '</li>', true, true, false);}
	function parse_tr($tree)   {return $this->simple_parse($tree, '<tr>', '</tr>', true, true, false);}
	
	function parse_quote($tree, $params = array())
	{
		$title = isset($params['quote']) ? $params['quote'].':' : $GLOBALS['TL_LANG']['tl_helpdesk_bbcode']['quote'];
		return $this->simple_parse(
					$tree, 
					'<blockquote class="quotebox">' .
						'<div class="titlebar quotebox-titlebar">' . 
							'<span class="icon">' . $this->theme->createImage('quote16') . '</span>' .
							'<span class="text quotebox-text">' . htmlspecialchars($title) . '</span>' .
						'</div>' .
						'<div class="scrollbox">',
						'</div>' .
					'</blockquote>'
				);
	} // parse_quote
	
	function parse_box($tree)
	{
		return $this->simple_parse(
					$tree, 
					'<blockquote class="quotebox">' .
						'<div class="scrollbox">',
						'</div>' .
					'</blockquote>'
				);
	} // parse_box
	
	function parse_note($tree, $params = array()) 
	{
		$title = isset($params['note']) ? $params['note'].':' : $GLOBALS['TL_LANG']['tl_helpdesk_bbcode']['note'];
		return $this->simple_parse(
					$tree, 
					'<blockquote class="notebox">' .
						'<div class="titlebar notebox-titlebar">' . 
							'<span class="icon">' . $this->theme->createImage('information16') . '</span>' .
							'<span class="text notebox-text">' . htmlspecialchars($title) . '</span>' .
						'</div>' .
						'<div class="scrollbox">',
						'</div>' .
					'</blockquote>'
				);
	} // parse_note

	function parse_warn($tree, $params = array()) 
	{
		$title = isset($params['warn']) ? $params['warn'].':' : $GLOBALS['TL_LANG']['tl_helpdesk_bbcode']['caution'];
		return $this->simple_parse(
					$tree, 
					'<blockquote class="warnbox">' .
						'<div class="titlebar warnbox-titlebar">' . 
							'<span class="icon">' . $this->theme->createImage('warning16') . '</span>' .
							'<span class="text warnbox-text">' . htmlspecialchars($title) . '</span>' .
						'</div>' .
						'<div class="scrollbox">',
						'</div>' .
					'</blockquote>'
				);
	} // parse_warn

	function parse_edit($tree) 
	{
		return $this->simple_parse(
					$tree, 
					'<span class="edit">[' .
						$GLOBALS['TL_LANG']['tl_helpdesk_bbcode']['edit'].' ',
						']</span>');
	} // parse_edit
	
	function parse_color($tree, $params = array()) 
	{
		if (preg_match('/^#[0-9a-fA-F]{3,6}$/', $params['color']))
			return $this->simple_parse($tree, '<span style="color:' . $params['color'] . '">', '</span>', true, true, false);
		return $this->simple_parse($tree, '', '', true, true, false);
	} // parse_color

	// more complex code generation methods
	
	function parse_list($tree, $params = array()) 
	{
		if (isset($params['list'])) {
			$style = $params['list'];
			if ($style=='0')
				return $this->simple_parse($tree, '<ol style="list-style-type:decimal-leading-zero;">', '</ol>', true, true, false);
			if ($style=='1')
				return $this->simple_parse($tree, '<ol style="list-style-type:decimal;">', '</ol>', true, true, false);
			if ($style=='a')
				return $this->simple_parse($tree, '<ol style="list-style-type:lower-latin;">', '</ol>', true, true, false);
			if ($style=='A')
				return $this->simple_parse($tree, '<ol style="list-style-type:upper-latin;">', '</ol>', true, true, false);
			if ($style=='i')
				return $this->simple_parse($tree, '<ol style="list-style-type:lower-roman;">', '</ol>', true, true, false);
			if ($style=='I')
				return $this->simple_parse($tree, '<ol style="list-style-type:upper-roman;">', '</ol>', true, true, false);
		} // if
		return $this->simple_parse($tree, '<ul>', '</ul>', true, true, false);
	} // parse_list
	
	function parse_table($tree, $params = array()) 
	{
		if (isset($params['table'])) {
			$style = $params['table'];
			if ($style=='0')
				return $this->simple_parse($tree, '<table class="table0">', '</table>', true, true, false);
			if ($style=='1')
				return $this->simple_parse($tree, '<table class="table1">', '</table>', true, true, false);
			if ($style=='2')
				return $this->simple_parse($tree, '<table class="table2">', '</table>', true, true, false);
		} // if
		return $this->simple_parse($tree, '<table>', '</table>', true, true, false);
	} // parse_list
	
	function parse_th($tree, $params = array())
	{
		$colspan = $rowspan = '';
		if (isset($params['th'])) {
			$span = explode(',',trim($params['th']));
			$c = count($span);
			if ($c>=2) {
				$rows = intval($span[1]);
				if ($rows>1 && $rows < 100) $rowspan = ' rowspan="' . $rows . '"';
			} // if
			if ($c>=1) {
				$cols = intval($span[0]);
				if ($cols>1 && $cols < 100) $colspan = ' colspan="' . $cols . '"';
			} // if
		} // if
		return $this->simple_parse($tree, '<th' . $rowspan . $colspan . '>', '</th>', true, true, false);
	} // parse_th
	
	function parse_td($tree, $params = array())
	{
		$colspan = $rowspan = '';
		if (isset($params['td'])) {
			$span = explode(',',trim($params['td']));
			$c = count($span);
			if ($c>=2) {
				$rows = intval($span[1]);
				if ($rows>1 && $rows < 100) $rowspan = ' rowspan="' . $rows . '"';
			} // if
			if ($c>=1) {
				$cols = intval($span[0]);
				if ($cols>1 && $cols < 100) $colspan = ' colspan="' . $cols . '"';
			} // if
		} // if
		return $this->simple_parse($tree, '<td' . $rowspan . $colspan . '>', '</td>', true, true, false);
	} // parse_td
	
	function parse_pre($tree)  
	{
		return $this->simple_parse(
					$tree, 
					'<div class="preformated">' .
						'<div class="scrollbox">' .
							'<pre>',
							
							'</pre>' .
						'</div>' .
					'</div>',
					false
				);
	} // parse_pre
	
	function parse_code($tree, $params = array()) 
	{
		$oldval = error_reporting(0);
		// [code]somecode[/code], as well as [code=lang]some code[/code] for syntax highlighting
		// lang = c csharp css css-gen html ini java php js cpp ...
		if (isset($params['code'])) {
			$lang = $params['code'];
			$parser = new GeSHi($tree->toText(), $lang);
			$parser->enable_classes();
			$parser->set_overall_class('helpdesk-code');
/*
echo "<!--\n";
echo $parser->get_stylesheet(false);
echo "\n-->";
*/
			return 
				'<blockquote class="codebox">' .
					'<div class="titlebar codebox-titlebar">' . 
						'<span class="icon">' . $this->theme->createImage('code16') . '</span>' .
						'<span class="text codebox-text">' . htmlspecialchars($lang) . ':</span>' .
					 '</div>' .
					'<div class="scrollbox"><pre>' . $parser->parse_code() . '</pre></div>' .
				'</blockquote>';
		} // if
		$code = $this->simple_parse(
					$tree, 
					'<blockquote class="codebox">' .
						'<div class="titlebar codebox-titlebar">' . 
							'<span class="icon">' . $this->theme->createImage('code16') . '</span>' .
							'<span class="text codebox-text">' . $GLOBALS['TL_LANG']['tl_helpdesk_bbcode']['code'] . '</span>' .
						'</div>' .
						'<div class="scrollbox">' .
							'<pre>',
							
							'</pre>' .
						'</div>' .
					'</blockquote>',
					false, false
				);
		error_reporting($oldval);
		return $code;
	} // parse_code

	function parse_url($tree, $params = array())
	{
		// [url]href[/url] as well as [url=href]text[/url] is supported
		$href = isset($params['url']) ? $params['url'] : $tree->toText();
		if (ctype_digit($href) && $this->module)
			$href = $this->module->createUrl('message',$href);
		else
			$href = htmlspecialchars($href);
		return $this->simple_parse($tree, '<a href="'.$href.'">', '</a>');
	} // parse_url
	
	function parse_mail($tree, $params = array())
	{
		// [mail]email[/mail] as well as [mail=email]text[/mail] is supported
		$href = isset($params['mail']) ? $params['mail'] : $tree->toText();
		return $this->simple_parse($tree, '<a href="mailto:'.htmlspecialchars($href).'">', '</a>');
	} // parse_mail
	
	function parse_img($tree)
	{
		$text = $tree->toText();
		$params = $tree->getParameters();
		$height = ''; $width = ''; $align = '';
		if (isset($params['img'])) {
			$size = explode(',',trim($params['img']));
			$c = count($size);
			if ($c==2) {
				$height = is_numeric($size[0]) ? ' height="'.intval($size[0]).'"' : '';
				$width  = is_numeric($size[1]) ? ' width="'.intval($size[1]).'"' : '';
			} else 
				if($c==1)
					$width  = is_numeric($size[0]) ? ' width="'.intval($size[0]).'"' : '';
		} // if
		if (isset($params['align'])) {
			$s = strtolower($params['align']);
			if($s == 'left' || $s == 'links') $align = ' align="left"';
			if($s == 'right' || $s == 'rechts') $align = ' align="right"';
		} // if
		if (ctype_digit($text) && !is_null($this->ident))
			$text = 'system/modules/helpdesk/Helpdesk'.(TL_MODE=='BE' ? 'Back' : 'Front').'endDownload.php?msg='.$this->ident.'&id='.$text;
		return '<img'.$height.$width.$align.' src="'.htmlspecialchars($text).'" />';
	} // parse_img
	
} // class HelpdeskBbcodeParser


/**
 * Class HelpdeskBbcodeAdminParser 
 *
 * HelpdeskBbcodeAdminParser class which enabled site admins to input
 * plain html into their messages.
 */
class HelpdeskBbcodeAdminParserr extends HelpdeskBbcodeParser
{
	function parse_html($tree)
	{
		return $tree->toText();
	}
} // HelpdeskBbcodeAdminParserr

/**
 * Class HelpdeskBbcodeStackItem 
 *
 * A recursive object used to create a tree, from which html or plain text 
 * can be derived.
 */
class HelpdeskBbcodeStackItem
{
	protected $parent;				// a link to the parent object of element
	protected $childs;				// a mixed array of plain text and other HelpdeskBbcodeStackItem objects
	protected $tag_open;			// the bbcode tag, without parameters
	protected $tag_close;			// the bbcode closing tag.
	protected $tag_full;			// full bbcode tag as found in the original unparsed text
	protected $was_closed = false;	// status
	protected $parameters;			// storeage array for parameter information

	function HelpdeskBbcodeStackItem()
	{
		$this->parent = null;
		$this->childs = array();
		$this->parameters = array();
		$this->tag_open = '';
		$this->tag_close = '';
		$this->tag_full = '';
	} // HelpdeskBbcodeStackItem

	function isTextTag($tag)
	{
		return in_array($tag, array('code', 'pre', 'raw'));
	} // isTextTag

	function setParent(&$parent)
	{
		if(!is_object($parent)) return false;
		if(get_class($parent) != get_class($this)) return false;
		$this->parent = $parent;
		return true;
	} // setParent

	function setTag($open, $close = '')
	{
		$this->tag_open = strtolower($open);
		$this->tag_close = strtolower($close);
	} // setTag

	/* 
	* parse $text until $this->tag_close is found. When a other closing tag than 
	* expected is found, handle it appropriate:
	*
	* Look down the tree, werther there is an element for which the found closing 
	* tag is appropriate. If this element is less then BBCODE_LOOKDOWN steps away, 
	* close the current tag and return to calling object. When out of range, handle 
	* the closing tag as ordinary text
	*/
	function take($text)
	{
		while(($s = strpos($text, '[')) >= 0 && strlen($text) > 0) {
			if ($s===false) {
				$this->append($text);
				$text = '';
			} elseif ($s == 0) {
				$close = strpos($text, ']');
				if ($close < 0)	{
					$this->append($text);
					$text = '';
				} elseif (substr($text, 0, 2) == '[/') {
					$tag = strtolower(substr($text, 0, $close+1));
					$text = substr($text, $close+1);
					if ($tag==$this->tag_close) {
						$this->was_closed = true;
						return $text;
					} else 
						if ($this->parent != null) {
							$subelem = $this->parent->isThisYours($tag, BBCODE_LOOKDOWN);
							if (!$subelem)
								$this->append($tag);
							else
								if ($subelem <= BBCODE_LOOKDOWN)
									return $tag.$text;
								else
									$this->append($tag);
						} // if
					else
						$this->append($tag);
				} else {
					$child = new HelpdeskBbcodeStackItem();
					$child->setParent($this);
					$text = $child->build($text);
					$this->append($child);
				} // if
			} else {
				$this->append(substr($text, 0, $s));
				$text = substr($text, $s);
			} // if
			$s = -1;
		} // while
		return $text;
	} // take

	/**
	 * parse $tag into $tag_open en $tag_full. extract (parameter,value) pairs 
	 * and store these in $this->parameters;
	 */
	function parseTag($tag)
	{
		$this->tag_full = '['.$tag.']';
		while(strpos($tag, ' =') > 0) $tag = str_replace(' =', '=', $tag);
		while(strpos($tag, '= ') > 0) $tag = str_replace('= ', '=', $tag);
		while(strpos($tag, ', ') > 0) $tag = str_replace(', ', ',', $tag);
		while(strpos($tag, ' ,') > 0) $tag = str_replace(' ,', ',', $tag);
		$exploded = explode(' ', $tag);
		$tag_open = '';
		foreach ($exploded as $index => $element) {
			$pair = explode('=', $element, 2);
			if(count($pair) == 2)
				$this->parameters[strtolower($pair[0])] = $pair[1];
			if($index == 0) $tag_open = $pair[0];
		} // foreach
		$this->tag_open = strtolower($tag_open);
		$this->tag_close = strtolower('[/'.$tag_open.']');
	} // parseTag

	/* 
	 * generates a tree from $text from where $this is the current root element.
	 */
	function build($text)
	{
		if (empty($text)) return '';

		if (substr($text, 0, 1) == '[') {
			// Starts with an tag? parsing should stop when /tag is found
			// therefore $tag_open, $tag_close should be initialized
			$sclose = strpos($text, ']');
			if ($sclose<0) {
				$this->append($text);
				return '';
			} // if
			$tag = substr($text, 1, $sclose-1);
			$text = substr($text, $sclose + 1);
			$this->parseTag($tag);
			if ($this->isTextTag($this->tag_open/*strtolower($tag)*/)) {
				$s = strpos(strtolower($text),$this->tag_close/*'[/'.strtolower($tag)*/);
				if($s == false)
					$text = $this->take($text);
				else {
					$subtext = substr($text, 0, $s);
					$this->childs[] = $subtext;
					$text = substr($text, $s);
					$text = substr($text, strpos($text,']')+1);
				} // if
			} else 
				$text = $this->take($text);
			return $text;
		} else{
			// Starts with text, therefor containerobject
			$text = $this->take($text);
			$this->append($text);
		} // if
	} // build

	/* 
	 * appends $data to the internal leaf structure.
	 * $data can be object or plain text
	 */
	function append($data)
	{
		if (empty($data)) return;
		$this->childs[] = $data;
	} // append

	/* 
	 * This method is called upon from child object, to find a object matching 
	 * to a found closing tag in order to maintain a stable structure. returns 
	 * 'false' or a numeric value, telling the calling child how many levels 
	 * the corresponding element is down in the tree, from the childs origin
	 */
	function isThisYours($closingTag, $was_closed = 0)
	{
		if ($closingTag == $this->tag_close) {
			if($was_closed >= 0) { $this->was_closed = true;}
			return 1;
		} // if
		if($this->parent == null) return false;
		$s = $this->parent->isThisYours($closingTag, $was_closed - 1);
		if(is_int($s)) return $s + 1;
		return $s;
	} // isThisYours 

	/* 
	 * Return the parameters for this object 
	 */
	function getParameters()
	{
		return $this->parameters;
	} // getParameters

	/* 
	 * Return a string representation of this tag in plain bbcode 
	 */
	function toString()
	{
		return $this->tag_full.$this->toText().($this->was_closed ? $this->tag_close : '');
	} // toString

	/* 
	 * Return a string representation of this tags inner in plain bbcode 
	 */
	function toText()
	{
		$text = '';
		foreach ($this->childs as $c)
			if(is_object($c))
				$text.= $c->toString();
			else
				$text.= $c;
		return $text;
	} // toText

	/* convert the contents of this element to html.
	* the $parser object is used to find appropriate
	* parse_tag methods.
	*/
	function innerToHtml(&$parser, $methods = array())
	{
		$text = '';
		foreach ($this->childs as $c)
			if(is_object($c))
				$text.= $c->parse($parser, $methods);
			else
				$text.= $parser->replace_text($c);
		return $text;
	} // innerToHtml

	/* 
	 * Convert the total object to html 
	 */
	function toHtml(&$parser, $methods=array(), $inner_only = true)
	{
		$text = '';
		if (strlen($this->tag_full) > 0 && !$inner_only) {
			if (isset($methods[$this->tag_open])) {
				$method = $methods[$this->tag_open];
				$text = $parser->$method($this);
			} else
				return $this->innerToHtml($parser, $methods);
		} else {
			// No method found for this tag
			foreach($this->childs as $c)
				if (is_object($c))
					$text.= $c->parse($parser, $methods);
				else
					$text.= $parser->replace_text($c);
		} // if
		return $text;
	} // toHtml

	/* 
	 * Parse this object into html, this method is called from the root element 
	 * of the constructed tree 
	 */
	function parse(&$parser, $methods = array())
	{
		$text = '';
		if (strlen($this->tag_full) > 0) {
			if (isset($methods[$this->tag_open])) {
				$method = $methods[$this->tag_open];
				$text = $parser->$method($this, $this->parameters);
			} else {
				foreach($this->childs as $c)
					if (is_object($c))
						$text.= $c->parse($parser, $methods);
					else
						$text.= $parser->replace_text($c);
				return $this->tag_full.$text.($this->was_closed ? $this->tag_close : '');
			} // if
		} else {
			// No method found for this tag
			foreach ($this->childs as $c)
				if (is_object($c))
					$text.= $c->parse($parser, $methods);
				else
					$text.= $parser->replace_text($c);
		} // if
		return $text;
	} // parse
	
} // class HelpdeskBbcodeStackItem

?>