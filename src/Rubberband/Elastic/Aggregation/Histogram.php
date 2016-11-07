<?php

namespace Rubberband\Elastic\Aggregation;

class DateHistogram extends \Rubberband\Elastic\Aggregation\BaseAggregation
{
	protected $bucket = true;
	public function make(&$params) {
		
		$name=$this->keyname();
		$cur = isset($params[$name])?$params[$name]:[];
		if(!isset($cur['histogram'])){
			$opts=[
				'field'=>$this->field,
				'interval'=>$this->options['interval'],
			];
			if(isset($this->options['format'])){
				$opts['format'] = $this->options['format'];
			}
			$cur['histogram']=$opts;
		}
		 
		$params[$name]=$cur;
		
	}

}
