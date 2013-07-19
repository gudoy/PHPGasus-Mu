<?php

define("_IN_MAINTENANCE", 						false); 						// Set this to true to redirect all requests to the maintenance page (/maintenance)
	
### VERSIONING	
define('_PHPGASUS_MU_VERSION', 					'0.0.1.0');
define("_APP_VERSION", 							'0.0.1.0');

define("_DEFAULT_OUTPUT_FORMAT", 				'html');
define("_TEMPLATES_ENGINE", 					'smarty'); 						// 'Smarty' or 'none'/null/false 
define("_TEMPLATES_COMPILE_CHECK", 				true);
define("_TEMPLATES_FORCE_COMPILE", 				false);
define("_TEMPLATES_CACHING", 					false);
define("_TEMPLATES_CACHE_LIFETIME", 			60*60);

define("_MINIFY_CSS", 							false); 						// Not yet implemented
define("_MINIFY_JS", 							false); 						// Not yet implemented
define("_MINIFY_HTML", 							false); 						// Experimental
define("_MINIFY_HTML_VIA", 						'PHP-tidy'); 					// 'Apache-mod_pagespeeed', 'Smarty-trimwhitespacefilter', 'PHP-tidy', 'Minify'



?>