<?php

namespace Rubberband\Elastic\Laravel\Eloquent;

use Elasticsearch\ClientBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ScopeInterface;

class ElasticityScope implements ScopeInterface {

	protected $extensions = ['search'];
	protected $aggs = [];
	protected $maxsize = 10000;

	public function apply(Builder $builder, Model $model) {
		$builder->macro('agg', function($builder, $field, $int, $name) {
			$this->aggs[$field] = ['n' => $name, 'int' => $int];
			return $builder;
		});
		$builder->macro('addAggregation', function($builder, $a) {
			$this->aggs[] = $a;
			return $builder;
		});
		$builder->macro('search', function($builder) {
			$model = $builder->getModel();
			$params = [
				'index' => $model->getIndexName(),
				'type' => $model->getIndexType(),
			];
			
			$query = [
				'bool' => [
					'must' => [
					],
				]
			];
			
			$fields = $model->getIndexFields();
			$fieldnames = [];
			foreach ($fields as $key => $conf) {
				$name = isset($conf['field']) ? $conf['field'] : $key;
				$fieldnames[$key] = $name;
			}
			$wheres = (array) $builder->getQuery()->wheres;
			foreach ($wheres as $where) {
				if ($where['type'] == 'Basic') {
					$op = $where['operator'];
					switch (strtolower($op)) {

						case '>':
							if (!isset($query['bool']['must']['range'])) {
								$query['bool']['must']['range'] = [];
							}
							$field = $fieldnames[$where['column']];
							$query['bool']['must']['range'][
								$field] = ['from' => $where['value']];

							break;
						case '=':

							$field = isset($fieldnames[$where['column']]) ? $fieldnames[$where['column']] : $where['column'];
							$query['bool']['must']['term'] = [
								$field => $where['value']
							];
							break;
						case 'like':

							$field = isset($fieldnames[$where['column']]) ? $fieldnames[$where['column']] : $where['column'];
							$query['bool']['must']['match'] = [
								$field => str_replace('%', '', $where['value'])
							];
							break;
					}
				}
			}
			$params['body'] = [
				'query' => $query
			];
			echo '<pre>';
			print_r($params);
			echo '</pre>';
			if (count($this->aggs) > 0) {
				$params['body']['aggs'] = [];
				foreach ($this->aggs as $f => $agg) {
					if (is_object($agg)) {
						$agg->make($params['body']['aggs']);
					}
				}
				$params['size'] = 0;
			}
			else {
				if (isset($builder->limit)) {
					$params['size'] = $builder->limit;
				}
				else {
					$params['size'] = $this->maxsize;
				}
			}


			$client = ClientBuilder::create()->build();
			$response = $client->search($params);
			return $this->parseResponse($response);
		});
	}

	function parseResponse($res) {
		return new \Rubberband\Elastic\Result($res);
	}

	public function extend(Builder $builder) {

		$builder->macro('agg', function($builder, $field, $int, $name) {
			$this->aggs[$field] = ['n' => $name, 'int' => $int];
			return $builder;
		});
		$builder->macro('addAggregation', function($builder, $a) {
			$this->aggs[] = $a;
			return $builder;
		});
		$builder->macro('search', function($builder) {
			$model = $builder->getModel();
			$params = [
				'index' => $model->getIndexName(),
				'type' => $model->getIndexType(),
			];
			$query = [
				'bool' => [
					'must' => [
					],
				]
			];
			$fields = $model->getIndexFields();
			$fieldnames = [];
			foreach ($fields as $i => $name) {
				if (is_numeric($i)) {
					$fieldnames[$name] = $name;
				}
				else {
					$fieldnames[$i] = $name;
				}
			}
			$wheres = (array) $builder->getQuery()->wheres;
			foreach ($wheres as $where) {
				if ($where['type'] == 'Basic') {
					$op = $where['operator'];
					switch ($op) {

						case '>':
							if (!isset($query['bool']['must']['range'])) {
								$query['bool']['must']['range'] = [];
							}
							$field = $fieldnames[$where['column']];
							$query['bool']['must']['range'][
								$field] = ['from' => $where['value']];

							break;
						case '=':
							$field = $fieldnames[$where['column']];
							$query['bool']['must']['term'] = [
								$field => $where['value']
							];
							break;
					}
				}
			}
			$params['body'] = [
				//	'query'=> $query
			];
			if (count($this->aggs) > 0) {
				$params['body']['aggs'] = [];
				foreach ($this->aggs as $f => $agg) {
					if (is_object($agg)) {
						$agg->make($params['body']['aggs']);
					}
				}
				$params['size'] = 0;
			}
			else {
				if ($builder->limit) {
					$params['size'] = $builder->limit;
				}
				else {
					$params['size'] = $this->maxsize;
				}
			}


			$client = ClientBuilder::create()->build();
			$response = $client->search($params);
			return $this->parseResponse($response);
		});
	}

	public function remove(Builder $builder, Model $model) {
		
	}

}
