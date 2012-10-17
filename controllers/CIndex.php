<?php

Class CIndex extends PHPGasus\Controller 
{	
	public function index()
	{
		//$this->data = 'Welcome to PHPGasus Mû!';
		$this->response->body = '<h1>Welcome to PHPGasus Mû!</h1>';
		
		$this->render();
	}
}


?>