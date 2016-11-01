<?php

namespace Rubberband\Elastic\Aggregation;

class DateHistogram extends \Rubberband\Elastic\Aggregation
{
	protected $bucket = true;
	
	public function make(&$params) {
 
		$name=$this->name;
		$cur = isset($params[$name])?$params[$name]:[];
		if(!isset($cur['date_histogram'])){
			$cur['date_histogram']=[
				'field'=>$this->field,
				'interval'=>$this->options['interval'],
			];
			
			if(count($this->aggregations)>0){
				$cur['aggs']=[];
				foreach($this->aggregations as $agg){
					$agg->make($cur['aggs']);
				}
			}
		}
		$params[$name]=$cur;
		
	}

}
