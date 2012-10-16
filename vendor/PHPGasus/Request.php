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

//var_dump($this);

		// Allways call dispatchMethod (that will redispatch to proper method)	
		return call_user_func_array(array(new $this->controllerName($this), 'dispatchMethod'), $this->filters);	
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
		$this->filters = array();
		
		// TODO: bench preg_split + replaced request uri, preg_split + redirect_url, explode + skiping '/' in dispatch
		//$parts 		= preg_split('/\//', trim(str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), '/'));
		$this->filters 	= preg_split('/\//', trim(str_replace('?' . $_SERVER['QUERY_STRING'], '', $this->relativeURI), '/'));
		//$parts = preg_split('/\//', trim($_SERVER['REDIRECT_URL'], '/'));

//var_dump($this->filters);
	}
	
	public function getController()
	{
		$p 						= $this->filters;
		$this->controllerName 	= 'CIndex';
		
		// Controllers/$controler exists?
		if ( isset($p[0]) && ( $isFile = file_exists(_PATH_CONTROLLERS . 'C' . ucfirst($p[0]) . '.php') ) && $isFile )
		{
			$this->controllerName = 'C' . ucfirst($p[0]);
			array_shift($p);
			$this->filters = $p;
		}
		
		// TODO: support for folders

	}
	
	public function getMethod()
	{
		
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
		$cleaned 				= trim(str_replace($this->outputFormat, '', $this->extension), '.');
		$this->outputModifiers 	= empty($cleaned) ? array() : explode('.', $cleaned);
	}
	
	public function getParams()
	{
		
	}
}

?>