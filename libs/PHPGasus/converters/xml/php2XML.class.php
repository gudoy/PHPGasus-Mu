<?php

class php2XML
{
	public $eol 				= PHP_EOL;
	//public $eol 				= "";
	public $tab 			= "\t";
	public $currentTabsNb 	= 0;
	
	public function __construct()
	{
		return $this;
	}
	
	public function process($data, $options = null)
	{
		$data = (Array) $data;
		
		$this->o = $options;
		
		// Add XML prolog 
		$this->output = '<?xml version="1.0" encoding="UTF-8" ?>' . $this->eol;
		
		// Start wrappring the data in a main object if option setted
		if ( !empty($o['mainObjectName']) ) { $this->output .= '<' . $o['mainObjectName'] . '>' . $this->eol; }
		
		// Loop over the data
		$this->loop($data, 'root');

		// End wrappring the data in a main object if option setted		
		if ( !empty ($o['mainObjectName']) ) { $this->output .= '</' . $o['mainObjectName'] . '>'; }
		
		return $this->output;
	}
	
	public function loop($obj, $mode = null, $parentName = '', $tabsNb = null)
	{		
		if ( !is_array($obj) ) return $obj;

		$tabsNb = !empty($tabsNb) ? $tabsNb + 1 : $this->currentTabsNb++;
		
		$s = (Object) array('openTagTabs' => 0, 'eolAfterOpenTag' => true, 'tabsBeforeContent' => 0, 'eolAfterContent' => true, 'closeTagTabs' => 0, 'eolAfterCloseTag' => true);
		
		switch($mode)
		{
			case 'root':		$submode = 'items'; break;
			case 'items':		$submode = 'properties'; $s->openTagTabs = 1; $s->eolAfterContent = false; $s->closeTagTabs = 1; break;
			case 'properties': 	$submode = null; $s->openTagTabs = 2; $s->eolAfterOpenTag = false; $s->eolAfterContent = false; break;
			default: 			break;
		}
		
		$count = count($obj);
		$i = 0;
		foreach ($obj as $key => $val)
		{			
			$i++;
			
			$isLast 	= $i === $count;
			$submode 	= null;
			$eltName     = is_numeric($key) || !$this->isValidElement($key)/*|| !empty($parentName)*/ ? Tools::singular($parentName) : $key;
//$this->dump($key);
//$this->dump($parentName);
//if ( is_array($val) ) { $this->dump($eltName); }
			
			// Store the current element
			
			$this->output .= $this->addTabs($tabsNb) . '<' . ( $eltName ) . ( $eltName !== $key ? ' key="' . $key . '"' : '' ) . '>';
			
			$this->output .= $s->eolAfterOpenTag && is_array($val) && !empty($val) ? $this->eol : '';
			//$this->output .= $this->addTabs($s->tabsBeforeContent);
			
/*
if ( is_array($val) )
{
	$this->dump($val);
	$this->dump($parentName);
	$this->dump($key);
}
*/
			
			$this->output .= ( !is_array($val) || empty($val) ? $this->handleValue($val) : $this->loop($val, $submode, $key, $tabsNb) );
			$this->output .= ($s->eolAfterContent && is_array($val) && !empty($val) ? $this->eol : '');
			
			//$this->output .= $this->addTabs($s->closeTagTabs) . '</' . $eltName .  '>' . ($s->eolAfterCloseTag ? $this->eol : '');
			$this->output .= ( !is_array($val) || empty($val) ? '' : $this->addTabs($tabsNb) );
			$this->output .= '</' . ( $eltName ) .  '>' . ($s->eolAfterCloseTag && !$isLast ? $this->eol : '');
			//$this->output .= '</' . ( !empty($parentName) ? $this->singular($parentName) : $eltName ) .  '>' . ($s->eolAfterCloseTag && !$isLast ? $this->eol : '');
		}
	}
	
	
	public function handleValue($val)
	{
		$disp = '';
		
		if 		( is_bool($val) )					{ $disp = $val ? 'true' : 'false'; }
		else if ( is_null($val) )					{ $disp = 'null'; }
		else if ( is_int($val) )					{ $disp = (int) $val; }
		else if ( is_array($val) && empty($val) )	{ $disp = ''; }
		else 										{ $disp = $val; }
		
		return $disp;
	}
	
	
	public function addTabs($howMany = 0)
	{
		$tabs = '';
		
		for ($i=0; $i<$howMany; $i++){ $tabs .= $this->tab; }
		
		return $tabs;
	}
	
	public function isValidElement($name)
	{
//$this->dump('isValidElement:' . $name);
//$this->dump(preg_match('/^[a-zA-Z_][a-zA-Z_0-9]*$/', $name));
		return preg_match('/^[a-zA-Z_][a-zA-Z_0-9]*$/', $name);
	}
	
	
	public function process2($data, $params = array())
	{
//var_dump(__METHOD__);
		
		$p = array_merge(array(
			'eol' 			=> PHP_EOL,
			'tab' 			=> "\t",
			'depth' 		=> 0,
			'element' 		=> 'root',
			'inline' 		=> false,
			'doctype' 		=> false,
			'childrenOnly' 	=> false,
		), $params);
		$p = array_merge($p, array(
			'eol' 			=> !$p['inline'] ? $p['eol'] : '',
			'tab' 			=> !$p['inline'] ? $p['tab'] : '',
		));
		$indent = '';
		if ( $p['tab'] ){ for ($i=0; $i<=$p['depth']; $i++){ $indent .= $p['tab']; } }
		
		$str = '';

//var_dump('params');		
//var_dump($p);
		
//var_dump($p);
//var_dump('data BEFORE attributes handling');
//var_dump($data);
		
		$attrStr = '';
		$attrs = is_array($data) && !empty($data['@attributes']) ? $data['@attributes'] : ( is_object($data) && !empty($data->{'@attributes'}) ? $data->{'@attributes'} : array() );
		//$attrs = is_array($data) ? $data['@attributes'] : ( is_object($data) ? $data->{'@attributes'} : array() );
		foreach($attrs as $name => $value){ $attrStr .= ' ' . $name . '="' . $value . '"'; }
		if ( is_array($data) ) { unset($data['@attributes']); }
		if ( is_object($data) ) { unset($data->{'@attributes'}); }
		
//var_dump('data AFTER attributes handling');
//var_dump($data);

		if ( $p['depth'] === 0 && $p['doctype'] !== false )
		{
			$str = '<?xml version="1.0" encoding="UTF-8" ?>' . $p['eol'];
		}
		
		if ( !$p['childrenOnly'] )
		{
			$str .= '<' . $p['element'] . $attrStr . '>';	
		}
		
		foreach (array_keys((array) $data) as $k)
		{
			$v 		= is_array($data) ? $data[$k] : ( is_object($data) ? $data->$k : null );
			$name 	= is_numeric($k) ? Tools::singular($p['element']) : $k;
			$name 	= $name === $p['element'] ? $name . 'Item' : $name;
			
			if ( is_array($v) || is_object($v) )
			{				
				//$str .='<' . $name . '>';
				//$str .= $p['eol'];
				//$str .= $indent;
				
				$content = $this->process2($v, array_merge($p, array('element' => $k, 'depth' => $p['depth']+1, 'childrenOnly' => false)));
				
				//$str .= $content;
				$str .= !empty($content) ? $p['eol'] . $indent . $content . $p['eol'] : '';
				//$str .= '</' . $name . '>';
			}
			else
			{
				$str .= $p['eol'];
				$str .= $indent;
				$str .= '<' . $name .'>';
				$str .= $v;
				$str .= '</' . $name . '>';
			}
		}

		//$str .= $p['depth'] !== 0 && !empty($str) ? $p['eol'] : '';
		
		if ( !$p['childrenOnly'] )
		{
			$str .= '</' . $p['element'] .'>';	
		}
		
		return $str;
	}

    static function xmlToArray($xml, $options = array())
    {
//var_dump(__METHOD__);
		
        $o = array_merge(array(
            'type' => 'xml',
            'parent' => null,
        ), $options);
		
        //$nodes  = $xml instanceof SimpleXMLElement ? $xml : ( is_file($xml) ? simplexml_load_file($xml) : simplexml_load_string($xml) );
        $nodes  = $xml instanceof SimpleXMLElement 
        			? $xml 
					: ( is_file($xml) 
						? simplexml_load_file($xml, 'SimpleXMLElement', LIBXML_COMPACT) 
						: simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_COMPACT)
					);
					
        $data 	= array();
		
		if ( !$nodes ){ return $data; }
		
//var_dump($nodes);

//if ( !$xml instanceof SimpleXMLElement ){ var_dump('nodes count: ' . $nodes->count()); }
	
		// If the element has attributes, get them
		if ( ($attrs = (array) $nodes->attributes()) && $attrs && isset($attrs['@attributes']) ){ $data['@attributes'] = $attrs['@attributes']; }
		
        foreach ($nodes as $node)
        {
			// Get current node Name
			$nodeName 	= $node->getName();
			
//var_dump($nodeName);
			
			// has attributes?
			//$nodeAttrs 		= (array) $node->attributes();
			//$hasAttrs 		= count($nodeAttrs) && isset($nodeAttrs['@attributes']);
			
			// has Children?
			//$childrenCount 	= $node->count();
			//$hasChidren 	= $childrenCount;
			
			// If the key already exists, it means that we may be facing a collection of items of the same tag  
			// we have to wrap the value into an indexed array before adding another item to the collection 
			if ( isset($data[$nodeName]) && !isset($data[$nodeName][0]) )
			{
//var_dump('creating collection array for: ' . $nodeName);
				$data[$nodeName] 	= array($data[$nodeName]);
				$data[$nodeName][] 	= $node instanceof SimpleXMLElement ? self::xmlToArray($node, $o) : $node;
//var_dump($data[$nodeName]);
			}
			// If the key already exists, and the value is an indexed array
			// assume we are facing another item of an the collection and just add it to the array
			elseif ( isset($data[$nodeName]) && isset($data[$nodeName][0]) )
			{
//var_dump('inserting new collection item in: ' . $nodeName);
				$data[$nodeName][] 	= $node instanceof SimpleXMLElement ? self::xmlToArray($node, $o) : $node;
			}
			// Otherwise, stay on a simple key => value mode
			else
			{
				$data[$nodeName] 	= $node instanceof SimpleXMLElement ? self::xmlToArray($node, $o) : $node;
			}
			
			// Handle possible text node, casting the node itsef into a string
			$textNode = trim((string) $node);
			if ( $textNode ) { $data[$nodeName]['text'] = $textNode; }
        }

		// If the element has a text node, casting the node itsef into a string
		$textNode = trim((string) $nodes);
		if ( $textNode ) { $data['text'] = $textNode; }
		
		// Force freeing memory
		unset($nodes, $node, $attrs, $textNode);
        
        return $data;
    }
}

?>