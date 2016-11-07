<?php

namespace Rubberband\Elastic\Aggregation;

class Sum extends \Rubberband\Elastic\Aggregation\BaseAggregation
{
	protected $bucket=false;
	public function make(&$params) {
		$name=$this->keyname();
		$cur = isset($params[$name])?$params[$name]:[];
		if(!isset($cur['sum'])){
			$cur['sum']=[
				'field'=>$this->field,
			];
		}
		$params[$name]=$cur;
	}

}
