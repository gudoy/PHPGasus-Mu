<?php

// 

class TemplateEngine
{
	public $engine 			= null;
	public $engineInstance 	= null;
	public $options 		= array();
	
	// __construct() ==> default: no engine 
	public function __construct($engineName)
	{
		$method = 'init' . ucfirst($engineName);
		
		if ( method_exists($this, $method) ){ call_user_method($method); }
	}
	
	// setOptions(['$name1' => $value1, '$name2' => $value2, ...])
	public function setOptions($mixed)
	{
		$args 	= func_get_args();
		$sign 	= !isset($args[1]) ? 'set' : 'get'; 
		
			$names = Tools::toArray($args[1]);
			
			foreach ( $names as $name => $value ){ $this->setOption($name, $value); }
	}
	
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
		$this->engineInstance->{$name} = $value;
	}
	
	public function initSmarty()
	{
		require (_PATH_LIBS . 'PHPGasus/templating/Smarty/Smarty.class.php');
		
		$this->engineInstance = new Smarty();
		
		$this->conf(array(
			'compile_check' 	=> _TEMPLATES_COMPILE_CHECK,
			'force_compile' 	=> _TEMPLATES_FORCE_COMPILE,
			'caching' 			=> _TEMPLATES_CACHING,
			'cache_lifetime' 	=> _TEMPLATES_CACHE_LIFETIME,
			'template_dir' 		=> _PATH . 'templates/',
			'compile_dir' 		=> _PATH . 'templates/_precompiled/',
			'cache_dir' 		=> _PATH . 'templates/_cache/',
			
			'error_reporting' 	=> E_ALL & ~E_NOTICE,
		));
	}
	
	public function loadFilter($name)
	{
		$this->engineInstance->loadFilter('output', 'trimwhitespace');	
	}
	
	public function assign()
	{
		$this->engineInstance->assign($this->templateData);
	}
	
	public function display($template, $cacheId)
	{
		$this->engineInstance->display($template, $cacheId);
	}
	
	public function fetch()
	{
		
	}
}

?>