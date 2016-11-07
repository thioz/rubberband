<?php
namespace Rubberband\Elastic\Aggregation;


abstract class BaseAggregation{
	
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
	
	function getType(){
		return $this->bucket?'bucket':'value';
	}
	
	function addAggregation($aggregation){
		$this->aggregations[] = $aggregation;
		return $this;
	}
	
	function aggregationNames(){
		$names=[];
		foreach($this->aggregations as $agg){
			$names[]=$agg->name();
		}
	}
	
	function name(){
		return $this->name;
	}

	function keyname(){
		return '__'.$this->name;
	}

	function field(){
		return $this->field;
	}
	
	abstract function make(&$params);
}