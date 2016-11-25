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
		$config = isset($this->indexable) ? $this->indexable : array_keys($this->attributes);
		$fields = [];
		foreach ($config as $key => $conf) {
			if (is_numeric($key)) {
				$field = false;
			}
			else {
				$field = $key;
			}
			if (is_string($conf)) {
				$name = $field !== false ? $field : $conf;
				$conf = ['multi' => false, 'field' => $conf];
				$fields[$name] = $conf;
			}
			else {
				$name = $field !== false ? $field : $conf['field'];
				$fields[$name] = $conf;
			}
		}
		return $fields;
	}

	public function createIndexDocument() {
		$fields = $this->getIndexFields();
		$doc = [];
		foreach ($fields as $key => $conf) {
			$name = isset($conf['field']) ? $conf['field'] : $key;
			
			$doc[$name] = $this->parseValueConfig($key, $conf);
		}

		return $doc;
	}

	function parseValueConfig($key, $conf) {
		if (isset($conf['multi']) && $conf['multi']) {
			$value = $this->{$key};
			if ($value instanceof \Illuminate\Database\Eloquent\Collection) {
				$values = [];
				$fields = isset($conf['fields']) ? $conf['fields'] : false;
				foreach ($value as $val) {
					if ($fields === false || is_array($fields)) {
						$row=[];
						$keys= $fields===false ? array_keys($val): $fields;
						foreach($keys as $key){
							$row[$key] = $val->{$key};
						}
						$values[] = $row;
					}
					else {
						if(is_string($fields)){
							$values[] = $val->{$fields};
						}
					}
				}
				return $values;
			}
		}
		else{
			return $this->{$key};
		}
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
		static::saved(function($model) {
			$model->addToIndex();
			 
		});

		static::updated(function($model) {
			$model->addToIndex();
		});
		static::addGlobalScope(new ElasticityScope());
	}

	function addToIndex($id = null) {

		$body = $this->createIndexDocument();
 
		$params = [
			'index' => $this->getIndexName(),
			'type' => $this->getIndexType(),
		];
		if (isset($body['id'])) {
			$params['id'] = $body['id'];
			unset($body['id']);
		}
		if ($id) {
			$params['id'] = $id;
		}
		$params['body'] = $body;

		$client = ClientBuilder::create()->build();
		$response = $client->index($params);
	}

}
