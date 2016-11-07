<?php
namespace Rubberband\Elastic\Laravel;

use Illuminate\Support\ServiceProvider;

class RubberbandServiceProvider extends ServiceProvider{
	public function register() {
		
		$this->registerDatabaseConnection();
		
	}
	
	protected function registerDatabaseConnection(){
		$this->app->bind('db.connector.rubberband', function($app){
			return new Database\ElasticConnector();
		});
		
		$this->app->bind('db.connection.rubberband', function($app,$args){
			return new Database\Connection($args[0],$args[1]);
		});
	}

}

