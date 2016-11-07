<?php

namespace Rubberband\Elastic\Laravel\Database;

use Elasticsearch\ClientBuilder;
use Illuminate\Database\Connectors\Connector;

class ElasticConnector extends Connector {
	
	public function connect(array $config) {
		$host = isset($config['host']) ? $config['host'] : 'localhost';
		$port = isset($config['port']) ? $config['port'] : 9200;
		return $this->createConnection('', $config, []);
	}
	
	
	public function createConnection($dsn, array $config, array $options){
		$client = ClientBuilder::create()->build();
		return $client;
		
	}
	

}
