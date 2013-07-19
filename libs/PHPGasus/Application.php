<?php

Class Application extends Core
{
	public function __construct(){}
	
	public function init()
	{
		$this->initEnv();
		
		return new Request();
	}
	
	public function initEnv()
	{
		if ( in_array(_APP_CONTEXT, array('dev','local')) )
		{
			# Errors
			error_reporting(2147483647); 						// DEV: display all errors
			ini_set('display_errors', 1);
			
			if ( in_array(_APP_CONTEXT, array('dev','local')) )
			{
				//require( _PATH . 'libs/PHPGasus/dev/errors/php_error.php' );
				//php_error\reportErrors(array('catch_ajax_errors' => false));
			}
			
			ini_set('xdebug.var_display_max_depth', 6);
			ini_set('xdebug.var_display_max_data', 99999);
			ini_set('xdebug.var_display_max_children', 999);
			ini_set('xdebug.max_nesting_level', 500); // default is 100, which can be cumbersome with smarty 
		}
		else
		{
			# Errors
			error_reporting(E_ERROR | E_PARSE); 				// PROD:
			ini_set('display_errors', 0);			
		}
		
		# Security
		ini_set('session.cookie_httponly', 	1);
		ini_set('session.cookie_secure', 	_APP_PROTOCOL === 'https' ? 1 :0 ); // Only active this when used with https

		// Force timezone to UTC
		// TODO: handle this properly (allow user to choose its tz)
		//$old = date_default_timezone_get();
		date_default_timezone_set('UTC');
	}
}

?>