<?php

// Classes autoloading
function phpGasusAutoload($className)
{
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
	
	if ( file_exists($filePath) ) { require($filePath); }
}

spl_autoload_register('phpGasusAutoload');

?>