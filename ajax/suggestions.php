<?php
//$json = json_encode($dbpediaentities, true);
//$json = json_decode($dbpediaentities, true);
//foreach ($json as $label=>$uri) {
//  $options .= "<option value='".$rel."' >".$label."</option>";
//};
//return $rels;


//require_once(dirname(__FILE__)."/../lib/smob/wrappers/tag/dbpedia.php");
//$x = new DBPediaTagWrapper($term);
//$uris = $x->get_uris();

require_once(dirname(__FILE__).'/../lib/smob/SMOB.php'); 
require_once(dirname(__FILE__)."/../config/config.php");

function get_wrappers($type) {
  if ($handle = opendir(dirname(__FILE__)."/../lib/smob/wrappers/$type")) {
      while (false !== ($file = readdir($handle))) {
      if (substr($file, 0, 1) != '.') {
        $services[] = substr($file, 0, -4);
        require_once(dirname(__FILE__)."/../lib/smob/wrappers/$type/$file");
      }
    }
    closedir($handle);
  }
  return $services;
}

function find_uris($wrapper, $term, $type) {
  $w = ucfirst($wrapper).ucfirst($type).'Wrapper';
  $x = new $w($term);
  return $x->get_uris();
}

$type = $_GET['type'];
$term = $_GET['term'];


// URIs from wrappers
$wrappers = get_wrappers($type);
//$ht = "<div id='suggestions'>";
$ht = "";
$i = 0;
foreach($wrappers as $wrapper) {
  $ht .= "<fieldset><legend>Via $wrapper</legend>";
  $uris = find_uris($wrapper, $term, $type);
  if($uris) {
    foreach($uris as $name=>$uri) {
      $ht .= "<input id='suggestion$i' type='radio' name='suggestion' value='$uri'/><label for='suggestion$i'>$name</label> ($uri)<br/>";
      $i++;
    }
  } else {
    $ht .= "Nothing retrieved from this service<br/>";
  }
  $ht .= "</fieldset>";
}
//$ht .= "</div>";
print $ht;
