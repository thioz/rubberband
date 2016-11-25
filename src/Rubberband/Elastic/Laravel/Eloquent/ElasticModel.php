<?php

namespace Rubberband\Elastic\Laravel\Eloquent;

use DateTime;
use Elasticsearch\ClientBuilder;

trait ElasticModel {

	protected $_elasticId;

	function setElasticId($id) {
		$this->_elasticId = $id;
	}

	function getElasticId() {
		return $this->_elasticId;
	}

	public function save(array $options = []) {

		 
		$keyname = $this->getKeyName();

		$fields = isset($this->indexable) ? $this->indexable : array_keys($this->attributes);
		$doc = [];
		foreach ($fields as $i => $key) {
			$name = $key;
			if (!is_numeric($i)) {
				$name = $key;
				$key = $i;
			}
			if(isset($this->attributes[$key])){
				$value = $this->attributes[$key];
				if ($value instanceof DateTime) {
					$value = $value->format('Y-m-d');
				}
				$doc[$name] = $value;
			}
		}
		
		$params = [
				'index' =>$this->indexname,
				'type' => $this->indextype,
		];
		
		if($this->exists){
			if(isset($doc[$keyname])){
				$params['id']=$doc[$keyname];
				unset($doc[$keyname]);
			}
		}
		$params['body'] = $doc;
	 
		$client = ClientBuilder::create()->build();
	 	$response = $client->index($params);
		if (!$this->exists) {
			$this->attributes[$keyname] = $response['_id'];
			$this->exists=true;
		}
		
	}

	function delete() {
		$params = [
				'index' =>$this->indexname,
				'type' => $this->indextype,
			'id' => $this->getElasticId()
		];

		$client = ClientBuilder::create()->build();
		$res = $client->delete($params);
	}
	public function getIndexName() {
		return  $this->indexname ;
	}

	public function getIndexType() {
		return $this->indextype ;
	}

	public function newEloquentBuilder($query) {
		return new QueryBuilder($query);
	}

	public function fromDateTime($value) {
		return date('Y-m-d', strtotime($value));
	}

	protected function updateTimestamps() {
		$time = $this->freshTimestamp();

		if (!$this->isDirty(static::UPDATED_AT)) {
			$this->setUpdatedAt($time);
		}

		if (!$this->exists && !$this->isDirty(static::CREATED_AT)) {
			$this->setCreatedAt(date('Y-m-d'));
		}
	}

	/**
	 * Get a new query builder instance for the connection.
	 *
	 * @return \Illuminate\Database\Query\Builder
	 */
	protected function newBaseQueryBuilder() {
		$conn = $this->getConnection();

		return new \Rubberband\Elastic\Laravel\Query\Builder($conn);
	}

}
