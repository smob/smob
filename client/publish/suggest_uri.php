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
$post = $_GET['post'];


// In case we need to publish content
if($content) {

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

// @@TODO : keep mappings in the local store for automation


// Publish the post
$id = publish($content);

// Output the results to let the user chose his URIs
print "<h2>LOD Integration</h2>";
print "<form id='mappings-form'>";
print "<input type='hidden' value='$id' id='post-id'>";
foreach(array('tags' => $tags, 'users' => $users) as $type => $items) {
if($items) {
	print "<fieldset><legend>$type</legend>";
	foreach($items as $item=>$wrapper) {
		print "<fieldset><legend>$item</legend>";
		foreach($wrapper as $wname => $uris) {
			print "<fieldset><legend>$wname</legend>";
			if($uris) {
				foreach($uris as $name=>$uri) {
					$val = "$type--$item--$uri";
					print "<input type='checkbox' value='$val'/>$name ($uri)<br/>";
				}
			} else {
				print "Nothing retrieved from this service<br/>";
			}
			print "</fieldset>";
		}
		print "</fieldset>";
	}
	print "</fieldset>";
}
}
print "</form>";
print '
<script type="text/javascript">
$(function() {
	$("#mappings").click(function () {
		mappings();
	});
});
</script>

<div id="smob-mappings" style="display: none;">
	<em>Publishing content ...</em>
</div>	<button id="mappings">Update mappings</button>
';
}

// In case we just need to update the mappings
if($post) {
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
		}
		elseif($ckl[0] == 'tags') {
			$tag = $ckl[1];
			$uri = $ckl[2];
			// Update with MOAT / commonTag
			$triples[] = array(uri($post), "sioc:topic", uri($uri));
		}
	}
	$triples = render_sparql_triples($triples);
	$query = "INSERT INTO <${post}.rdf> { $triples }";
	do_query($query);
	print "The mappings have been successfully updated !";
}

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
