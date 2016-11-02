<?php

namespace Rubberband\Elastic\Aggregation;

class Terms extends \Rubberband\Elastic\Aggregation
{
	protected $bucket = true;
	public function make(&$params) {
		$name=$this->name;
		$cur = isset($params[$name])?$params[$name]:[];
		if(!isset($cur['terms'])){
			$cur['terms']=[
				'field'=>$this->field,
			 
			];
		}
		$params[$name]=$cur;
		
	}

}
