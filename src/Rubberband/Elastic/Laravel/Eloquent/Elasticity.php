<?php

namespace Rubberband\Elastic\Laravel\Eloquent;

use DateTime;
use Elasticsearch\ClientBuilder;

trait Elasticity {
	public function getIndexName() {
		return isset($this->indexname) ? $this->indexname : $this->getConnectionName();
	}

	public function getIndexType() {
		return isset($this->indextype) ? $this->indextype : $this->createIndexTypeName();
	}
 
	function createIndexTypeName() {
		$class = get_class($this);
		$parts = explode('\\', strtolower($class));
		return array_pop($parts);
	}

	public function getIndexFields() {
		return isset($this->indexable) ? $this->indexable : array_keys($this->attributes);
		$fields=[];
		foreach($names as $k => $name){
			if(is_numeric($k)){
				$key=$name;
				$attr=$name;
			}
			else{
				$attr=$k;
				$key=$name;
			}
			$fields[$key] = $attr;
		}
		return $fields;
	}

	public function createIndexDocument() {
		$fields = $this->getIndexFields();
		$doc = [];
		foreach ($fields as $i => $key) {
			$name = $key;
			if (!is_numeric($i)) {
				$name = $key;
				$key = $i;
			}
			$doc[$name] = $this->formatIndexValue($this->{$key});
		}

		return $doc;
	}

	function formatIndexValue($v) {
		if ($v instanceof DateTime) {
			return $v->format('Y-m-d H:i:s');
		}
		return $v;
	}

	function index() {
		$body = $this->createIndexDocument();
		$params = [
			'index' => $this->getIndexName(),
			'type' => $this->getIndexType(),
		];
		if (isset($body['id'])) {
			$params['id'] = $body['id'];
			unset($body['id']);
		}
		$params['body'] = $body;
//			echo '<pre>';
//			print_r($params);
//			echo '</pre>';
		$client = ClientBuilder::create()->build();
		$response = $client->index($params);
	}

	public static function bootElasticity() {
		static::created(function($model) {

			$model->addToIndex();
		});
		static::addGlobalScope(new ElasticityScope());
	}

	function addToIndex($id=null) {
	 
		$body = $this->createIndexDocument();
		$params = [
			'index' => $this->getIndexName(),
			'type' => $this->getIndexType(),
		];
		if (isset($body['id'])) {
			$params['id'] = $body['id'];
			unset($body['id']);
		}
		if($id){
			$params['id']=$id;
		}
		$params['body'] = $body;

		$client = ClientBuilder::create()->build();
	 	$response = $client->index($params);
	
	}

}
