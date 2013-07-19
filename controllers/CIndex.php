<?php

Class CIndex extends Controller 
{	
	public function index()
	{
		// Set the response body content
		//$this->response->body = '<h1>Welcome to PHPGasus Mû!</h1>';
		
		// Data to be passed to the response
		//$this->data = 'Welcome to PHPGasus Mû!';
		//$this->data = array('foo' => 'foo', 'bar' => 'bar', 'foobar' => 42);
		
		// Use an .html file as template
		//$this->response->template = _PATH . 'templates/' . __FUNCTION__ . '.html';
		
		// Set an explicit view name
		$this->response->view->name = 'home';
		
		$this->render();
	}
 
	public function down()				{ $this->render(); }
	public function maintenance()		{ $this->render(); }
	public function down()				{ $this->render(); }
	public function notfound()			{ $this->render(); }

}


?>