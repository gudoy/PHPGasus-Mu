<?php

namespace PHPGasus;

Class Application extends Core
{
	public function __construct(){}
	
	public function init()
	{
		return new Request();
	}
}

?>