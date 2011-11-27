<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Class HelpdeskMailbox
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

class HelpdeskMailbox
{
	protected	$username	= '';
	protected	$password	= '';
	protected	$server		= '';
	protected	$port		= null;
	protected	$type		= null;
	protected	$tls		= '';
	protected	$cert		= '';
	protected	$mailbox	= '';
	protected	$handle		= false;
	protected	$expunge	= false;
	protected	$maxAtchSize;
	protected	$currAtchSize;
	protected	$extensions;

	public function __set($key, $value)
	{
		switch ($key) {
			case 'username':
				$this->username = trim($value);
				break;

			case 'password':
				$this->password = trim($value);
				break;

			case 'server':
				$this->server = trim($value);
				break;

			case 'mailbox':
				$this->mailbox = trim($value);
				break;

			case 'type':
				switch (strtolower(trim($value))) { 
					case 'imap': 
					case 'imap4': 
						$this->type = '/imap';
						break;
					case 'pop':
					case 'pop3':
						$this->type = '/pop3';
						break;
					default:
						throw new Exception(sprintf('Invalid protocol "%s"', $value));
				} // switch
				break;

			case 'port': {
				$port = intval($value);
				if ($port>0 && $port <= 65536)
					$this->port = $port;
				else
					throw new Exception(sprintf('Invalid port # "%s"', $value));
				break;
			}
			
			case 'tls':
				switch (strtolower($value)) {
					case 'disable':
						$this->tls = '/notls'; 
						$this->cert = '';
						break;
					case 'tls_cert':
						$this->tls = '/tls'; 
						$this->cert = '';
						break;
					case 'tls_nocert':
						$this->tls = '/tls'; 
						$this->cert = '/novalidate-cert';
						break;
					case 'ssl_cert':
						$this->tls = '/ssl'; 
						$this->cert = '';
						break;
					case 'ssl_nocert':
						$this->tls = '/ssl'; 
						$this->cert = '/novalidate-cert';
						break;
					case 'enable_nocert':
						$this->tls = ''; 
						$this->cert = '/novalidate-cert';
						break;
					case 'enable_cert':
					default: 
						$this->tls = ''; 
						$this->cert = '';
				} // switch
				break;
				
			default:
				throw new Exception(sprintf('Invalid argument "%s"', $key));
				break;
		} // switch
	} // __set
	
	public function open()
	{
		if ($this->handle) $this->close();
		$this->handle = imap_open($this->mbox(), $this->username, $this->password);
		return $this->handle != false;
	} // open
	
	public function close()
	{
		if ($this->handle) {
			if ($this->expunge) {
				imap_expunge($this->handle);
				$this->expunge = false;
			} // if
			imap_close($this->handle);
			$this->handle = false;
		} // if
	} // close
	
	public function &folders()
	{
		return imap_listmailbox($this->handle, $this->mbox(), "*");
	} // folders
	
	public function delete($msgno)
	{
		imap_delete($this->handle, $msgno);
		$this->expunge = true;
	} // folders
	
	public function count()
	{
		return imap_num_msg($this->handle);
	} // count
	
	public function &header($msgno)
	{
		$h = imap_headerinfo($this->handle, $msgno);
		$this->toUtf8($h);
		return $h;
	} // header
	
	public function &content($msgno, $maxAtchSize=null, $extensions='*')
	{
		$this->extensions = explode(',', strtolower(trim(str_replace(' ','',$extensions),',')));
		$this->maxAtchSize = $maxAtchSize;
		$this->currAtchSize = 0;
		$cont = array(
			'plain'			=> '',
			'html'			=> '',
			'attachments'	=> array()
		);
		$struct = imap_fetchstructure($this->handle, $msgno);
		if (!is_object($struct)) return $cont;
		$this->addPart($struct, $cont, $msgno);
		return $cont;
	} // content

	public function __destruct()
	{
		$this->close();
	} // __destruct
	
	private function addPart(&$part, &$cont, $msgno, $partno = null)
	{
		if ($part->ifdisposition && strtoupper($part->disposition)=="ATTACHMENT") {
			if (is_null($this->maxAtchSize) || ($this->currAtchSize+$part->bytes)<=$this->maxAtchSize) {
				// attachment: get filename if present
				$filename = '';
				if (count($part->dparameters)>0)
					foreach ($part->dparameters as $dparam)
						if ((strtoupper($dparam->attribute)=='NAME') ||(strtoupper($dparam->attribute)=='FILENAME')) 
							$filename = $dparam->value;
							
				if ($filename=='' && count($part->parameters)>0)
					foreach ($part->parameters as $param)
						if ((strtoupper($param->attribute)=='NAME') ||(strtoupper($param->attribute)=='FILENAME')) 
							$filename = $param->value;
				
				if ($filename!='') {
					$p = explode('.',$filename);
					if (in_array(strtolower(end($p)), $this->extensions) || in_array('*', $this->extensions)) {
						if (!$partno)
							$data = imap_body($this->handle, $msgno);
						else
							$data = imap_fetchbody($this->handle, $msgno, $partno);
						switch ($part->encoding) {
							case 3: $data = base64_decode($data); break;
							case 4: $data = quoted_printable_decode($data); break;
							default:;
						} // switch
						$cont['attachments'][] = array(
							'filename'	=> $filename,
							'data'		=> $data
						);
						$this->currAtchSize += $part->bytes;
					} // if
				} // if
			} // if
		} else
		
		if ($part->type == 0) {	// text
			if (!$partno)
				$data = imap_body($this->handle, $msgno);
			else
				$data = imap_fetchbody($this->handle, $msgno, $partno);
			switch ($part->encoding) {
				case 3: $data = base64_decode($data); break;
				case 4: $data = quoted_printable_decode($data); break;
				default:;
			} // switch
		
			$charset = '';
			if (count($part->parameters)>0)
				foreach ($part->parameters as $param)
					if (strtoupper($param->attribute)=='CHARSET') 
						$charset = strtoupper($param->value);
				
			switch (strtoupper($part->subtype)) {
				case 'PLAIN':
					if (strlen($cont['plain'])) $cont['plain'] .= "\n========\n";
					$cont['plain'] .= $charset!='UTF-8' ? utf8_encode($data) : $data;
					break;
				case 'HTML':
					if (strlen($cont['html'])) $cont['html'] .= "<br />========\n<br />\n";
					$cont['html'] .= $this->extractBody($charset!='UTF-8' ? utf8_encode($data) : $data);
					break;
				default:;
			} // switch
		} // if
		
		if (is_array($part->parts)) {
			$i = 1;
			foreach($part->parts as $p) {
				$this->addPart($p, $cont, $msgno, $partno ? $partno.'.'.$i : $i);
				$i++;
			} // foreach
		} // if
	} // addPart
	
	private function toUtf8(&$mixed)
	{
		if (is_string($mixed)) {
            if (strpos($mixed,'=?')!==false) $mixed = imap_utf8($mixed);  
		} else
		if (is_array($mixed)) {
			foreach ($mixed as $key => $value) $this->toUtf8($mixed[$key]);
		} else
		if (is_object($mixed)) {
			$arr = get_object_vars($mixed);
			foreach ($arr as $key => $value) $this->toUtf8($mixed->$key);
        } // if
	} // toUtf8
	
	private function mbox()
	{
		// find server
		$server = $this->server;
		if ( !$server ) $server = 'localhost';
		
		// find type
		$type = $this->type;
		if (!$type) $type = '/pop3';
		
		// find port
		$port = $this->port;
		if (!$port) {
			if ($this->type == '/pop3')
				$port = $this->tls=='/ssl' ? 995 : 110;
			else
				$port = $this->tls=='/ssl' ? 993 : 143;
		} // if
		
		// compose tokens
		return '{' . $server . ':' . $port . $type . $this->tls . $this->cert . '}' . $this->mailbox;
	} // mbox
	
	private function &extractBody($html)
	{
		if (preg_match('#<\s*body(\s*|\s+[^>]*)>(.*)<\s*/\s*body\s*>#is', $html, $matches))
			return $matches[2];
		return $html;
	} // extractBody
	
} // class HelpdeskMailbox

/**
 * Test area
 * 
echo '<pre>';

$mx = new HelpdeskMailbox();
$mx->username	= 'support';
$mx->password	= 'secret';
$mx->server		= 'example.com';
//$mx->ssl		= 'novalidate';

if (!$mx->open()) die("connect to mailserver failed\n");

echo "folders:\n";
$folders = $mx->folders();

if ($folders == false) die("No folders found\n");
foreach ($folders as $val) echo "$val\n";

$count = $mx->count();
echo "there is/are $count Message(s) waiting<br/>";

for ($m = 1; $m <= $count; $m++) { 
	$header = $mx->header($m);
	print_r($header);
	$content = $mx->content($m);
	print_r($content);
	//if (count($content->attachments))
	//	foreach ($content->attachments as $att) {
	//		$fp = fopen($filestore.$att->filename, "w+");
	//		fwrite($fp, $att->data);
	//		fclose($fp);
	//	} // foreach
	$mx->delete($m);
} // for
$mx->close();
*/
?> 