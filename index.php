<?php

use Elasticsearch\ClientBuilder;
use Rubberband\Elastic\Query;

require 'vendor/autoload.php';



ini_set('display_errors', true);
error_reporting(E_ALL);


$client = ClientBuilder::create()->build();

$q=new Query($client);
$q->from('api.trans')->addAggregation(new \Rubberband\Elastic\Aggregation\DateHistogram('permonth','created_at',['interval'=>'month']));
$rows =$q->get();
echo '<pre>';
print_r($rows->aggregation('permonth')->flatten());
echo '</pre>';