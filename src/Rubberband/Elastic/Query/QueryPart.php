<?php
namespace Rubberband\Elastic\Query;

use Rubberband\Elastic\Query;

class QueryPart 
{
	protected $key;
	protected $parts=[];
	
	function __construct($key)
	{
		$this->key=$key;
	}
	
	function addChild($child){
		$this->parts[] = $child;
	}
	
	function key(){
		return $this->key;
	}
	
	function parts(){
		return $this->parts;
	}
	
	function build(){
		$q = [];
		foreach($this->parts as $part){
			$q[$part->key()] = $part->build();
		}
		return $q;
	}
	
	
}