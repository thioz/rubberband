<?php

namespace Rubberband\Elastic\Aggregation;

class Average extends \Rubberband\Elastic\Aggregation\BaseAggregation
{
	protected $bucket=false;
	public function make(&$params) {
		$name=$this->keyname();
		$cur = isset($params[$name])?$params[$name]:[];
		if(!isset($cur['avg'])){
			$cur['avg']=[
				'field'=>$this->field,
			];
		}
		$params[$name]=$cur;
	}

}
