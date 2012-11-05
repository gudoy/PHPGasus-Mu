<?php

//namespace PHPGasus;

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
		'download' 		=> array('mime' => 'application/force-download'),
		//'download' 		=> array('application/octet-stream')
		//'download' 		=> array('application/x-msdownload') 		// for exe/dll ????
		'csv' 			=> array('mime' => 'text/csv'),
		'txt' 			=> array('mime' => 'text/plain'),
		// TODO
		//'php' 			=> array('mime' => 'vnd.php.serialized'),
		//'phptxt' 			=> array('mime' => 'text/plain'),
		//'rss' 			=> array('mime' => 'application/rss+xml'),
		//'atom' 			=> array('mime' => 'application/atom+xml'),
		//'rdf' 			=> array('mime' => 'application/rdf+xml'),
		//'zip' 			=> array('mime' => 'application/rdf+xml'),
		//'gz' 				=> array('mime' => 'multipart/x-gzip'),
	);
	
	public $body 		= null;
	public $headers 	= null;
	public $data 		= null;
	public $view 		= null;
	
	//public function __construct(Request $Request)
	public function __construct()
	{
		// TODO: pass the Request in the constructor or let the user assign (or not) it to a $request member?
		// IT would be better not having to rely on it's presence 
		
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
		// Handle keys to be passed with or without ': ' at the end
		//$this->headers[] = $name . ': ' . $value;
		//$this->headers[] = trim(trim($name), ':') . ': ' . $value;
		$cleaned 					= trim(trim($name), ':');
		$this->headers[$cleaned] 	= $value;
		
		return $this;
	}

	public function setHeaders($headers = array())
	{
		foreach ((array) $headers as $k => $v)
		{
			// Skip item if its index is numeric
			if ( is_numeric($k) ){ continue; }

			/*			
			// Handle keys to be passed with or without ': ' at the end
			//$this->headers[] = trim(trim($k), ':') . ': ' . $v;
			$cleaned 					= trim(trim($k), ':');	
			$this->headers[$cleaned] 	= $v;
			*/
			$this->setHeader($k, $v);
		}
		
		return $this;
	}
	
	public function setFileBaseName()
	{	
		$this->fileBasename = !empty($this->fileBasename) 
			? $this->fileBasename 
			//: ( isset($this->request) 
			: ( !empty($this->request->resource)
				? $this->request->resource . $this->currentFormat 
				: $_SERVER['REQUEST_TIME'] . $this->currentFormat
			);
		
		$this->setHeader('Content-Disposition: ', 'attachment; filename=" ' . $this->fileBasename . '.json"');
	}
	
	public function writeHeaders()
	{		
		//foreach ($this->headers as $item){ header($item); }
		foreach ($this->headers as $k => $v){ header($k . ': ' . $v); }
		
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
		$this->setHeader('Content-Type', 'plain/text'); 
		$this->body = isset($this->body) ? $this->body : $this->data;
		$this->currentFormat = 'txt';
	}
	
	public function renderBin()
	{
		$this->setHeader('Content-Type: ', 'application/octet-stream');
		$this->setHeader('Content-Transfer-Encoding: ', 'Binary');
		
		$data = isset($this->body) ? $this->body : $this->data;
		
		// TODO
		// Is file ==> readfile, file_get_contents???
		// is_string:
		// if ( is_string($data) ) { for($i = 0; $i < strlen($data); $i++) { $this->body = base_covert(ord($data[$i]),10,2); } }
		// elseif is_numeric($data) {  } 
		// else { $this->body = pack('H*', $data); } 
			 
		// otherwise ???
		
		// TODO: open file
	}
	
	public function renderDownload()
	{
		$this->setheaders(array(
			'Content-Type' 				=> 'application/octet-stream',
			'Content-Transfer-Encoding' => 'Binary',
		));
		$this->setFileBaseName();
		
		$this->body = isset($this->body) ? $this->body : $this->data;
	}
	
	public function renderXml(){}
	public function renderCsv()
	{
//var_dump(__METHOD__);
		
		$output 	= '';
		$eol 		= PHP_EOL;
		$o 			= array(
			'fixbool' 	=> false, 																				// transform bools to their string representation
			'separator' => !empty($_GET['separator']) && in_array($_GET['separator'], array(',',';','\n','\t')) 
				? $_GET['separator'] 
				: ",",
			'comment' 	=> '#',
		); 
		
		//$buffer = fopen('php://temp', 'r+');
		// Get current data
		$data = isset($this->body) ? $this->body : $this->data;
		
		// Special case for scalar data
		if ( is_scalar($data) )
		{
			$output .= $o['fixbool'] && is_bool($data) ? ($data == true ? 'true' : 'false') : $data;
		}
		// Otherwise
		else
		{
			$keys = array_keys((array) $data);
			
			// Loop over the data
			foreach ($keys as $k)
			{
				$isNumIndex 	= !is_numeric($k);
				$isResource 	= $isNumIndex && DataModel::isResource($k);
				$addColNames 	= $isResource || !$isNumIndex;
				
				// Skip everything that is not the current resource
				if ( isset($this->request->resource) && $isResource && $k !== $this->request->resource ){ continue; }
				
				// Is 
				
				// Add a 1st line with column names
				if ( $addColNames ){ $output .= $o['comment'] . join($sep, $keys) . $eol; }

var_dump($k);
				
				$rows = $data[$k];
				
var_dump($rows);
			}
		}
		
		
		

				
		$this->setHeader('Content-Type', 'text/csv'); 
		$this->body = $output;
		$this->currentFormat = 'csv';
	}

	public function getTemplate()
	{
		
	}

	public function renderTemplate()
	{
		$this->view = new ArrayObject(array_merge(array(
			// Caching
			'cache' 					=> _TEMPLATES_CACHING,
			'cacheId' 					=> null,
			'cacheLifetime' 			=> null,
			
			// Metas
			'title' 					=> null,
			'metas' 					=> array(),
			//'description' 				=> _APP_META_KEYWORDS,
			//'keywords' 					=> _APP_META_KEYWORDS,
			'htmlAttributes' 			=> null,
			//'robotsArchivable' 			=> _APP_META_ROBOTS_ARCHIVABLE,
			//'robotsIndexable' 			=> _APP_META_ROBOTS_INDEXABLE,
			//'robotsImagesIndexable' 	=> _APP_META_ROBOTS_IMAGES_INDEXABLE,
			//'googleTranslatable' 		=> _APP_META_GOOGLE_TRANSLATABLE,
			'refresh' 					=> null,
			//'allowPrerendering' 		=> _APP_ALLOW_PAGE_PRERENDERING,
			
			// Viewport
			//'iosWebappCapable' 			=> _APP_IOS_WEBAPP_CAPABLE,
			//'viewportWidth' 			=> _APP_VIEWPORT_WIDTH,
			//'viewportIniScale' 			=> _APP_VIEWPORT_INI_SCALE,
			//'viewportMaxScale' 			=> _APP_VIEWPORT_MAX_SCALE,
			//'viewportUserScalable' 		=> _APP_VIEWPORT_USER_SCALABLE,
			
			//'minifyCSS' 				=> _MINIFY_CSS,
			//'minifyJS' 					=> _MINIFY_JS,
			//'minifyHTML' 				=> _MINIFY_HTML,
		), (array) $this->view,
		array(
			/*
			'minifyCSS' 				=> isset($_GET['minify']) ? in_array($_GET['minify'], array('css','all')) : $this->view->minifyCSS,
			'minifyJS' 					=> isset($_GET['minify']) ? in_array($_GET['minify'], array('js','all')) : $this->view->minifyJS,
			'minifyHTML' 				=> isset($_GET['minify']) ? in_array($_GET['minify'], array('html','all')) : $this->view->minifyHTML,
			 */
		)), 2);
		
		//define('SMARTY_DIR', _PATH_LIBS . 'PHPGasus/templating/Smarty/');
		//define('SMARTY_PLUGINS_DIR', SMARTY_DIR . 'plugins/');
		//define('SMARTY_SYSPLUGINS_DIR', SMARTY_DIR . 'sysplugins/');
		//require (SMARTY_DIR . 'Smarty.class.php');
		require (_PATH_LIBS . 'PHPGasus/templating/Smarty/Smarty.class.php');
		
		
		// Instanciate a Smarty object and configure it
		$this->templateEngine 						= new Smarty();
		$this->templateEngine->compile_check 			= _TEMPLATES_COMPILE_CHECK;
		$this->templateEngine->force_compile 			= _TEMPLATES_FORCE_COMPILE;
		$this->templateEngine->caching 				= isset($this->view['cache']) 			? $this->view['cache'] : _TEMPLATES_CACHING;
		$this->templateEngine->cache_lifetime 		= isset($this->view['cacheLifetime']) 	? $this->view['cacheLifetime'] : _TEMPLATES_CACHE_LIFETIME;
		$this->templateEngine->template_dir 			= _PATH . 'templates/';
		$this->templateEngine->compile_dir 			= _PATH . 'templates/_precompiled/';
		$this->templateEngine->cache_dir 			= _PATH . 'templates/_cache/';	
		
//var_dump($this->request);
		
		// Variables passed to the templates 
		$this->template = isset($this->template) 
			? $this->template 
			//: 'pages/' . $this->request->controllerRelPath . $this->request->resource . '/' . $this->request->methodName . '.html.tpl'; 
			//: 'pages/' . $this->request->controllerRelPath . $this->request->resource . '/' . $this->request->methodName . '.html.tpl';
			//: 'pages/' . $this->request->controllerRelPath . $this->request->resource . '/' . $this->request->methodName . '.html.tpl';
			: 'pages/' . (!empty($this->request->breadcrumbs) ? join('/' , $this->request->breadcrumbs) . '/' : '') . $this->request->resource . '/' . $this->request->methodName . '.html.tpl';
		$this->templateData = array('request' => $this->request, 'data' => $this->data, 'view' => $this->view);
		
//var_dump($this->template);
		
		$this->templateEngine->assign($this->templateData);
		$this->templateEngine->display($this->template, $this->view->cacheId);
	}
	
	public function render()
	{
		// Send headers
		$this->writeHeaders();
		
//var_dump($this->currentFormat);
		
		// Special when using templating
		if ( $this->currentFormat === 'html' )
		{
			$this->renderTemplate();
		}
		// Otherwise, just print the body
		else
		{
			echo $this->body;		
		}	
	}
}

?>