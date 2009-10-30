<?php

require_once(dirname(__FILE__).'/SPAF_Maps.class.php');

function find_geo_alternatives($query){

// 2. instantiate the object and set search query
$map = new SPAF_Maps($query);


// 3. get results
$results = $map->getResults();
$locations=results2array($results);

//$locations=save_array_dump("geonames.file", $locations);
return $locations;

}

function find_geo_uri($geoname){

// 2. instantiate the object and set search query
$map = new SPAF_Maps($query);


// 3. get results
$results = $map->getResults();

$location = $results[0];
$geonames_uri= "http://sws.geonames.org/" . $location['geonameId'] . "/";


return $geonames_uri;

}


function results2array($results){
$locations= array();
if(!$results) return $locations;
$cnt = sizeof($results);
//echo "<select name=\"$var_name\">\n";
for ($i = 0; $i < $cnt; $i++) {
  $location = &$results[$i];
  $geo_name= $location['name'] ." (" . $location['countryName'] . "), (" . $location['lat'] . ", " . $location['lng'] . ")";
  $geonames_uri= "http://sws.geonames.org/" . $location['geonameId'] . "/";

  $locations[$geonames_uri]=$geo_name;
}
return $locations;

}
?>
