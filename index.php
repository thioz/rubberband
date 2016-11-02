<?php

use Elasticsearch\ClientBuilder;
use Rubberband\Elastic\Query;

require 'vendor/autoload.php';



ini_set('display_errors', true);
error_reporting(E_ALL);


$client = ClientBuilder::create()->build();

$q=new Query($client);
$q->from('api.people');
$rows =$q->get();
echo '<pre>';
print_r($rows);
echo '</pre>';