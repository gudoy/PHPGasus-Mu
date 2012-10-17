<?php

Class CTests extends PHPGasus\Controller 
{
	public function index()
	{
var_dump(__METHOD__);
		$this->data = array('foo' => 'bar', 'foobar' => 42, 'bar' => false);
		
		$this->render();
	}
	
	public function foo()
	{
var_dump(__METHOD__);	
		$this->data = 'foo';
		
		$this->render();
	}
	
	public function json()
	{
var_dump(__METHOD__);
		
		$this->data = array('foo' => 'bar', 'foobar' => 42, 'bar' => false);
		
		$this->render();		
	}
}

?>