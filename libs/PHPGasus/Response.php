<?php

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
		418 => 'I\'am a teapot',
		
		// Server error
		500 => 'Internal server error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
	);
	
	// formats params: mime, headers, ...
	public static $knownFormats = array(
		// !! Warning !! 
		// Order is important for reverse search (getting the extension from the mime)
		// When multiple formats share the same mime type, the main should be the first of the sequence 
	
		// Text formats
		'html' 			=> array('mime' => 'text/html'),
		'xhtml' 		=> array('mime' => 'application/xhtml+xml'),
		'json' 			=> array('mime' => 'application/json'),
		'jsonp' 		=> array('mime' => 'application/json'),
		'jsonreport' 	=> array('mime' => 'application/json'),
		'xml' 			=> array('mime' => 'application/xml'),
		'plist' 		=> array('mime' => 'application/plist+xml'),
		'yaml' 			=> array('mime' => 'text/yaml'),
		'txt' 			=> array('mime' => 'text/plain'),
		'dataurl' 		=> array('mime' => 'text/plain'),
		'datauri' 		=> array('mime' => 'text/plain'),
		'csv' 			=> array('mime' => 'text/csv'),
		
		
		// Image formats
		'jpg' 			=> array('mime' => 'image/jpeg'),
		'gif' 			=> array('mime' => 'image/gif'),
		'png' 			=> array('mime' => 'image/png'),
		'qr' 			=> array('mime' => 'image/png'),
		
		// Binary formats
		'download' 		=> array('mime' => 'application/force-download'),
		//'download' 		=> array('application/octet-stream'),
		//'download' 		=> array('application/x-msdownload'), 		// for exe/dll ????
		//'bin' 				=> array('application/octet-stream'),
		
		// Archive formats
		'zip' 			=> array('mime' => 'application/zip'),
		//'gz' 				=> array('mime' => 'multipart/x-gzip'),
		//'rar' 			=> array('mime' => 'application/rar'),
		
		// TODO
		'php' 			=> array('mime' => 'vnd.php.serialized'),
		//'rss' 			=> array('mime' => 'application/rss+xml'),
		//'atom' 			=> array('mime' => 'application/atom+xml'),
		//'rdf' 			=> array('mime' => 'application/rdf+xml'),
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
		
		$this->view = new ArrayObject(array(), 2);
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
		
		// TODO: if status === 204, render directly?
		
		return $this;
	}
	
	public function setHeader($name, $value)
	{
		// Handle keys to be passed with or without ': ' at the end
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
		
		$this->setHeader('Content-Disposition: ', 'attachment; filename="' . $this->fileBasename . '.' . $this->request->outputFormat . '"');
	}
	
	public function writeHeaders()
	{
		foreach ((array) $this->headers as $k => $v){ header($k . ': ' . $v); }
		
		return $this;
	}
	
	public function renderDefault()
	{
		$this->{'render' . _DEFAULT_OUTPUT_FORMAT}();
	}
	
	public function renderHtml()
	{
//var_dump(__METHOD__);
				
		//$this->setHeader('Content-Type', $this->knownFormats['html']['mime'] . '; charset=utf-8;');
		$this->setHeader('Content-Type', self::$knownFormats['html']['mime'] . '; charset=utf-8;');
		
		$this->useTemplate = isset($this->useTemplate) 
			? $this->useTemplate 
			//: ( isset($this->body) && ( empty($this->request->outputModifiers) || in_array($this->request->outputModifiers[0], array('html','xhtml')) ) ? false : true );
			: ( isset($this->body) ? false : true );
			
//var_dump('useTemplate: ' . (int) $this->useTemplate);
		
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
		//$this->setHeader('Content-Type', $this->knownFormats['xhtml']['mime'] . '; charset=utf-8;');
		$this->setHeader('Content-Type', self::$knownFormats['xhtml']['mime'] . '; charset=utf-8;');
		$this->renderHtml();
		$this->currentFormat = 'html';
	}
	
	public function minify()
	{
		// No minification or minification via an Apache module 
		if ( _MINIFY_HTML_VIA === 'Apache-mod_pagespeeed' )
		{
			$this->templateEngine->display($this->template, $this->view->cacheId);
		}
		// No minification or minification via an Apache module
		elseif ( _MINIFY_HTML_VIA === 'Smarty-trimwhitespacefilter' )
		{
			$this->templateEngine->loadFilter('output', 'trimwhitespace');
			$this->templateEngine->display($this->template, $this->view->cacheId);
		}
		elseif ( _MINIFY_HTML_VIA === 'php-simple' )
		{
			// TODO
			$output 	= $this->templateEngine->fetch($this->template, $this->view->cacheId);
		    $search 	= array(
				'/\>[^\S ]+/s', //strip whitespaces after tags, except space
				'/[^\S ]+\</s', //strip whitespaces before tags, except space
				'/(\s)+/s'  // shorten multiple whitespace sequences
			);
		    $replace 	= array('>', '<','\\1');
			$output 	= preg_replace($search, $replace, $buffer);
			
			echo $output;
		}
		elseif ( _MINIFY_HTML_VIA === 'PHP-tidy' )
		{
			// TODO
			
			$output 	= $this->templateEngine->fetch($this->template, $this->view->cacheId);
			$options 	= array(
				'clean' 			=> true,
				'hide-comments' 	=> true,
				'indent' 			=> false,
				'indent-attributes' => false,
				'merge-divs' 		=> false,
				'merge-spans' 		=> false,
				);
			$Tidy = new tidy();
			$Tidy->parseString($output, $options, 'utf8');
			//$Tidy->cleanRepair();
			
			echo $Tidy;
		}
		elseif ( _MINIFY_HTML_VIA === 'Minify' )
		{
			$output = $this->templateEngine->fetch($this->template, $this->view->cacheId);
			$output = $this->minifyHtml($output);
			
			echo $output;
		}
	}

	public function minifyHtml($output)
	{
		require _PATH_LIBS . 'PHPGasus/outputs/Minify/lib/Minify/HTML.php';
		require _PATH_LIBS . 'PHPGasus/outputs/Minify/lib/Minify/CSS.php';
		require _PATH_LIBS . 'PHPGasus/outputs/Minify/lib/JSMin.php';

    	return Minify_HTML::minify($output, array(
    	));
	}
	
	
	public function renderJson()
	{		
		//$this->setHeader('Content-Type', $this->knownFormats['json']['mime'] . '; charset=utf-8;');
		$this->setHeader('Content-Type', self::$knownFormats['json']['mime'] . '; charset=utf-8;');
		$this->body = json_encode(isset($this->body) ? $this->body : $this->data);
		$this->currentFormat = 'json';
	}
	
	public function renderJsonp()
	{
		//$this->setHeader('Content-Type', $this->knownFormats['json']['mime'] . '; charset=utf-8;');
		$this->setHeader('Content-Type', self::$knownFormats['json']['mime'] . '; charset=utf-8;');
		
		$callback = !empty($_GET['callback']) ? filter_var($_GET['callback'], FILTER_SANITIZE_STRING) : null;
		$callback = !empty($callback) ? $callback : 'callback';
		
		$this->renderJson();
		$this->body = $callback . '(' . $this->body . ')';
		$this->currentFormat = 'jsonp';
	}

	public function renderJsonreport()
	{
		//$this->setHeader('Content-Type', $this->knownFormats['html']['mime'] . '; charset=utf-8;');
		$this->setHeader('Content-Type', self::$knownFormats['html']['mime'] . '; charset=utf-8;');
		
		$this->renderJson();
		$this->body = '<div id="json" class="jsonreport">' . $this->body . '</div>';
		$this->body .= '<script type="text/javascript" src="' . _URL . 'public/js/libs/jsonreport.js' . '"></script>';
		$this->body .= '<script type="text/javascript">window.onload = function(){ var json = document.getElementById("json"); json.innerHTML = _.jsonreport(document.getElementById("json").innerHTML) };</script>';
		$this->currentFormat = 'jsonp';
	}
	
	public function renderDatauri(){ return $this->renderdatauri(); }
	public function renderDataurl()
	{
		//$this->setHeader('Content-Type', $this->knownFormats['dataurl']['mime']);
		$this->setHeader('Content-Type', self::$knownFormats['dataurl']['mime']);

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
			//$mime = $this->knownFormats[$this->currentFormat]['mime'];
			$mime = self::$knownFormats[$this->currentFormat]['mime'];
			$this->body = 'data:' . $mime . ';base64,' . base64_encode($data);
		}
		
		$this->currentFormat = 'dataurl';
	}
	public function renderTxt()
	{
		//$this->setHeader('Content-Type', $this->knownFormats['txt']['mime'] . '; charset=utf-8;');
		$this->setHeader('Content-Type', self::$knownFormats['txt']['mime'] . '; charset=utf-8;');
		$this->body = isset($this->body) ? $this->body : $this->data;
		$this->currentFormat = 'txt';
	}
	
	public function renderBin()
	{
		$this->setheaders(array(
			//'Content-Type' 				=> $this->knownFormats['bin']['mime'],
			'Content-Type' 				=> self::$knownFormats['bin']['mime'],
			'Content-Transfer-Encoding' => 'Binary',
		));
		
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
			//'Content-Type' 				=> $this->knownFormats['download']['mime'],
			'Content-Type' 				=> self::$knownFormats['download']['mime'],
			'Content-Transfer-Encoding' => 'Binary',
		));
		$this->setFileBaseName();
		
		$this->body = isset($this->body) ? $this->body : $this->data;
	}
	
	public function renderXml()
	{
		//$this->setHeader('Content-Type', $this->knownFormats['txt']['mime'] . '; charset=utf-8;');
		$this->setHeader('Content-Type', self::$knownFormats['xml']['mime'] . '; charset=utf-8;');
		
		// TODO
		
		$this->currentFormat = 'xml';
	}
	
	public function renderCsv()
	{
//var_dump(__METHOD__);
		
//var_dump($this);
//die();
		
		$output 	= '';
		$eol 		= PHP_EOL;
		$o 			= array(
			'fixbool' 			=> false, 																				// transform bools to their string representation
			'separator' 		=> !empty($_GET['separator']) && in_array($_GET['separator'], array(',',';','\n','\t')) 
				? $_GET['separator'] 
				: ",",
			'addColumnNames' 	=> true,
			'addComments' 		=> true,
			'comment' 			=> '#',
			'eol' 				=> PHP_EOL . ($this->request->outputFormat === 'html' ? '<br/>' : ''),
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
			// Possible cases:
			// - several resources containing collection: $data = array('users' => $users, 'products' => $products, ...)
			// - only 1 resource : $data = array('users' => $users)
			
			$keys 				= array_keys((array) $data);
			$pattern 			= null;
			$collectionsCount 	= 0;
			$itemsCount 		= 0;
			
			// Loop over the data
			foreach ($keys as $k)
			{
				if ( !$pattern )
				{
					$isNumIndex 	= is_numeric($k);
					//$isResource 	= DataModel::isResource($k);
					$isResource 	= !$isNumIndex ? DataModel::isResource($k) : false;
					//$pattern 		= !$isNumIndex && $isResource ? 'multiple' : 'single'; // 'single' or 'multiple' resources		
					$pattern 		= !$isNumIndex && is_array($data[$k]) ? 'multiple' : 'single'; // 'single' or 'multiple' resources
				}
				
				// Handle case where the current looped item is a collection
				if ( $pattern === 'multiple' )
				{
					// Get current collection
					$collection = $data[$k];
					
					// Loop over the collection items
					foreach (array_keys($collection) as $itemIndex)
					{
						// Get current item
						$item = $collection[$itemIndex];
						
						if ( is_scalar($item) )
						{
							$output .= $o['fixbool'] && is_bool($item) ? ($item == true ? 'true' : 'false') : $item;
						}
						else
						{
							// Loop over the item columns
							if ( $itemsCount === 0 && $o['addComments'] && $o['addColumnNames'] )
							{
								// Add a 1st line with column names
								$output .= $o['comment'] . join($o['separator'], array_keys((array) $item)) . $o['eol'];
							}
							
							// TODO: loop over column values to be able to fix types?????
							//$output .= join($o['separator'], $item);
							$tmpCount = 0;
							foreach ($item as $val)
							{
								if ( $tmpCount !== 0 ) { $output .= $o['separator']; } 
	
								$output .= $o['fixbool'] && is_bool($val) 
									? ($val == true ? 'true' : 'false') 
									: (is_bool($val) ? (string) (int) $val : $val);
								$tmpCount++;
							}
						}

						$output .= $o['eol'];
						$itemsCount++;
					}
					
					unset($collection);
					$collectionsCount++;
				}
				else
				{
					// Get current collection
					$item = $data[$k];
					
					if ( is_scalar($item) )
					{
						$output .= $o['fixbool'] && is_bool($item) 
							? ($item == true ? 'true' : 'false') 
							: (is_bool($item) ? (string) (int) $item : $item);
					}
					else
					{
						// Loop over the item columns
						if ( $itemsCount === 0 && $o['addComments'] && $o['addColumnNames'] )
						{
							// Add a 1st line with column names
							$output .= $o['comment'] . join($o['separator'], array_keys($item)) . $o['eol'];
						}
						
						// TODO: loop over column values to be able to fix types?????
						//$output .= join($o['separator'], $item);
						$tmpCount = 0;
						foreach ($item as $val)
						{
							if ( $tmpCount !== 0 ) { $output .= $o['separator']; } 

							$output .= $o['fixbool'] && is_bool($val) 
								? ($val == true ? 'true' : 'false') 
								: (is_bool($val) ? (string) (int) $val : $val);
							$tmpCount++;
						}
					}
					
					$output .= $o['eol'];
					$itemsCount++;					
				}
			}
		}
		
		//$this->setHeader('Content-Type', $this->knownFormats['csv']['mime'] . '; charset=utf-8;');
		$this->setHeader('Content-Type', self::$knownFormats['csv']['mime'] . '; charset=utf-8;');
		$this->body = $output;
		$this->currentFormat = 'csv';
	}

	public function renderPhp()
	{		
		//$this->setHeader('Content-Type', $this->knownFormats['php']['mime']);
		$this->setHeader('Content-Type', self::$knownFormats['php']['mime']);
		$this->body = serialize(isset($this->body) ? $this->body : $this->data);
		$this->currentFormat = 'txt';
	}


	public function renderZip()
	{
		if 	( !extension_loaded('zip') )
		{
			// TODO: what should we do? Throw an error/exception??? If no previous modifiers, render default output???
			return;
		} 
		
		$this->body = isset($this->body) ? $this->body : $this->data;
		
		// Create a zip archive, open it for writing
		$zipFile 		= tempnam('tmp', 'zip');
		$zip 			= new ZipArchive();
		$zip->open($zipFile, ZipArchive::OVERWRITE);
		
		// Is file
			// wrap file in a zip
			//$zip->addFile($file);
		// Case no modifiers
			// render as default output format first
			//$this->renderDefault();
			//$zip->addFromString($this->body);
		// Otherwise
			// Create file
			//$zip->addFromString($this->body);
		
		$zip->close();
		
		$this->setHeaders(array(
			//'Content-Type' 		=> $this->knownFormats['zip']['mime'],
			'Content-Type' 		=> self::$knownFormats['zip']['mime'],
			
			'Content-Length' 	=> filesize($zipFile),
		));		
		$this->setFileBaseName();
		$this->currentFormat = 'zip';
		
		// Push the file to the client & delete the zip
		readfile($zipFile);
		//unlink($zipFile);
	}

	public function getTemplate()
	{
		
		
	}

	public function renderTemplate()
	{
		$v = &$this->view;
		
		function uclowerCallback($matches){ return strtolower($matches[0]); }
		
		$this->view = new ArrayObject(array_merge(array(
			// Caching
			'cache' 					=> _TEMPLATES_CACHING,
			'cacheId' 					=> null,
			'cacheLifetime' 			=> null,
			
			// Metas
			'name' 						=> $this->request->methodName,
			'title' 					=> isset($v['title']) 
				? $v['title'] 
				: ( isset($v['name']) ? ucfirst($v['name']) . ' - ' : '') . ( defined('_APP_TITLE') ? _APP_TITLE : _APP_NAME ),
			'metas' 			 		=> array(),
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
			'id' 						=> isset($v->id) ? $v->id : preg_replace_callback('/^([A-Z]{1})/', "uclowerCallback", str_replace(' ', '', ucfirst($v->name))),
		)), 2);

		$_req = $this->request;
		
		// Variables passed to the templates 
		$this->template = isset($this->template) 
			? $this->template 
			//: 'pages/' . $this->request->controllerRelPath . $this->request->resource . '/' . $this->request->methodName . '.html.tpl'; 
			//: 'pages/' . $this->request->controllerRelPath . $this->request->resource . '/' . $this->request->methodName . '.html.tpl';
			//: 'pages/' . $this->request->controllerRelPath . $this->request->resource . '/' . $this->request->methodName . '.html.tpl';
			: 'pages/' 
				. (!empty($_req->breadcrumbs) ? join('/' , $_req->breadcrumbs) . '/' : '') 
				//. $_req->resource . '/' 
				. ( !empty($_req->breadcrumbs) && end($_req->breadcrumbs) === $_req->resource ? '' : $_req->resource . '/' )
				. $_req->methodName 
				. '.html.tpl';
		$this->templateData = array(
			'request' 	=> $this->request, 
			'data' 		=> $this->data, 
			'view' 		=> $this->view,
			//'response' 	=> $this,
		);
		
		unset($_req);
		
		// TODO: Instead of the following, use a templateEngine class
		// $this->templateEngine = new templateEngine('smarty');
		// $this->templateEngine->conf(
			//'caching' 			=> isset($this->view['cache']) 			? $this->view['cache'] : _TEMPLATES_CACHING;
			// 'cache_lifetime' 	=> isset($this->view['cacheLifetime']) 	? $this->view['cacheLifetime'] : _TEMPLATES_CACHE_LIFETIME
		// )
		
		// Init templace engine
		if ( _TEMPLATES_ENGINE === 'smarty' )
		{
			require (_PATH_LIBS . 'PHPGasus/templating/Smarty/Smarty.class.php');
			
			// Instanciate a Smarty object and configure it
			$this->templateEngine 						= new Smarty();
			$this->templateEngine->compile_check 		= _TEMPLATES_COMPILE_CHECK;
			$this->templateEngine->force_compile 		= _TEMPLATES_FORCE_COMPILE;
			$this->templateEngine->caching 				= isset($this->view['cache']) 			? $this->view['cache'] : _TEMPLATES_CACHING;
			$this->templateEngine->cache_lifetime 		= isset($this->view['cacheLifetime']) 	? $this->view['cacheLifetime'] : _TEMPLATES_CACHE_LIFETIME;
			$this->templateEngine->template_dir 		= _PATH . 'templates/';
			$this->templateEngine->compile_dir 			= _PATH . 'templates/_precompiled/';
			$this->templateEngine->cache_dir 			= _PATH . 'templates/_cache/';
			$this->templateEngine->error_reporting 		= E_ALL & ~E_NOTICE;
			
			// 
			$this->templateEngine->assign($this->templateData);
		}
		// Otherwise, do not use template engine
		else
		{
			//require(_PATH . 'templates/' . $this->template);
			$output = file_get_contents(_PATH . 'templates/' . $this->template);
			echo $output;
		}
		
		try
		{
			$minify = defined('_MINIFY_HTML') && _MINIFY_HTML;
			
			// No minification
			if ( !$minify || ($minify && _MINIFY_HTML_VIA == '') )
			{
				// Just display the template
				$this->templateEngine->display($this->template, $this->view->cacheId);
			}
			// Otherwise
			else
			{
				// Handle minificifaction (different possible cases but basically, will fetch the template output, minify it and then display)
				$this->minify();
			}
			
		}
		catch(Exception $e)
		{
			echo $e->getMessage() . "<br/>";
			
			$defaultTpl = 'pages/default.html.tpl';
			
			if ( strpos($e->getMessage(), 'Unable to load template file') !== false )
			{
				// TODO: in PROD env, use 404 template, and in others env use default template?????
				$this->templateEngine->display($defaultTpl, $this->view->cacheId);	
			}
		}
	}
	
	public function render()
	{
		// Send headers
		$this->writeHeaders();
		
		// Special when using templating
		if ( $this->currentFormat === 'html' && ( !isset($this->useTemplate) || $this->useTemplate ) )
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