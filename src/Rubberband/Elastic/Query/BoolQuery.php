<?php

namespace Rubberband\Elastic\Query;

use Rubberband\Elastic\Query;

class BoolQuery extends Query
{
	protected function buildParams(){
		
		$params = [
			'index'=> $this->index,
		];
		
		if($this->type){
			$params['type']=$this->type;
		}
		
		$params['body'] = [
			'query' => [
				'bool' => [
					'must' => $this->buildMustParams()
				]
				
			]
		];
		return $params;
	}
}
