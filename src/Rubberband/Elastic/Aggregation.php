<?php
namespace Rubberband\Elastic;


abstract class Aggregation{
	
	protected $aggregations = [];
	protected $options = [];
	protected $field;
	protected $name;
	protected $bucket = false;
	
	public function __construct($name, $field, $options = []) {
		$this->name = $name;
		$this->options = $options; 
		$this->field = $field;
	}
	
	function isBucket(){
		return $this->bucket;
	}
	
	function addAggregation($aggregation){
		$this->aggregations[] = $aggregation;
		return $this;
	}
	
	abstract function make(&$params);
}