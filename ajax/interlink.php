<?php

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

if(!in_array($type, array('tag', 'location', 'user'))) die();

print "<fieldset><legend>$term</legend>";
if($type == 'tag' || $type == 'user') {
	$term = substr($term, 1);
} else if($type == 'location') {
	$term = substr($term, 2);
}
$wrappers = get_wrappers($type);
foreach($wrappers as $wrapper) {
	print "<fieldset><legend>Via $wrapper</legend>";
	$uris = find_uris($wrapper, $term, $type);
	if($uris) {
		foreach($uris as $name=>$uri) {
			$val = "$type--$term--$uri";
			print "<input type='checkbox' value='$val'/>$name (<a href='$uri' target='_blank'>$uri</a>)<br/>";
		}
	} else {
		print "Nothing retrieved from this service<br/>";
	}
	print "</fieldset>";
}
