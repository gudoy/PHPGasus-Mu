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
		
		
		$this->controllerNamespacedName = $this->controllerName;
		//$this->controllerNamespacedName = 'PHPGasus\\controllers\\' . str_replace('/', '\\', $this->controllerRelPath) . $this->controllerName;
		
//var_dump($this);
//var_dump($this->controllerNamespacedName);
//die();
	
		$this->getMethod();
		$this->getParams();
		
//die();		// TODO: move this in getController??? Somewehere else???
		$this->resource = substr(strtolower($this->controllerName), 1);
		
//die();
		//class_exists($this->controllerNamespacedName);
		
		//return call_user_func_array(array(new $this->controllerName($this), $this->methodName), $this->filters);
		return call_user_func_array(array(new $this->controllerNamespacedName($this), $this->methodName), $this->filters);
		//return call_user_func_array(array(new $this->controllerNamespacedName(null), 'index'), $this->filters);
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
		//$this->filters = array();
		
		// TODO: bench preg_split + replaced request uri, preg_split + redirect_url, explode + skiping '/' in dispatch
		//$parts 		= preg_split('/\//', trim(str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), '/'));
		$cleaned 	= trim(str_replace('?' . $_SERVER['QUERY_STRING'], '', $this->relativeURI), '/');
		$this->filters = !empty($cleaned) ? preg_split('/\//', $cleaned) : array();

//var_dump($this->filters);
	}
	
	public function getController0()
	{
		$p 								= $this->filters;
		$this->controllerName 			= 'CIndex';
		$this->controllerRelPath 		= '';
		$this->breadcrumbs 				= array();
		
		// Controllers/$controler exists?
		//if ( isset($p[0]) && ( $isFile = file_exists(_PATH_CONTROLLERS . 'C' . ucfirst($p[0]) . '.php') ) && $isFile )
		//if ( isset($p[0]) && ($cName = 'C' . ucfirst($p[0])) && $cName && ($isFile = file_exists(_PATH_CONTROLLERS . $cName . '.php')) && $isFile )
		
		if ( isset($p[0]) && ($cName = 'C' . ucfirst($p[0])) && $cName && ($isFile = file_exists(_PATH_CONTROLLERS . $cName . '.php')) && $isFile && class_exists($cName) )
		{
			//$this->controllerName = 'C' . ucfirst($p[0]);
			$this->controllerName = $cName;
			array_shift($p);
			$this->filters = $p;
		}
		
		// TODO: support for folders

		$this->controllerNamespacedName 		= $this->controllerName;
	}
	
	public function getController()
	{
		$this->controllerName 			= 'CIndex';
		$this->controllerRelPath 		= '';
		$this->breadcrumbs 				= array();
		//$this->controllerNamespacedName = $this->controllerName;
		
		// If the site is in maintenance
		if ( _IN_MAINTENANCE ) {  return; }

		// Special case for Home (propably the most visited page)
		// We can optmitize by directly calling the controller
		elseif ( str_replace(rtrim(_PATH_REL, '/'), '', $_SERVER['REDIRECT_URL']) === '/' ) { return; }

		// Otherwise,
		// Loop over the request parts
		$i = -1;
		foreach ((array) $this->filters as $item)
		{
			$i++;	
			$item 		= strtolower($item); 													// Lowercase the item
			$hasNext 	= isset($this->filters[$i+1]); 											// Check if there's a next part to check against
			$cName 		= 'C' . ucfirst($item); 												// Controller name
			$cPath 		= _PATH_CONTROLLERS . 
				( $this->breadcrumbs ? join('/', $this->breadcrumbs) . '/' : '' ); 					// Current path to controller
			$cFilepath 	= $cPath . $cName . '.php'; 											// Controller file path	

//var_dump($item);
//var_dump($cPath);
//var_dump($cFilepath);
//var_dump('hasNext:' . (int) $hasNext);
//var_dump($cFilepath);
//var_dump('is file:' . (int)is_file($cFilepath));
//var_dump('is dir:' . (int)is_dir($cPath . $item));
						
			
			// Is an existing folder in controllers?
			// TODO: require the controller to exists??? For
			if ( ( $isDir = is_dir($cPath . $item) ) && $isDir )
			{
//var_dump('isfolder:' . $isDir);
//var_dump($cPath);
//var_dump($cPath . $item . '/' . $cName . '.php');

				if ( ( $isFileinFolder = file_exists($cPath . $item . '/' . $cName . '.php') ) && $isFileinFolder )
				{
//var_dump('is file in folder:' . $isFile);
					$this->controllerName = $cName;
					$this->controllerRelPath .= $item . '/';

					array_shift($this->filters);
				}
				
				if 	( ( $isFile = is_file($cFilepath) ) && $isFile ){ $this->controllerName = $cName; }
		
				// Is there a next item?
				if 		( $hasNext ){ $this->breadcrumbs[] = $item; continue; }
				
				// Otherwise, does the controller in the same folder or the current directory
				//if 	( ( $isFile = is_file($cFilepath) ) && $isFile ){ $this->controllerName = $cName; }
			}
			// Is an existing controller?
			elseif ( ( $isFile = is_file($cFilepath) ) && $isFile ){ $this->controllerName = $cName; }
		}

//var_dump($this);
		
		//$cName = 'PHPGasus\\controllers\\' . join('\\', $this->breadcrumbs) . str_replace('//', '\\', $this->controllerRelPath) . $this->controllerName;
		//$this->controllerNamespacedName = 'controllers\\' . str_replace('/', '\\', $this->controllerRelPath) . $this->controllerName;

		//require($this->controllerRelPath . $this->controllerName . '.php');
		//require($cPath . $this->controllerName . '.php');
		require(_PATH_CONTROLLERS . $this->controllerRelPath . $this->controllerName . '.php');
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
		
		//if ( isset($params[0]) && $params[0] == 'new' && method_exists($this->controllerName, 'create') )
		if ( isset($params[0]) && $params[0] == 'new' && method_exists($this->controllerNamespacedName, 'create') )
		{
			$method = 'create';
			array_shift($params);
		}
		//else if	( isset($params[0]) && method_exists($this->controllerName, $params[0]) && $params[0][0] !== '_' )
		else if	( isset($params[0]) && method_exists($this->controllerNamespacedName, $params[0]) && $params[0][0] !== '_' )
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
		
		// Do not continue any longer if the last param does not contains extension
		if ( $dotPos === false ) { return; }
		
		$this->filters[$lastKey] = substr($last, 0, $dotPos);
		
		// Try to get expected output format
		$info 					= pathinfo(rtrim($this->extension, '.'));
		$this->outputFormat 	= $info['extension'];
		
		// Try to find modifiers to apply to output format 
		//$cleaned 				= trim(str_replace($this->outputFormat, '', $this->extension), '.');
		$cleaned 				= trim(preg_replace('/' . $this->outputFormat . '$/', '', $this->extension), '.');
		$this->outputModifiers 	= empty($cleaned) ? array() : explode('.', $cleaned);
	}
	
	public function getParams()
	{
		
	}
}

?>