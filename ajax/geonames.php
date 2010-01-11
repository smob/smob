<?php

require_once(dirname(__FILE__).'/../lib/smob/SMOBTools.php'); 

$query = $_GET['query'];

$uri = "http://ws.geonames.org/searchJSON?q=".urlencode($query)."&maxRows=10";
$res = SMOBTools::do_curl($uri);
$json = json_decode($res[1], true);
foreach($json['geonames'] as $j) {
	$uri= "http://sws.geonames.org/" . $j['geonameId'] . "/";
	$name = $j['name'] .', '.  $j['countryName']. ' ('. $j['fcodeName'] . ')';
	$suggestions .= "'$name',";
	$data .= "'$uri',";
}

$s = substr($suggestions, 0, -1);
$d = substr($data, 0, -1);
print "{ query:'$query',suggestions:[$s],data:[$d] }";

?>