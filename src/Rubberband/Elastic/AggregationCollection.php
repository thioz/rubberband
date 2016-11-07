<?php

namespace Rubberband\Elastic;

class AggregationCollection implements \Iterator, \ArrayAccess {

	protected $data = [];
	protected $type;
	protected $i = 0;
	protected $hasBuckets = false;

	public function __construct($data, $type = null) {
		if (!$type) {
			if (isset($data['buckets'])) {
				$type = 'bucket';
			}
			else {
				$type = 'value';
			}
		}
		$this->type = $type;

		$this->data = $type == 'bucket' ? $data['buckets'] : $data;
	}

	function each($cb) {
		$res = [];
		foreach ($this->data as $row) {
			$res[] = call_user_func($cb, new AggregationHit($row));
		}
		return $res;
	}

	public function current() {
		return new AggregationHit($this->get($this->i));
	}

	public function key() {
		return $this->i;
	}

	protected function get($i) {
		$this->data[$i];
	}

	public function next() {
		$this->i++;
	}

	public function rewind() {
		$this->i = 0;
	}

	public function valid() {
		return isset($this->data[$this->i]);
	}

	function flatten() {
		$rows = $this->data;
		return $this->flattenRows($rows);
	}

	function flattenRow($row, $akeys) {
		if (count($akeys) == 0) {
			return $row['doc_count'];
		}
		else {
			$flat = [
				'doc_count' => $row['doc_count']
			];

			foreach ($akeys as $akey) {
				$name = substr($akey, 2);
				$subrow = $row[$akey];
				$flat[$name] = isset($subrow['value']) ? $subrow['value'] : $this->flattenRows($subrow['buckets']);
			}
		}
		return $flat;
	}

	function flattenRows($rows) {
		$flat = [];
		$hasAggregations = false;
		foreach ($rows as $i => $row) {
			if ($i == 0) {
				$akeys = $this->getAggregationKeys($row);
				if (count($akeys) > 0) {
					$hasAggregations = true;
				}
			}

			$k = isset($row['key_as_string']) ? $row['key_as_string'] : $row['key'];
			$flat[$k] = $this->flattenRow($row, $akeys);
		}
		return $flat;
	}

	function getAggregationKeys($row) {
		return array_filter(array_keys($row), function($k) {
			return substr($k, 0, 2) == '__';
		});
	}

	public function offsetExists($offset) {

		return isset($this->data[$offset]);
	}

	public function offsetGet($offset) {
		return new AggregationHit($this->data[$offset]);
	}

	public function offsetSet($offset, $value) {
		
	}

	public function offsetUnset($offset) {
		
	}

}
