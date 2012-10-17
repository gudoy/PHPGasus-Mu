<?php

namespace PHPGasus;

Class Response extends Core
{
	public $httpVersion = '1.1';
	
	// Default status code to 200 OK
	public $statusCode = 200;
	
	// Known status codes
	// http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html
	// http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
	public $statusCodes = array(
		
		// Information
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		118 => 'Connection timed out',
		
		
		// Success
		200 => 'OK',
		201 => 'Created',				
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		
		// Redirection
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'User Proxy',
		307 => 'Temporary Redirect',
		
		// Client error
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested range unsatisfiable',
		417 => 'Expectation Failed',
		
		// Server error
		500 => 'Internal server error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
	);
	
	// formats params: mime, headers, ...
	public $knownFormats = array(
		'html' 			=> array('mime' => 'text/html'),
		'xhtml' 		=> array('mime' => 'application/xhtml+xml'),
		'json' 			=> array('mime' => 'application/json'),
		'jsonp' 		=> array('mime' => 'application/json'),
		//'jsontxt' 		=> array('mime' => 'text/plain' ), 			// use '.json.txt' instead
		'jsonreport' 	=> array('mime' => 'application/json'),
		'xml' 			=> array('mime' => 'application/xml'),
		//'xmltxt' 		=> array('mime' => 'text/plain'), 				// use '.xml.txt' instead
		'plist' 		=> array('mime' => 'application/plist+xml'),
		//'plisttxt' 		=> array('mime' => 'text/plain'), 			// use '.plist.txt' instead
		'yaml' 			=> array('mime' => 'text/yaml'),
		//'yamltxt' 		=> array('mime' => 'text/plain'), 			// use '.yaml.txt' instead
		'qr' 			=> array('mime' => 'image/png'),
		'dataurl' 		=> array('mime' => 'text/plain'),
		// TODO
		//'php' 			=> array('mime' => 'vnd.php.serialized'),
		//'phptxt' 			=> array('mime' => 'text/plain'),
		//'csv' 			=> array('mime' => 'text/csv'),
		//'rss' 			=> array('mime' => 'application/rss+xml'),
		//'atom' 			=> array('mime' => 'application/atom+xml'),
		//'rdf' 			=> array('mime' => 'application/rdf+xml'),
		//'zip' 			=> array('mime' => 'application/rdf+xml'),
		//'gz' 				=> array('mime' => 'multipart/x-gzip'),
	);
	
	public $body 		= null;
	public $headers 	= null;
	public $data 		= null;
	
	//public function __construct(Request $Request)
	public function __construct()
	{
		//$this->request = &$Request;
		$this->currentFormat = null;
	}
	
	public function init()
	{
		
	}
	
	public function setSatusCode($code = 200)
	{
		// Do not continue if the passed statuscode is unknown
		if ( empty($this->$statusCodes[$code]) ){ return $this; } 
		
		$this->statusCode = $code;
		
		$this->headers[] = 'HTTP/' . $this->httpVersion . ' ' . $this->statusCode;
		
		// TODO: if status === 204, render directly ?
		
		return $this;
	}
	
	public function setHeader($name, $value)
	{
		$this->headers[] = $name . ': ' . $value;
		
		return $this;
	}
	
	
	public function writeHeaders()
	{
		foreach ($this->headers as $item){ header($item); }
		
		return $this;
	}
	
	public function renderHtml()
	{
		$this->setHeader('Content-Type', 'text/html; charset=utf-8;');
		
		// TODO: use default template (using conf)???
		// Has the template been defined
		if ( !empty($this->template) && file_exists($this->template) )
		{
			$this->body = str_replace('public/', '/public/', file_get_contents($this->template));	
		}
		// Assume returned data are already in HTML
		else
		{
			$this->body = isset($this->body) ? $this->body : $this->data;	
		}
		
		$this->currentFormat = 'html';
	}
	public function renderXhtml()
	{
		$this->setHeader('Content-Type', 'application/xhtml+xml; charset=utf-8;');
		$this->renderHtml();
		$this->currentFormat = 'html';
	}
	
	
	public function renderJson()
	{
		$this->setHeader('Content-Type', 'application/json; charset=utf-8;');
		$this->body = json_encode(isset($this->body) ? $this->body : $this->data);
		$this->currentFormat = 'json';
	}
	
	public function renderJsonp()
	{
		$this->setHeader('Content-Type', 'application/json; charset=utf-8;');
		
		$callback = !empty($_GET['callback']) ? filter_var($_GET['callback'], FILTER_SANITIZE_STRING) : null;
		$callback = !empty($callback) ? $callback : 'callback';
		
		$this->renderJson();
		$this->body = $callback . '(' . $this->body . ')';
		$this->currentFormat = 'jsonp';
	}

	public function renderJsonreport()
	{
		$this->setHeader('Content-Type', 'text/html; charset=utf-8;');
		
		$this->renderJson();
		$this->body = '<div id="json" class="jsonreport">' . $this->body . '</div>';
		$this->body .= '<script type="text/javascript" src="' . _URL . 'public/js/libs/jsonreport.js' . '"></script>';
		$this->body .= '<script type="text/javascript">window.onload = function(){ var json = document.getElementById("json"); json.innerHTML = _.jsonreport(document.getElementById("json").innerHTML) };</script>';
		$this->currentFormat = 'jsonp';
	}
	
	public function renderDataurl()
	{
		$this->setHeader('Content-Type', 'plain/text; charset=utf-8;');

		$data = isset($this->body) ? $this->body : $this->data;
		
		// Is the response already a dataurl string?
		if ( is_string($data) && preg_match('/^data\:[a-z\-\*]*\/.*\;base64\,.*/', $data) )
		{
			$this->body = $data;
		}
		// Or is the requested resource an existing file?
		elseif ( isset($this->request) && file_exists($this->request->url) )
		{
			$filename 	= $this->request->url;
			$mime 		= null;
			
			if ( function_exists("finfo_file") )
			{
			    $finfo 	= finfo_open(FILEINFO_MIME_TYPE);
			    $mime 	= finfo_file($finfo, $filename);
			    finfo_close($finfo);
			}
			else if ( function_exists('mime_content_type') ) 				{ $mime = mime_content_type($filename); }
			else if ( !stristr(ini_get("disable_functions"), "shell_exec"))	{ $mime = shell_exec("file -bi " . escapeshellarg($filename)); }
			
			$this->body = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($this->request->url));
		}
		else
		{
			// Get current mime
			$mime = $this->knownFormats[$this->currentFormat]['mime'];
			$this->body = 'data:' . $mime . ';base64,' . base64_encode($data);
		}
		
		$this->currentFormat = 'dataurl';
	}
	public function renderTxt()
	{
		//header('plain/text');
		$this->setHeader('Content-Type', 'plain/text'); 
		$this->body = isset($this->body) ? $this->body : $this->data;
		$this->currentFormat = 'txt';
	}
	public function renderXml(){}
	public function renderCsv(){}
	
	public function render()
	{
		// Send headers
		$this->writeHeaders();
		
		// And display body
		echo $this->body;		
	}
}

?>