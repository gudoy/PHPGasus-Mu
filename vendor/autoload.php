<?php

// Classes autoloading
function __phpgasus_autoload($className)
{	
	$first 		= $className[0]; 													// Get first letter
	
	$is2ndUp 	= $className[1] === strtoupper($className[1]); 						// Check if second is uppercased
	
	if ( $first === 'C' && $is2ndUp ) { return false; } 
	
	$known 		= array('C' =>'controller'); 										// Known classes types
	$type 		= isset($known[$first]) && $is2ndUp ? $known[$first] : 'lib'; 		// Set class type
	$path 		= constant('_PATH_' . strtoupper($type  . 's')); 					// Get class type base path
	$file 		= $path . $className . '.php'; 										// Get class filepath

var_dump(__METHOD__);
var_dump($className);
//var_dump($className);
var_dump($file);
	
	//class_exists($className) || (file_exists($file) && require($file));
	return (file_exists($file) && require($file));
}
//spl_autoload_register('__autoload');

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

//var_dump('first: ' . $first);
//var_dump('is2ndUp: ' . (int) $is2ndUp);
				
		if ( in_array($first, array('M','V','C')) && $is2ndUp )
		{
			$known 		= array('C' =>'controller'); 										// Known classes types
			$type 		= isset($known[$first]) && $is2ndUp ? $known[$first] : 'lib'; 		// Set class type
			$path 		= constant('_PATH_' . strtoupper($type  . 's')); 					// Get class type base path
			//$fileName 	= $path . $className . '.php'; 									// Get class filepath	
			$fileName 	= $path; 															// Get class filepath
		}
	}
	
//var_dump(__METHOD__);
//var_dump('classname: ' . $className);
//var_dump($className);
//var_dump('filename: ' . $fileName);
	
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

//var_dump('filename: ' . $fileName);	

    require $fileName;
}
spl_autoload_register('__autoload');

?>