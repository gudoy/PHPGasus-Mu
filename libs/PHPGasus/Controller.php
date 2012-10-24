<?php

namespace PHPGasus;

Class Controller extends Core
{
	public $data = null;
	
	public function __construct(Request $Request)
	{
		parent::__construct();
		
		//$this->response = new Response($this);
		
		$this->request 	= $Request;
		$this->response = new Response();
		$this->response->request = &$this->request;
		
		$this->init();
	}
	
	public function init()
	{
		if ( !empty($this->inited) ){ return; }
		
		//$this->inited = true;
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