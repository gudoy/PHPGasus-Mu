<?php

namespace PHPGasus\converters\xml;

class XML2PHP
{
    static function parse($xml, $options = array())
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