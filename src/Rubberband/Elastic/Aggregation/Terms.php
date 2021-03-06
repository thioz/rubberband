<?php

namespace Rubberband\Elastic\Aggregation;

class Terms extends \Rubberband\Elastic\Aggregation\BaseAggregation
{
	protected $bucket = true;
	public function make(&$params) {
		$name=$this->keyname();
		$cur = isset($params[$name])?$params[$name]:[];
		if(!isset($cur['terms'])){
			$cur['terms']=[
				'field'=>$this->field,
			 
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
