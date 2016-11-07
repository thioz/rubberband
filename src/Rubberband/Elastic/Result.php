<?php

namespace Rubberband\Elastic;

class Result {

	protected $result = [];
	protected $aggregations = [];
	protected $query;

	public function __construct($result) {
		$this->result = $result;
	}

	function aggregations() {
		return isset($this->result['aggregations']) ? $this->result['aggregations'] : [];
	}

	function setAggregations($aggs) {
		foreach ($aggs as $k => $agg) {
			$this->aggregations[$k] = $agg;
		}
	}

	function setAggregation($k, $type) {
		$this->aggregations[$k] = ['type' => $type];
	}

	/**
	 * 
	 * @param type $k
	 * @return AggregationCollection
	 */
	function aggregation($name) {
		$key = $this->getAggregationKey($name);
		$conf = $this->aggregations[$name];
		$agg = isset($this->result['aggregations'][$key]) ? new AggregationCollection($this->result['aggregations'][$key], $conf['type']) : [];

		return $agg;
	}

	protected function getAggregationKey($k) {
		return '__' . $k;
	}

	function hits() {
		return isset($this->result['hits']) ? $this->formatHits() : [];
	}

	function took() {
		return $this->result['took'];
	}

	function formatHits() {
		$docs = [];
		foreach ($this->result['hits']['hits'] as $hit) {

			$docs[] = new Hit($hit, $this);
		}
		return $docs;
	}

	function formatAggregation($key) {
		$aggs = $this->result['aggregations'][$key];
		if (isset($aggs['buckets'])) {
			return $aggs['buckets'];
		}
		if (isset($aggs['value'])) {
			return $aggs['value'];
		}
		return $aggs;
	}

}
