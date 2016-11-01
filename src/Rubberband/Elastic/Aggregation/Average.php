<?php

namespace Rubberband\Elastic\Aggregation;

class DateHistogram extends \Rubberband\Elastic\Aggregation
{
	public function make(&$params) {
		$name=$this->name;
		$cur = isset($params[$name])?$params[$name]:[];
		if(!isset($cur['avg'])){
			$cur['avg']=[
				'field'=>$this->field,
			];
		}
		$params[$name]=$cur;
	}

}
