<?php

//namespace PHPGasus;
//namespace PHPGasus\Controllers;
//use PHPGasus;

Class CIndex extends Controller
//Class CIndex extends \PHPGasus\Controller 
{	
	public function index()
	{
		//$this->data = 'Welcome to PHPGasus Mû!';
		$this->response->body = '<h1>Welcome to PHPGasus Mû!</h1>';
		
		$this->render();
	}
	
	public function maintenance()
	{
var_dump(__METHOD__);
		
		$this->render();
	}
 
 
	public function down()
	{
		$this->render();
	}
	
	public function notfound()
	{
var_dump(__METHOD__);
		
		$this->render();
	}
}


?>