<?php 
include_once 'vendor/autoload.php';
use simplehtmldom\HtmlWeb;

$client = new HtmlWeb();
$html = $client->load('http://www.rushydro.ru/hydrology/informer/');

// Returns the page title
echo $html->find('title', 0)->plaintext . PHP_EOL;


 ?>
