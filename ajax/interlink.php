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

if($type == 'tag') {
	$tag = substr($term, 1);
	print "<fieldset><legend>#$tag</legend>";
	$wrappers = get_wrappers('tag');
	foreach($wrappers as $wrapper) {
		print "<fieldset><legend>Via $wrapper</legend>";
		$uris = find_uris($wrapper, $tag, 'tag');
		if($uris) {
			foreach($uris as $name=>$uri) {
				$val = "$type--$tag--$uri";
				print "<input type='checkbox' value='$val'/>$name ($uri)<br/>";
			}
		} else {
			print "Nothing retrieved from this service<br/>";
		}
		print "</fieldset>";
	}
}

// In case we just need to update the mappings
/*if($post) {
	$checked = $_GET['checked'];
	$unchecked = $_GET['unchecked'];
	$ck = explode(' ', $checked);
	foreach($ck as $c) {
		$ckl = explode('--', $c);
		if($ckl[0] == 'users') {
			$user = $ckl[1];
			$uri = $ckl[2];
			// Update with sioc:xxx
			$triples[] = array(uri($post), "sioc:topic", uri($uri));
			$triples[] = array(uri($uri), "sioc:name", literal($user));
			
		}
		elseif($ckl[0] == 'tags') {
			$tag = $ckl[1];
			$uri = $ckl[2];
			// Update with MOAT / commonTag
			$tagging = 'http://example.org/tagging/'.uniqid();
			$triples[] = array(uri($tagging), "a", "tags:RestrictedTagging");
			$triples[] = array(uri($tagging), "tags:taggedResource", uri($post));
			$triples[] = array(uri($tagging), "tags:associatedTag", literal($tag));
			$triples[] = array(uri($tagging), "moat:tagMeaning", uri($uri));
		}
	}
	$triples = render_sparql_triples($triples);
	$query = "INSERT INTO <${post}.rdf> { $triples }";
	do_query($query);
	print "The mappings have been successfully updated !";
}

*/

