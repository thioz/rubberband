<?php

namespace Rubberband\Elastic\Laravel;

class ResultCollection extends \Rubberband\Elastic\Result implements \Iterator
{
	protected $currentIndex=0;
	protected $items=[];
	
	public function __construct($result) {
		parent::__construct($result);
		$this->items=$this->hits();
	}
	public function current() {
		return $this->items[$this->currentIndex];
	}

	public function key() {
		return $this->currentIndex;
	}

	public function next() {
		$this->currentIndex++;
	}

	public function rewind() {
		$this->currentIndex=0;
	}

	public function valid() {
		return isset($this->items[$this->currentIndex]);
	}

}