<?php

namespace PHPGasus;

Class Request extends Core
{
	public function __construct()
	{
//var_dump(__METHOD__);

		// In case where the app do not use a hostname but is accessed instead via an IP, we are to remove the app base base from the request URI
		$this->relativeURI 		= str_replace(rtrim(_PATH_REL, '/'), '', $_SERVER['REQUEST_URI']);

		$this->getParams();
		$this->getExtension();
		$this->getController();

//var_dump($this);

		// Allways call dispatchMethod (that will redispatch to proper method)	
		return call_user_func_array(array(new $this->controllerName($this), 'dispatchMethod'), $this->params);	
	}

	public function getParams()
	{
		$this->params = array();
		
		// TODO: bench preg_split + replaced request uri, preg_split + redirect_url, explode + skiping '/' in dispatch
		//$parts 		= preg_split('/\//', trim(str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), '/'));
		$this->params 	= preg_split('/\//', trim(str_replace('?' . $_SERVER['QUERY_STRING'], '', $this->relativeURI), '/'));
		//$parts = preg_split('/\//', trim($_SERVER['REDIRECT_URL'], '/'));

//var_dump($this->params);
	}

	public function getExtension()
	{
		$p = $this->params;
	
		// Get the extensions from the last param (if any)
		$lastKey 				= count($p)-1;
		$last 					= end($p);
		$dotPos 				= strpos($last, '.');
		$this->extension 		= !empty($p) && $dotPos !== false ? substr($last, $dotPos) : null; 
		
		// Do not continue any longer if the last param does not contains extension
		if ( $dotPos === false ) { return; }
		
		$this->params[$lastKey] = substr($last, 0, $dotPos);
		
		$info 					= pathinfo($this->extension);
		$this->outputFormat 	= $info['extension'];
	}
	
	public function getController()
	{
		$this->controllerName 	= 'CIndex';
		
		$p = $this->params;
		
		// Controllers/$controler exists?
		if ( isset($p[0]) && ( $isFile = file_exists(_PATH_CONTROLLERS . 'C' . ucfirst($p[0]) . '.php') ) && $isFile )
		{
			$this->controllerName = 'C' . ucfirst($p[0]);
			array_shift($p);
			$this->params = $p;
		}
		
		// TODO: support for folders
	}
}

?>