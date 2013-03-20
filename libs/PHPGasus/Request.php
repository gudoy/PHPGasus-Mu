<?php

//namespace PHPGasus;

Class Request extends Core
{
	public function __construct()
	{
//var_dump(__METHOD__);

		// In case where the app do not use a hostname but is accessed instead via an IP, we are to remove the app base base from the request URI
		$this->relativeURI 		= str_replace(rtrim(_PATH_REL, '/'), '', $_SERVER['REQUEST_URI']);

		$this->getCurrentURL();
		$this->getCurrentURI();
		$this->getFilters();
		$this->getExtension();
		$this->getController();
	
		$this->getMethod();
		$this->getParams();
		
//die();		// TODO: move this in getController??? Somewehere else???
		$this->resource = substr(strtolower($this->controllerName), 1);
		

		return call_user_func_array(array(new $this->controllerName($this), $this->methodName), $this->filters);
	}

	public function getCurrentURL()
	{
		if ( isset($this->url) ){ return $this->url; }

    	$protocol 		= _APP_PROTOCOL;
		$host 			= $_SERVER['SERVER_NAME'];
		$tmp 			= parse_url($protocol . $host . $_SERVER['REQUEST_URI']);
		$tmp['query'] 	= isset($tmp['query']) ? urlencode(urldecode($tmp['query'])) : '';
		$path 			= join('', $tmp);

		$this->url = $protocol . $host . $_SERVER['REQUEST_URI'];
		
		return $this->url;
	}
	
	public function getCurrentURI()
	{
//var_dump(__METHOD__);
		
		$url 	= $this->getCurrentURL();
		$dotPos = strpos($url, '.');
		$hasDot = strpos($url, '.') !== false;
		
		if ( !$hasDot )
		{
			return $url;
		}
		else
		{	
			$parts 			= parse_url($url);
			$parts['path'] 	= preg_replace('/\..*$/','$1', $parts['path']); 
		
			$scheme   		= isset($parts['scheme']) ? $parts['scheme'] . '://' : ''; 
			$host     		= isset($parts['host']) ? $parts['host'] : ''; 
			$port     		= isset($parts['port']) ? ':' . $parts['port'] : ''; 
			$user     		= isset($parts['user']) ? $parts['user'] : ''; 
			$pass     		= isset($parts['pass']) ? ':' . $parts['pass']  : ''; 
			$pass     		= ($user || $pass) ? "$pass@" : ''; 
			$path     		= isset($parts['path']) ? $parts['path'] : ''; 
			$query    		= isset($parts['query']) ? '?' . $parts['query'] : ''; 
			$fragment 		= isset($parts['fragment']) ? '#' . $parts['fragment'] : ''; 
  
			$uri 			= $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
		}
		
		return $uri;
	}

	public function getFilters()
	{
		
//var_dump($_SERVER);
		
		// TODO: bench preg_split + replaced request uri, preg_split + redirect_url, explode + skiping '/' in dispatch
		//$cleaned 	= trim(str_replace('?' . $_SERVER['QUERY_STRING'], '', $this->relativeURI), '/');
		//$cleaned = trim($_SERVER['PATH_INFO'], '/'); // PATH_INFO is an Apache env and thus not always present
		//$cleaned = trim(str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), '?'); // does not handle redirection to /index/foo/path (as for foo.example.com/)
//var_dump($_SERVER['QUERY_STRING']);
//var_dump($_SERVER['PHP_SELF']);
		$cleaned = trim(str_replace(array('index.php', $_SERVER['QUERY_STRING'], '//'), array('', '', '/'), $_SERVER['PHP_SELF']), '/');
//var_dump($cleaned);
		$this->filters = !empty($cleaned) ? preg_split('/\//', $cleaned) : array();
//var_dump($this->filters);

//die();
	}

	public function getController()
	{
		$o = array(
			'requireFileInFolder' => true,
		);
		
		$this->controllerName 			= 'CIndex';
		$this->breadcrumbs 				= array();
		$this->controllerRelPath 		= '';
		
		// If the site is in maintenance
		if ( _IN_MAINTENANCE ) {  return; }

		// Special case for Home (propably the most visited page)
		// We can optmitize by directly calling the controller
		//elseif ( str_replace(rtrim(_PATH_REL, '/'), '', $_SERVER['REDIRECT_URL']) === '/' ) { return; }
		//elseif ( str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['PATH_INFO']) === '/' ) { return; } // PATH_INFO is an Apache env and thus not always present
		//elseif ( trim(str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), '?') === '/' ) { return; }
		elseif ( empty($this->filters) ) { return; }

		// Otherwise,
		// Loop over the request parts
		$i = -1;
		foreach ((array) $this->filters as $item)
		{
			$i++;	
			$item 		= strtolower($item); 													// Lowercase the item
			$hasNext 	= isset($this->filters[$i+1]); 											// Check if there's a next part to check against
			$cName 		= 'C' . ucfirst($item); 												// Controller name
			$cPath 		= _PATH_CONTROLLERS . (!empty($this->breadcrumbs) ? join('/', $this->breadcrumbs) . '/' : ''); 					// Current path to controller		
			$cFilepath 	= $cPath . $cName . '.php'; 											// Controller file path	

//var_dump($item);
//var_dump($cPath);
//var_dump($cFilepath);
//var_dump('hasNext:' . (int) $hasNext);
//var_dump($cFilepath);
//var_dump('is file:' . (int)is_file($cFilepath));
//var_dump('is dir:' . (int)is_dir($cPath . $item));

//var_dump($this->filters);					
//var_dump($cPath . $item);
			
			// Is an existing folder in controllers?
			// TODO: require the controller to exists??? For
			if ( ( $isDir = is_dir($cPath . $item) ) && $isDir )
			{
//var_dump('isfolder:' . $isDir);
//var_dump('hasNext:' . $hasNext);
//var_dump($cPath);
//var_dump($cPath . $item . '/' . $cName . '.php');

				$isFileinFolder = $o['requireFileInFolder'] && file_exists($cPath . $item . '/' . $cName . '.php');
				
//var_dump('isFileinFolder:' . (int)$isFileinFolder);

				// TODO: test if has index method????
				if ( $isFileinFolder )
				{
					$this->controllerName 	= $cName;
					$this->breadcrumbs[] 	= $item;
					array_shift($this->filters);
				}

				// If has next
				if 		( $hasNext )		{ continue; }
				elseif 	( $isFileinFolder )	{ array_shift($this->filters);  }
			}
			// Is an existing controller?
			elseif ( ( $isFile = is_file($cFilepath) ) && $isFile ){ $this->controllerName = $cName; array_shift($this->filters); break; }
		}

		$this->controllerRelPath = (!empty($this->breadcrumbs) ? join('/', $this->breadcrumbs) . '/' : '');

//var_dump($this);
//var_dump($this->filters);
//die();
		require(_PATH_CONTROLLERS . $this->controllerRelPath . $this->controllerName . '.php');
			
		return;
	}

	
	public function getMethod()
	{
//var_dump(__METHOD__);
		
//var_dump($this);
		
		//$params = func_get_args(); 
		$params = &$this->filters;
		$method = 'index';

//var_dump($params);
//var_dump($params[0]);
//var_dump($this->controllerNamespacedName);
//var_dump(method_exists($this->controllerNamespacedName, $params[0]));
		
		//if ( isset($params[0]) && $params[0] == 'new' && method_exists($this->controllerName, 'create') )
		if ( isset($params[0]) && $params[0] == 'new' && method_exists($this->controllerName, 'create') )
		{
			$method = 'create';
			array_shift($params);
		}
		//else if	( isset($params[0]) && method_exists($this->controllerName, $params[0]) && $params[0][0] !== '_' )
		else if	( isset($params[0]) && method_exists($this->controllerName, $params[0]) && $params[0][0] !== '_' )
		{
			$method = $params[0];
			array_shift($params);			
		}

//var_dump($this->controllerName);
//var_dump($method);
//var_dump(method_exists($this->controllerName, $params[0]));

		$this->methodName = $method;

//var_dump($this);
	}

	public function getExtension()
	{
		$p = $this->filters;
	
		// Get the extensions from the last param (if any)
		$lastKey 				= count($p)-1;
		$last 					= end($p);
		$dotPos 				= strpos($last, '.');
		$this->extension 		= !empty($p) && $dotPos !== false ? substr($last, $dotPos) : null;
		$this->outputFormat 	= _DEFAULT_OUTPUT_FORMAT;
		$this->outputModifiers 	= null;
		
		// Try to get expected output format
		$info 					= $dotPos !== false ? pathinfo(rtrim($this->extension, '.')) : null;
		$ext 					= !empty($info['extension']) ? $info['extension'] : null; 
		
		// Remove the extension in the last filter
		if ( $dotPos ){ $this->filters[$lastKey] = substr($last, 0, $dotPos); } 
		
		// If no known extension has been passed
		if ( !$ext || !isset(Response::$knownFormats[$ext]) )
		{
			// Get the 'accept' http header and split it to get all the accepted mime type with their prefered priority
			$accepts 	= !empty($_SERVER['HTTP_ACCEPT']) ? explode(',',$_SERVER['HTTP_ACCEPT']) : array();
			$prefs 		= array();
			$i 			= 1;
			$len 		= count($accepts);
			foreach ($accepts as $item)
			{				
				$mime 			= preg_replace('/(.*);(.*)$/', '$1', trim($item)); 										// just get the mime type (or like)
				
				// Do not process mime types that have already been found earlier in the loop (prevent priority conflicts)
				if ( !empty($prefs[$mime]) ){ continue; }
				
				$q 				= strpos($item, 'q=') !== false ? preg_replace('/.*q=()(,;\s)?/Ui','$1',$item) : 1; 	// get the priority (default=1)
				$prefs[$mime] 	= $q*100 + ($len);
				$len--;
			}
			
			// Fix this fucking webkit that prefer xml over html
			$ua = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
			
			if ( stripos($ua, 'webkit/') !== false )
			{				
				if ( isset($prefs['application/xml']) && isset($prefs['application/xhtml+xml']) && isset($prefs['text/html']) )
				{		
					$prefs['application/xml'] 	= $prefs['application/xml']-(2);
					$prefs['text/html'] 		= 150;
					
					if ( isset($prefs['image/png']) ){ $prefs['image/png'] = $prefs['application/xml']-(5); }
				}
			}
			
			// Fix this damn big fucking shit of ie that even does not insert text/html as a prefered type 
			// and prefers being served in their own proprietary formats (word,silverlight,...). MS screw you!!!!  
			if ( ($start = stripos($ua, 'MSIE')) && $start !== false && ($v = substr($ua, $start + 5, 1)) && $v <= 9 )
			{				
				if ( !isset($prefs['text/html']) ) { $prefs['text/html'] = 150; }
			}
			
			// Sort by type priority
			arsort($prefs);
			
			// Now, loop over the types and break as soon as we find a known type
			foreach ($prefs as $pref => $priority)
			{
				$result = array_search(array('mime' => $pref), Response::$knownFormats);
				 
				// If it's a known type, stop here
				if ( $result ){ $this->outputFormat = $result; break; }
			}
			
			// If no valid output format has been found, fallback to the default one
			if ( empty($this->outputFormat) ){ $this->outputFormat = _DEFAULT_OUTPUT_FORMAT; }
		}
		// Otherwise, set it as the output format and look for output modifiers
		else
		{
			$this->outputFormat 	= $ext;
			
			// Try to find modifiers to apply to output format 
			$cleaned 				= trim(preg_replace('/' . $this->outputFormat . '$/', '', $this->extension), '.');
			$this->outputModifiers 	= empty($cleaned) ? array() : explode('.', $cleaned);
		}
	}
	
	public function getParams()
	{
		
	}
}

?>