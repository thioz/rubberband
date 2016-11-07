<?php

namespace Rubberband\Elastic\Laravel\Eloquent;

class QueryBuilder {

	/**
	 *
	 * @var \Rubberband\Elastic\Laravel\Query\Builder
	 */
	protected $builder;

	/**
	 *
	 * @var \Illuminate\Database\Eloquent\Model
	 */
	protected $model;

	public function __construct($builder) {
		$this->builder = $builder;
	}

	function setModel($model) {
		$this->model = $model;


		$this->builder->from($model->getIndexName() . '.' . $model->getIndexType());
		return $this;
	}
	
	function get(){
		return $this->builder->get();
	}

	function with($w) {
		return $this;
	}

	function find($id) {

		$q = $this->builder->where($this->model->getKeyName(), '=', $id);
		$hit = $q->limit(1)->first();
		if ($hit) {
			$c = get_class($this->model);
			$model = new $c();
			$model->setRawAttributes($hit->source());
			$model->setElasticId($hit->id());
			return $model;
		}
	}
	
 
	
	
	
	function where($c,$op,$val){
		return $this->builder->where($c, $op, $val);
	}

}
