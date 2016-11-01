<?php

namespace Rubberband\Elastic\Aggregation;

class DateHistogram extends \Rubberband\Elastic\Aggregation
{
	protected $bucket = true;
	public function make(&$params) {
		$name=$this->name;
		$cur = isset($params[$name])?$params[$name]:[];
		if(!isset($cur['histogram'])){
			$cur['histogram']=[
				'field'=>$this->fields,
				'interval'=>$this->options['interval'],
			];
		}
		$params[$name]=$cur;
		
	}

}
