<?php

namespace Rubberband\Elastic;

class Result{
	
	protected $result = [];
	protected $query;
	public function __construct($result)
	{
		$this->result = $result;
	}
	
	function aggregations(){
		return isset($this->result['aggregations']) ? $this->result['aggregations']:[];
	}

	function aggregation($k){
		return isset($this->result['aggregations'][$k]) ? $this->formatAggregation($k):[];
	}
	
	function hits(){
		return isset($this->result['hits']) ? $this->formatHits() : [];
	}
	
	function took(){
		return $this->result['took'];
	}
	
	function formatHits(){
		$docs = [];
		foreach($this->result['hits']['hits'] as $hit){
			
			$docs[]= new Hit($hit,$this);
		}
		return $docs;
	}
	
	function formatAggregation($key){
		$aggs=$this->result['aggregations'][$key];
		if(isset($aggs['buckets'])){
			return $aggs['buckets'];
		}
		if(isset($aggs['value'])){
			return $aggs['value'];
		}
		return $aggs;
	}
}
