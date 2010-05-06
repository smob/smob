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

function existing_uris($term, $type) {
	$uris = array();
	if($type == 'user') {
		$query = "
SELECT DISTINCT ?uri
WHERE {
	[] sioc:addressed_to ?uri .
	?uri sioc:name '$term' .
}";
	} elseif(in_array($type, array('tag', 'location'))) {
		$query = "
SELECT DISTINCT ?uri
WHERE {
	[] tags:associatedTag '$term' ;
		moat:tagMeaning ?uri .
}";		
	}
	$res = SMOBStore::query($query);
	foreach($res as $r) {
		$uris[] = $r['uri'];
	}
	return $uris;
}

$type = $_GET['type'];
$term = $_GET['term'];

if(!in_array($type, array('tag', 'location', 'user'))) die();

$original = $term;
if($type == 'tag' || $type == 'user') {
	$term = substr($term, 1);
} else if($type == 'location') {
	$term = substr($term, 2);
}
// Remove last char if ,./?!+
$stops = array(',', '.', '/', '?', '!', '+', ':');
$last = substr($term, strlen($term)-1);
if(in_array($last, $stops)) {
	$term = substr($term, 0, -1);
	$original = substr($original, 0, -1);
}

// URIs from wrappers
$wrappers = get_wrappers($type);
$class = 'empty';
foreach($wrappers as $wrapper) {
	$ht .= "<fieldset><legend>Via $wrapper</legend>";
	$existing = existing_uris($term, $type);
	$uris = find_uris($wrapper, $term, $type);
	if($uris) {
		foreach($uris as $name=>$uri) {
			$val = "$type--$term--$uri";
			$checked = in_array($uri, $existing) ? 'checked=\'true\'' : '';
			if($checked) {
				$class = 'found';
			}
			$ht .= "<input type='checkbox' value='$val' $checked/>$name (<a href='$uri' target='_blank'>$uri</a>)<br/>";
		}
	} else {
		$ht .= "Nothing retrieved from this service<br/>";
	}
	$ht .= "</fieldset>";
}

print "
{
	\"id\" : \"tc-$type-$term\",
	\"term\" : \"$original\",
	\"html\" : \"$ht\",
	\"class\" : \"$class\"
}";
