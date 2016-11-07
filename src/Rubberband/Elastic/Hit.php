<?php

namespace Rubberband\Elastic;

class Hit implements \ArrayAccess
{

	protected $hit = [];

	public function __construct($hit)
	{
		$this->hit = $hit;
	}

	function id()
	{
		return $this->hit['_id'];
	}

	function score()
	{
		return $this->hit['_score'];
	}

	function type()
	{
		return $this->hit['_type'];
	}
	
	function index()
	{
		return $this->hit['_index'];
	}

	function source()
	{
		return $this->hit['_source'];
	}

	function save($client)
	{
		$params = [
			'index' => $this->index(),
			'type' => $this->type(),
			'id' => $this->id(),
			'body' => $this->source(),
		];
		
 
		$client->index($params);
	}

	function __get($name)
	{
	 
		if(isset($this->hit['_source'][$name])){
			return $this->hit['_source'][$name];
		}
		if(method_exists($this, $name)){
			return call_user_func([$this,$name]);
		}
		return null;
	}

	public function __set($name, $value)
	{
		$this->_hit['_source'][$name] = $value;
	}

	public function offsetExists($offset)
	{
		return array_get($this->hit['_source'], $offset, false);
	}

	public function offsetGet($offset)
	{
		return array_get($this->hit['_source'], $offset, null);
	}

	public function offsetSet($offset, $value)
	{
		array_set($this->hit['_source'], $offset, $value);
	}

	public function offsetUnset($offset)
	{
		$parts = explode('.', $offset);
		$root = &$this->hit['_source'];

		foreach ($parts as $i => $part)
		{
			if ($i < count($parts) - 1)
			{
				if (!isset($root[$part]))
				{
					return;
				}
				$root = &$root[$part];
			}
			else
			{
				unset($root[$part]);
			}
		}
	}

}
