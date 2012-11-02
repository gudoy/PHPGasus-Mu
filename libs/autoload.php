<?php

// PSR-0 + phpgasus specific autoloader
function __autoload0($className)
{	
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
//	$lastNsPos = strripos($className, '\\');
	
//var_dump('classname: ' . $className);
//var_dump('lastNsPos: ' . $lastNsPos);
	
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
//var_dump('PHPGasus specific');
//		$className = substr($className, $lastNsPos + 1);
//var_dump($className);
		
		$first 		= $className[0]; 														// Get first letter
		$is2ndUp 	= $className[1] === strtoupper($className[1]); 							// Check if second is uppercased
				
		if ( in_array($first, array('M','V','C')) && $is2ndUp )
		{
			$known 		= array('C' =>'controller'); 										// Known classes types
			$type 		= isset($known[$first]) && $is2ndUp ? $known[$first] : 'lib'; 		// Set class type
			$path 		= constant('_PATH_' . strtoupper($type  . 's')); 					// Get class type base path
			//$fileName 	= $path . $className . '.php'; 									// Get class filepath
			//$classname 	= substr($className, strripos($className, '\\') + 1);
			$fileName 	= $path; 															// Get class filepath
		}
	}
	
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';	

//$fileName = str_replace('PHPGasus/controllers/', 'controllers/', $fileName);

var_dump('filename: ' . $fileName);

//die();

    require $fileName;
}

function __autoload1($className)
{
//var_dump('classname: ' . $className);
	
	$prefix 	= 'PHPGasus';
	
	$className 	= ltrim($className, '\\');
	$ini 		= $className;
	
	$lastNsPos 	= strripos($className, '\\');
	$className 	= substr($className, (int) $lastNsPos + 1);
	$namespace 	= $lastNsPos !== false ? substr($ini, 0, (int) $lastNsPos) : 0;
	$fileName 	= '';
	$first 		= $className[0]; 														// Get first letter
	$is2ndUp 	= $className[1] === strtoupper($className[1]); 							// Check if second is uppercased
	$known 		= array('C' =>'controller', 'M' => 'Model'); 						// Known classes types

//var_dump('ini: ' . $ini);
//var_dump('classname: ' . $className);
//var_dump('lastNsPos: ' . $lastNsPos);
//var_dump('namespace: ' . $namespace);
//var_dump('first: ' . $first);
//var_dump('is2ndUp: ' . (int) $is2ndUp);
		
	// PHPGasus specifc
	

	//$fileName 	= $path; 															// Get class filepath

//var_dump('filename: ' . $fileName);
//var_dump('path: ' . $path);

	if ( $is2ndUp && isset($known[$first]) )
	{
		$type 		= $known[$first]; 													// Set class type
		$path 		= constant('_PATH_' . strtoupper($type  . 's')); 					// Get class type base path		
		//$fileName  	= $path
//var_dump('namespace: ' . $namespace);
		//$namespace = str_replace($prefix . '\\' . $type  . 's', '', $namespace);
		$namespace = str_replace($prefix . '\\' . $type  . 's', $type . 's', '', $namespace);
//var_dump('namespace: ' . $namespace);
		$fileName  	= $path
			//. str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR 
			. str_replace('\\', DIRECTORY_SEPARATOR, $namespace)
			. str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	}
	else
	{
		$path 		= _PATH_LIBS;
		$fileName  	= $path . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	}
				
	//$fileName  	= $path . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';		
	

	
	//$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	
var_dump('filename: ' . $fileName);
//var_dump($namespace . '\\' . $className);

	//if ( class_exists($namespace . '\\' . $className) ){ return; }

	require $fileName;
}

function __autoload2($className)
{
    $className 	= ltrim($className, '\\');
    $fileName  	= '';
    $namespace 	= '';
	$known 		= array('controllers' => 'c', 'models' => 'm', 'views' => 'v');
	
    if ( $lastNsPos = strripos($className, '\\'))
    {
		$namespace = substr($className, 0, $lastNsPos);
		$className = substr($className, $lastNsPos + 1);
		$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	
	// PHPGasus specific:
	// If 2nd subnamespace is one of the known ones, set the proper file path
	$namespaces = explode('\\', $namespace);
	$type 		= !empty($namespaces[1]) && isset($known[$namespaces[1]]) ? $namespaces[1] : 'libs'; 
	$path 		= constant('_PATH_' . strtoupper($type));
	$filePath 	= $type !== 'libs' ? $path . str_replace('PHPGasus/' . $type . '/', '', $fileName) : $path . $fileName;
	
//var_dump('filename: ' . $fileName);

//var_dump('namespace: ' . $namespace);
//var_dump('type: ' . $type);
//var_dump($namespaces);
//var_dump('filePath: ' . $filePath);

    require $filePath;
}

// Classes autoloading
function phpGasusAutoload($className)
{
//var_dump(__METHOD__);
//var_dump('classname: ' . $className);
//if ( strpos($className, 'Smarty') !== false ){ return; }

	$className 	= ltrim($className, '\\');
	$hasNs 		= strpos($className, '\\') !== false;
	$ds 		= DIRECTORY_SEPARATOR;

	// Handle PSR-0
	if ( $hasNs )
	{
		$filePath = _PATH_LIBS . str_replace('\\', $ds, $className) . '.php';
	}
	// PHPGasus MV
	else
	{
		$first 		= $className[0]; 													// Get first letter
		$is2ndUp 	= $className[1] === strtoupper($className[1]); 						// Check if second is uppercased
		
		$known 		= array('C' =>'controllers', 'M' => 'Models'); 										// Known classes types
		$type 		= isset($known[$first]) && $is2ndUp ? $known[$first] : 'libs'; 		// Set class type
		$path 		= constant('_PATH_' . strtoupper($type)) . ( $type === 'libs' ? 'PHPGasus' . $ds : ''); 	// Get class type base path
		$filePath 	= $path . $className . '.php'; 										// Get class filepath		
	}
	

//var_dump('hasNs:' . (int) $hasNs);
//var_dump('classname: ' . $className);
//var_dump('filePath: ' . $filePath);

		
	//require($filePath);
	//file_exists($filePath) && require($filePath);
	if ( file_exists($filePath) ) { require($filePath); }
}

spl_autoload_register('phpGasusAutoload');

?>