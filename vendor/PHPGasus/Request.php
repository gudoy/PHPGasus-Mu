<?php

namespace PHPGasus;

Class Request extends Core
{
	public function __construct()
	{
//var_dump(__METHOD__);

		// In case where the app do not use a hostname but is accessed instead via an IP, we are to remove the app base base from the request URI
		$this->relativeURI 		= str_replace(rtrim(_PATH_REL, '/'), '', $_SERVER['REQUEST_URI']);

		$this->getCurrentURL();
		$this->getFilters();
		$this->getExtension();
		$this->getController();
		$this->getMethod();
		$this->getParams();

		// Allways call dispatchMethod (that will redispatch to proper method)	
		//return call_user_func_array(array(new $this->controllerName($this), 'dispatchMethod'), $this->filters);
		return call_user_func_array(array(new $this->controllerName($this), $this->methodName), $this->filters);
	}

	public function getCurrentURL()
	{
		if ( isset($this->url) ){ return $this->url(); }

    	$protocol 		= _APP_PROTOCOL;
		$host 			= $_SERVER['SERVER_NAME'];
		$tmp 			= parse_url($protocol . $host . $_SERVER['REQUEST_URI']);
		$tmp['query'] 	= isset($tmp['query']) ? urlencode(urldecode($tmp['query'])) : '';
		$path 			= join('', $tmp);

		$this->url = $protocol . $host . $_SERVER['REQUEST_URI'];
		
		return $this->url;
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
	
	public function getController()
	{
		$p 						= $this->filters;
		$this->controllerName 	= 'CIndex';
		
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

	}
	
	public function getMethod()
	{
//var_dump(__METHOD__);
		
//var_dump($this);
		
		//$params = func_get_args(); 
		$params = &$this->filters;
		$method = 'index';
		
		if ( isset($params[0]) && $params[0] == 'new' && method_exists($this->controllerName, 'create') )
		{
			$method = 'create';
			array_shift($params);
		}
		else if	( isset($params[0]) && method_exists($this->controllerName, $params[0]) && $params[0][0] !== '_' )
		{
			$method = $params[0];
			array_shift($params);			
		}

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