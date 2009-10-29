<?php

require_once(dirname(__FILE__)."/../../config.php");
require_once(dirname(__FILE__)."/../../lib/smob/lib.php");
require_once(dirname(__FILE__)."/pub.php");

function get_wrappers($type) {
	if ($handle = opendir(dirname(__FILE__)."/wrappers/$type")) {
    	while (false !== ($file = readdir($handle))) {
			if (substr($file, 0, 1) != '.') {
				$services[] = $file;
				require_once(dirname(__FILE__)."/wrappers/$type/$file/index.php");
			}
		}
		closedir($handle);
	}
	return $services;
}

function find_uris($type, $term) {
	global $wrappers;
	foreach($wrappers[$type] as $w) {
		$w = ucfirst($w).ucfirst($type).'Wrapper';
		$x = new $w($term);
		$uris[$w] = $x->get_uri();	
	}
	return $uris;
}

$content = $_GET['content'];

$wrappers['user'] = get_wrappers('user');	
$wrappers['tag'] = get_wrappers('tag');	

$ex = explode(' ', $content);

foreach($ex as $e) {
	if(substr($e, 0, 1) == '@') {
		$user = substr($e, 1);
		$users[$user] = find_uris('user', $user);
	}
	elseif(substr($e, 0, 1) == '#') {
		$tag = substr($e, 1);
		$tags[$tag] = find_uris('tag', $tag);
	}
}

// @@TODO : Improve the following with real checkbox used to generate the RDF
// + keep mappings in the local store for automation


// Publish the post
publish($content);

// Output the results
print "<form>";
foreach(array($tags, $users) as $items) {
if($items) {
	foreach($items as $item=>$wrapper) {
		print "<fieldset><legend>$item</legend>";
		foreach($wrapper as $wname => $uris) {
			print "<fieldset><legend>$wname</legend>";
			if($uris) {
				foreach($uris as $name=>$uri) {
					print "<input type='checkbox'/>$name ($uri)<br/>";
				}
			} else {
				print "Nothing retrieved from this service<br/>";
			}
			print "</fieldset>";
		}
		print "</fieldset>";
	}
}
}
print "</form>";

/////////////////////////////////
// All the wrappers must inherit from this class
// And implement the get_uri method that returns an array
// of URI=>label mappings for the tags, e.g.
//
// Array
// (
//    [London] => http://dbpedia.org/resource/London
//    [City of London] => http://dbpedia.org/resource/City_of_London
// )

abstract class SMOBURIWrapper {

	public function __construct($item) {
		$this->item = $item;
	}
	
	public function get_uri() {
		// Needs to be done in each wrapper plug-in
	}

}