<?php

// PSR-0 + phpgasus specific autoloader
function __autoload($className)
{	
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
	
//var_dump($lastNsPos = strripos($className, '\\'));
	
    if ( $lastNsPos = strripos($className, '\\') )
    {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
		
		$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
	// PHPGasus specific
	else
	{
		$first 		= $className[0]; 														// Get first letter
		$is2ndUp 	= $className[1] === strtoupper($className[1]); 							// Check if second is uppercased
				
		if ( in_array($first, array('M','V','C')) && $is2ndUp )
		{
			$known 		= array('C' =>'controller'); 										// Known classes types
			$type 		= isset($known[$first]) && $is2ndUp ? $known[$first] : 'lib'; 		// Set class type
			$path 		= constant('_PATH_' . strtoupper($type  . 's')); 					// Get class type base path
			//$fileName 	= $path . $className . '.php'; 									// Get class filepath	
			$fileName 	= $path; 															// Get class filepath
		}
	}
	
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';	

//var_dump($fileName);

    require $fileName;
}
spl_autoload_register('__autoload');

?>