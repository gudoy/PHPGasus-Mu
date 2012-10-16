<?php

namespace PHPGasus;

Class Controller extends Core
{
	public $data = null;
	
	public function __construct(Request $Request)
	{
		$this->request 	= $Request;
		$this->response = new Response();
		$this->response->request = &$this->request;
		
		parent::__construct();
		
		$this->init();
	}
	
	public function init()
	{
		//$this->response = new Response($this);
	}
	
	public function dispatchMethod()
	{
		$params = func_get_args(); 
		$method = 'index';
		
		if ( isset($params[0]) && $params[0] == 'new' && method_exists($this, 'create') )
		{
			$method = 'create';
			array_shift($params);
		}

		// TODO: test if method exists
			
		return call_user_func_array(array($this, $method), $params);
	}
	
	public function render()
	{
		$_req 	= &$this->request;
		$_resp 	= &$this->response;
		
		$of 	= $this->request->outputFormat;
		
		//$_resp->data = $this->data;
		$_resp->data = &$this->data;
		
		// Loop over output format modifiers
		foreach ((array) $_req->outputModifiers as $format)
		{
			// Do not continue any longer if there's a current response body and if it's already in the proper format
			if ( isset($_resp->body) && $_resp->currentFormat === $format ){ continue; }
			
			// Skip unknown response formats
			if ( !isset($_resp->knownFormats[$format]) || !method_exists($_resp, 'render' . ucfirst($format)) );
			
			// Otherwise, call the proper rendering method
			$_resp->{'render' . ucfirst($format)}();
		}
		
		// Call the final rendering method
		$_resp->{'render' . ucfirst($of)}();
		
		echo $_resp->body;
	}	
}

?>