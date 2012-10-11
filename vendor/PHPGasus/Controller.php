<?php

namespace PHPGasus;

Class Controller extends Core
{
	public function __construct(Request $Request)
	{
		$this->request 	= $Request;
		
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
		
		if ( isset($params[0]) && $params[0] == 'new' )
		{
			$method = 'create';
			array_shift($params);
		}

		// TODO: test if method exists
			
		return call_user_func_array(array($this, $method), $params);
	}
}

?>