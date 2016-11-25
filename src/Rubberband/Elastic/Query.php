<?php

namespace Rubberband\Elastic;

class Query {

	protected $match = [];
	protected $order = [];
	protected $term = [];
	protected $parts = [];
	protected $maxsize = 10000;
	protected $limit;
	protected $index;
	protected $type;
	protected $partStack = [];
	protected $aggregations = [];

	public function __construct($client) {
		$this->client = $client;
	}

	function addAggregation($agg) {
		$this->aggregations[] = $agg;
		return $this;
	}

	function limit($n) {
		$this->limit = $n;
		return $this;
	}

	function filter($cb) {

		$filter = new Query\QueryPart('filter');
		$part = $this->getStack();
		$this->partStack[] = $filter;
		$cb($this);
		if ($part) {
			$part->addChild($filter);
		}
		else {
			$this->parts[] = $filter;
		}
		array_pop($this->partStack);
		return $this;
	}

	function must($cb) {

		$current = new Query\QueryPart('must');
		$part = $this->getStack();
		$this->partStack[] = $current;
		$cb($this);
		if ($part) {
			$part->addChild($current);
		}
		else {
			$this->parts[] = $current;
		}
		array_pop($this->partStack);
		return $this;
	}

	function not($cb) {

		$not = new Query\QueryPart('not');
		$part = $this->getStack();
		$this->partStack[] = $not;
		$cb($this);
		if ($part) {
			$part->addChild($not);
		}
		else {
			$this->parts[] = $not;
		}
		array_pop($this->partStack);
		return $this;
	}

	function bool($cb) {

		$part = new Query\QueryPart('bool');
		$current = $this->getStack();
		$this->partStack[] = $part;
		$cb($this);
		if ($current) {
			$current->addChild($part);
		}
		else {
			$this->parts[] = $part;
		}
		array_pop($this->partStack);
		return $this;
	}

	function range($field, $min, $max) {
		$part = $this->getStack();
		if ($part) {
			$part->addChild(['type' => 'range', 'field' => $field, 'min' => $min, 'max' => $max]);
		}
		else {
			$this->parts[] = ['type' => 'range', 'field' => $field, 'min' => $min, 'max' => $max];
		}
		return $this;
	}

	function getStack() {
		$cnt = count($this->partStack);
		if ($cnt > 0) {
			return $this->partStack[$cnt - 1];
		}
	}

	function execute() {
		$params = $this->buildParams();
				
		return $this->getClient()->search($params);
	}

	function first() {
		$results = $this->execute();
		if (isset($results['hits']['hits'][0])) {
			return new Hit($results['hits']['hits'][0]);
		}
		return null;
	}

	/**
	 * 
	 * @return Result
	 */
	function get() {
		$results = $this->execute();
		$collection = $this->newCollection($results);
		return $this->prepareCollection($collection);
	}

	function newCollection($results) {
		return new Result($results);
	}

	function prepareCollection($collection) {
		foreach ($this->aggregations as $agg) {
			$collection->setAggregation($agg->name(), $agg->getType());
		}
		return $collection;
	}

	function save($hit) {
		
	}

	function getClient() {
		return $this->client;
	}

	function from($from) {

		$ref = explode('.', $from);
		if (isset($ref[0])) {
			$this->index = $ref[0];
		}
		if (isset($ref[1])) {
			$this->type = $ref[1];
		}
		return $this;
	}

	function match($field, $value) {
		$part = $this->getStack();
		if ($part) {
			$part->addChild(['type' => 'match', 'field' => $field, 'value' => $value]);
		}
		else {
			$this->parts[] = ['type' => 'match', 'field' => $field, 'value' => $value];
		}
		return $this;
	}

	function term($field, $value) {
		$part = $this->getStack();
		if ($part) {
			$part->addChild(['type' => 'term', 'field' => $field, 'value' => $value]);
		}
		else {
			$this->parts[] = ['type' => 'term', 'field' => $field, 'value' => $value];
		}
		return $this;
	}

	function buildParams() {

		$params = [
			'index' => $this->getIndexName(),
		];
		if ($this->type) {
			$params['type'] = $this->getTypeName();
		}
				
		$params['body'] = [
		];
		if (count($this->parts) > 0) {
			$params['body']['query'] = [];
		}
		$params['size'] = $this->limit !== null ? $this->limit : $this->maxsize;
		foreach ($this->parts as $part) {
			if ($part instanceof Query\QueryPart) {
				$params['body']['query'][$part->key()] = $this->buildPart($part);
			}
			else {
				$params['body']['query'] = $this->buildPartType($part, $params['body']['query']);
			}
		}

		if (count($this->aggregations) > 0) {
			$params['body']['aggs'] = [];
			foreach ($this->aggregations as $aggregation) {

				$aggregation->make($params['body']['aggs']);
			}
		}

		if (count($this->order) > 0) {
			$params['body']['sort'] = [];
			foreach ($this->order as $o) {
				$params['body']['sort'] = [$o['field'] => $o['order']];
			}
		}
		return $params;
	}

	function getIndexName() {
		return $this->index;
	}

	function getTypeName() {
		return $this->type;
	}

	function buildPart($part) {
		$query = [];
		foreach ($part->parts() as $child) {
			if ($child instanceof Query\QueryPart) {
				$query[$child->key()] = $this->buildPart($child);
			}
			else {
				$query = $this->buildPartType($child, $query);
			}
		}
		return $query;
	}

	function buildPartType($child, $query) {
		if (isset($child['type'])) {
			switch ($child['type']) {
				case 'range':
					if (!isset($query['range'])) {
						$query['range'] = [];
					}
					$range = [];
					if ($child['min']) {
						$range['from'] = $child['min'];
					}
					if ($child['max']) {
						$range['to'] = $child['max'];
					}
					$query['range'][
						$child['field']] = $range;

					break;
				case 'term':
					if (!isset($query['term'])) {
						$query['term'] = [];
					}
					$query['term'][$child['field']] = $child['value']
					;
					break;
				case 'match':
					if (!isset($query['match'])) {
						$query['match'] = [];
					}
					$query['match'][$child['field']] = $child['value']
					;
					break;
			}
		}
		return $query;
	}

	protected function buildMustParams() {
		$params = [];
		if (count($this->term) > 0) {
			$params['term'] = $this->buildTermParams();
		}
		if (count($this->match) > 0) {
			$params['match'] = $this->buildMatchParams();
		}
		return $params;
	}

	function buildTermParams() {
		$terms = [];
		foreach ($this->term as $term) {
			$terms[$term['field']] = $term['value'];
		}
		return $terms;
	}

	function buildMatchParams() {
		$matches = [];
		foreach ($this->match as $match) {
			$matches[$match['field']] = $match['value'];
		}
		return $matches;
	}

	function orderBy($field, $order = 'asc') {
		$this->order[] = ['field' => $field, 'order' => $order];
		return $this;
	}

}
