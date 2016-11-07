<?php
namespace Rubberband\Elastic;

class AggregationHit 
{
	protected $data;
	public function __construct($hit) {
		$this->data=$hit;
	}
	
	function key(){
		return $this->data['key'];
	}
	function stringKey(){
		return isset($this->data['key_as_string']) ? $this->data['key_as_string'] : null;
	}
	
	function count(){
		return $this->data['doc_count'];
	}

	function value(){
		if(isset($this->data['value'])){
			return $this->data['value'];
		}
		elseif(isset($this->data['doc_count'])){
			return $this->data['doc_count'];
		}
		
	}
	
	function aggregation($k){
		$key=$this->getAggregationKey($k);
		$data=isset($this->data[$key])?$this->data[$key]:[];
		if(isset($data['buckets'])){
			return new AggregationCollection($data);
		}
		if(isset($data['value'])){
			return new AggregationHit($data);
		}
		return $data;
	}	
	
	function aggregations(){
		$res=[];
		foreach($this->data as $k => $v){
			if(substr($k,0,2)=='__'){
				$res[]= new AggregationCollection($this->data[$k]);
			}
		}
		return $res;
	}
	
	protected function getAggregationKey($k){
		return '__'.$k;
	}
	
}
