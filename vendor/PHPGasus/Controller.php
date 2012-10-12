<?php

namespace PHPGasus;

Class Controller extends Core
{
	public $data = null;
	
	public function __construct(Request $Request)
	{
		$this->request 	= $Request;
		$this->response = new Response();
		
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
	
	public function render()
	{
		$_req 	= &$this->request;
		$_resp 	= &$this->response;
		$_of 	= $this->request->outputFormat;
		
		if ( $_of === 'dataurl' )
		{
			if ( is_string($this->data) && preg_match('/^data\:[a-z\-\*]*\/.*\;base64\,.*/', $this->data) )
			{
				$_resp->body = $this->data;
			}
			elseif ( file_exists($_req->url) )
			{
				$filename 	= $_req->url;
				$mime 		= null;
				
				if ( function_exists("finfo_file") )
				{
				    $finfo 	= finfo_open(FILEINFO_MIME_TYPE);
				    $mime 	= finfo_file($finfo, $filename);
				    finfo_close($finfo);
				}
				else if ( function_exists('mime_content_type') ) 				{ $mime = mime_content_type($filename); }
				else if ( !stristr(ini_get("disable_functions"), "shell_exec"))	{ $mime = shell_exec("file -bi " . escapeshellarg($filename)); }
				
				$_resp->body = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($_req->url));
			}
			
			header('plain/text');
		}
		else if ( $_of === 'json' )
		{			
			header('application/json');
			$_resp->body = json_encode($this->data);
		}
		else if ( $_of === 'txt' )
		{
			header('plain/text');
			$_resp->body = $this->data;
		}
		else if ( $_of === 'html' )
		{
			// Has the template been defined
			if ( !empty($_resp->template) && file_exists($_resp->template) )
			{
				$_resp->body = str_replace('public/', '/public/', file_get_contents($_resp->template));	
			}
			// Assume returned data are already in HTML
			else
			{
				$_resp->body = !empty($_resp->body) ? $_resp->body : $this->data;	
			}
			
		}
		
		echo $_resp->body;
	}
}

?>