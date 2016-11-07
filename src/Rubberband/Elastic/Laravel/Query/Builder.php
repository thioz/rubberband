<?php

namespace Rubberband\Elastic\Laravel\Query;
use Illuminate\Support\Traits\Macroable;

class Builder extends \Rubberband\Elastic\Query {

	use Macroable {
		__call as macroCall;
	}
	
	/**
	 *
	 * @var \Rubberband\Elastic\Laravel\Database\Connection
	 */
	protected $connection;


	public function __construct($connection) {
		$this->connection = $connection;
	}
	
	function getIndexName() {
			
		return $this->connection->getDatabaseName();
	}
	
	function where($column, $op, $value){
		switch($op){
			case '=':
				$this->term($column,$value);
				break;
			case '>':
				$this->range($column,$value,null);
				break;
			case '<':
				$this->range($column,null,$value);
				break;
		}
		return $this;
	}
	
	function table($table) {
		$this->type = $table;
		return $this;
	}
	
	public function newCollection($results) {
		return new \Rubberband\Elastic\Laravel\ResultCollection($results);
	}
	
	public function getClient() {
		return $this->connection->getPdo();
	}
	
	function delete(){
		echo '<pre>';
		print_r('dessl');
		echo '</pre>';
	}
}
