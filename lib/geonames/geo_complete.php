<?php
require_once(dirname(__FILE__).'/geonames.php');


$q = strtolower($_GET["q"]);
if (!$q) return;

$items= find_geo_alternatives($q);

foreach ($items as $key=>$value) {
  //  if (strpos(strtolower($value), $q) !== false) {
        echo "$value\n";
  //  }
}

?> 