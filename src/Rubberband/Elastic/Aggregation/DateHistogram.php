<?php

namespace Rubberband\Elastic\Aggregation;

class DateHistogram extends \Rubberband\Elastic\Aggregation\BaseAggregation {

	protected $bucket = true;

	public function make(&$params) {

		$name = $this->keyname();
		$cur = isset($params[$name]) ? $params[$name] : [];
		if (!isset($cur['date_histogram'])) {
			$opts = [
				'field' => $this->field,
				'interval' => $this->options['interval'],
			];
			if (isset($this->options['format'])) {
				$opts['format'] = $this->options['format'];
			}
			$cur['date_histogram'] = $opts;
	 

			if (count($this->aggregations) > 0) {
				$cur['aggs'] = [];
				foreach ($this->aggregations as $agg) {
					$agg->make($cur['aggs']);
				}
			}
		}
		$params[$name] = $cur;
	}

}
