<?php

# 
define("_APP_CONTEXT", 					!getenv("APP_CONTEXT") ? 'prod' : getenv("APP_CONTEXT"));

# Try to get the server domain (or use the IP as the domain if it hasn't)
define("_HOST_IS_IP", 					filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_IP));
define("_DOMAIN", 						 _HOST_IS_IP ? $_SERVER['HTTP_HOST'] : preg_replace('/(.*\.)?(.*\..*)/', '$2', $_SERVER['SERVER_NAME']));

# Try to get the server subdomain (if not an ip)
define("_SUBDOMAIN", 					_HOST_IS_IP ? '' : str_replace('.' . _DOMAIN, '', $_SERVER['HTTP_HOST']));

# Get the projet full path on the server
define("_PATH",							realpath((dirname(realpath(__FILE__))) . '/../') . '/'); // 

# Get app name using base project folder name
define("_APP_NAME", 					basename(_PATH));

# Get path relatively to server root
define("_PATH_REL", 					str_replace($_SERVER['DOCUMENT_ROOT'], '', _PATH));

# Get used scheme (http or https)
define("_APP_PROTOCOL", 				'http' . ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '' ) . '://');

// If a server name has been defined, use it
// Otherwise, use the server ip and the project base folder path as the base URL
define("_URL", 							_APP_PROTOCOL . $_SERVER['HTTP_HOST'] . rtrim(_PATH_REL, '/') . '/');
define("_URL_REL", 						'/' . _PATH_REL);
define("_URL_STATIC", 					_APP_PROTOCOL . 'static.' . _DOMAIN . '/');
define("_URL_STATIC_1", 				_APP_PROTOCOL . 'static1.' . _DOMAIN . '/');


# Security
ini_set('session.cookie_httponly', 	1);
ini_set('session.cookie_secure', 	_APP_PROTOCOL === 'https' ? 1 :0 ); // Only active this when used with https

# Errors
// error_reporting(E_ERROR | E_PARSE); 				// PROD:
//error_reporting(E_ALL | E_STRICT | E_DEPRECATED);
error_reporting(2147483647); 						// DEV: display all errors
ini_set('display_errors', 1);


ini_set('xdebug.var_display_max_depth', 6);
ini_set('xdebug.var_display_max_data', 99999);
ini_set('xdebug.var_display_max_children', 999);
ini_set('xdebug.max_nesting_level', 500); // default is 100, which can be cumbersome with smarty 


// Force timezone to UTC
// TODO: handle this properly (allow user to choose its tz)
//$old = date_default_timezone_get();
date_default_timezone_set('UTC');


?>