<?php
namespace Rubberband\Elastic\Laravel\Database;

class Connection extends \Illuminate\Database\Connection
{
	protected $client;
	public function __construct($pdo, $database = '', $tablePrefix = '', array $config = array()) {
		parent::__construct($pdo, $database, $tablePrefix, $config);
	}

    /**
     * Get a new query builder instance.
     *
     * @return \Rubberband\Elastic\Laravel\Query\Builder
     */
    public function query()
    {
        return new \Rubberband\Elastic\Laravel\Query\Builder($this);
    }	
		
    public function statement($query, $bindings = [])
    {
	 
        return $this->run($query, $bindings, function ($me, $query, $bindings) {
            if ($me->pretending()) {
                return true;
            }

            $bindings = $me->prepareBindings($bindings);

            return $me->getPdo()->prepare($query)->execute($bindings);
        });
    }	
	
}