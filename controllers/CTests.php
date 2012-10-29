<?php

//namespace PHPGasus\Controllers;

//Class CTests extends \PHPGasus\Controller 
Class CTests extends Controller
{
	public function index()
	{
var_dump(__METHOD__ . ' (NOT in test folder)');
		//$this->data = array('foo' => 'bar', 'foobar' => 42, 'bar' => false);
		
		$this->render();	
	}
	
	public function foo()
	{
var_dump(__METHOD__);	
		$this->data = 'foo';
		
		$this->render();
	}
	
	public function json()
	{
//var_dump(__METHOD__);
		
		$this->data = array('foo' => 'bar', 'foobar' => 42, 'bar' => false);
		
		$this->render();		
	}
	
	public function haarcascade()
	{
		ini_set('memory_limit', '256M');
		ini_set('max_execution_time', 5*60);
		
		//$filepath 	= _PATH . 'tests/xml/frontalEyes35x16.xml';
		//$filepath 	= _PATH . 'tests/xml/eyeright.xml';
		//$filepath 	= _PATH . 'tests/xml/eyeleft.xml';
		//$filepath 	= _PATH . 'tests/xml/eyes22x5.xml';
		//$filepath 	= _PATH . 'tests/xml/haarcascade_eye_tree_eyeglasses.xml';
		$filepath 	= _PATH . 'tests/xml/hand.xml';
		$name 		= str_replace('.xml', '', basename($filepath));

//var_dump($name);
//var_dump($filepath);
//var_dump(basename($filepath));
		
		$tmp 		= PHPGasus\converters\xml\XML2PHP::parse(file_get_contents($filepath));
		$rootNode 	= key($tmp);
		
//var_dump(key($tmp));
//var_dump($tmp);		
//var_dump(count($tmp));
//die();
		
		$sizes = explode(' ', trim($tmp[$rootNode]->size));
		
//var_dump($sizes);
		
//var_dump(count($tmp[$name]->stages->_));
		
		// Loop over stages
		$s = array();
		foreach ($tmp[$rootNode]->stages->_ as $stage)
		{
//var_dump($stage->stage_threshold);
//var_dump('stage:');
//var_dump($stage);
			
			// Loop over trees
			$t = array();
			foreach($stage->trees->_ as $tree)
			{
//var_dump('tree:');
//var_dump($tree);

				$feature = &$tree->_->feature;
//var_dump($feature);
//die();

				// Loop over rectanges
				$r = array();
				foreach ($feature->rects->_ as $rect)
				{
					$coords = explode(' ', trim($rect));
					$r[] = array(
						'x1' 	=> (int) $coords[0],
						'x2' 	=> (int) $coords[1], 
						'y1' 	=> (int) $coords[2],
						'y2' 	=> (int) $coords[3],
						'f' 	=> (float) $coords[4],
					);
				}

				
				$t[] = array(
					'feats' => array(
						array(
							'thres' 	=> (float) $tree->_->threshold,
							'has_l' 	=> isset($tree->_->left_val),
							'l_val' 	=> (float) $tree->_->left_val,
							'l_node' 	=> -1,
							'has_r' 	=> isset($tree->_->right_val),
							'r_val' 	=> (float) $tree->_->right_val,
							'r_node' 	=> -1,
							'rects' 	=> $r,
						), 
					),
				);
//var_dump($t);
//die();
				
//die();
			}
			
			$s[] = array(
				'thres' 	=> (float) $stage->stage_threshold,
				'trees' 	=> $t,
			);

//die();
//break;
			
			//$s['trees']['feats'] = $f;
			//$cascade['stages'][] = $s; 
		}
		
//var_dump($s);
//die();
//var_dump($tmp);
		$this->data = array(
			$name => array(
				'size1' => (int) $sizes[0],
				'size2' => (int) $sizes[1],
				'stages' => $s, 
			)
		);
		
		$this->response->fileBasename = $name;
		$this->render();
	}
}

?>