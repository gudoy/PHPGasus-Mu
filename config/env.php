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
define("_URL_STATIC_2", 				_APP_PROTOCOL . 'static2.' . _DOMAIN . '/');
define("_URL_STATIC_3", 				_APP_PROTOCOL . 'static3.' . _DOMAIN . '/');
define("_URL_STATIC_4", 				_APP_PROTOCOL . 'static4.' . _DOMAIN . '/');


// Specific
define("_APP_NAME", 					basename(_PATH)); 		// Get app name using base project folder name
define("_APP_DISPLAY_NAME", 			'');
define("_APP_TITLE", 					'');
define("_APP_DESCRIPTION", 				'');
define("_APP_KEYWORDS", 				'');
define("_APP_AUTHOR", 					'');

define("_APP_TILE_COLOR", 				'#000000');

define("_GOOGLE_ANALYTICS_UA", 			'');
define("_GOOGLE_ANALYTICS_DOMAIN", 		_DOMAIN);

?>