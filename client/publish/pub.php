<?php

require_once(dirname(__FILE__)."/../../config.php");
require_once(dirname(__FILE__)."/../../lib/smob/lib.php");
require_once(dirname(__FILE__)."/../../lib/geonames/geonames.php");

$authority = "http://" . $_SERVER['HTTP_HOST'];
$root = $authority . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); 
	
function twitter_post($content, $user, $pass)  {
  $dest = 'http://twitter.com/statuses/update.xml';
  return curl_post($dest, $content, $user, $pass);
}

function laconica_post($service, $content, $user, $pass)  {
  $dest = $service.'api/statuses/update.xml';
  return curl_post($dest, $content, $user, $pass);
}

function do_curl_post($url, $postfields, $userpwd = null) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	if ($userpwd) {
		curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
	}
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function curl_post($dest, $content, $user, $pass) {
	$postfields = "status=$content&source=smob";
	$userpwd = "$user:$pass";
	return do_curl_post($dest, $postfields, $userpwd);
}

function load_post($post, $server) {
	global $servers;
	$key = $servers[$server];
	$query = urlencode("LOAD <$post> ");
	$postfields = "query=$query&key=$key";
	$dest = "${server}sparql.php";
	return do_curl_post($dest, $postfields);
}

function send_data($url, $server) {
  global $servers;
  $key = $servers[$server];
  $dest = "$server/load/index.php?key=$key&data=".urlencode($url);

  print "Telling <a href='$server'>$server</a>";
  print " about <a href='$url'>$url</a>...\n";

  list ($resp, $status, $code) = curl_get($dest);

  if ($code != 200 && $status)
    print "$status: ";
  if (!$resp) {
    if ($code == 200)
      print "Done.";
    else
      print "No response!";
  }
  print "$resp\n";
}

# XXX maybe one day, someone writes the proper escaping functions for PHP...
function uri($uri) {
	return "<" . $uri . ">";
}

function literal($literal) {
	return '"' . addslashes($literal) . '"';
}

function render_sparql_triple($triple) {
	return implode(" ", $triple);
}

function render_sparql_triples($triples) {
	if (!$triples)
		return "";
	$r = render_sparql_triple($triples[0]);
	$i = 1;
	while ($i < count($triples)) {
		if (count($triples[$i]) == 1)
			$r .= " ,\n";
		else if (count($triples[$i]) == 2)
			$r .= " ;\n";
		else
			$r .= " .\n";
		$r .= render_sparql_triple($triples[$i]);
		$i += 1;
	}
	$r .= " .";
	return $r;
}

function post_template($post_uri, $user_uri, $foaf_uri, $ts, $content, $reply_ofs) {
	$triples[] = array(uri($post_uri), "a", "sioct:MicroblogPost");
	$triples[] = array("sioc:has_creator", uri($user_uri));
	$triples[] = array("foaf:maker", uri($foaf_uri));
	$triples[] = array("dct:created", literal($ts));
	$triples[] = array("dct:title", literal("Update - $ts"));
	$triples[] = array("sioc:content", literal($content));

	foreach ($reply_ofs as $reply_of)
		$triples[] = array("sioc:reply_of", uri($reply_of));

	return $triples;
}

function opo_template($opo_uri, $post_uri, $user_uri, $foaf_uri, $ts, $content, $reply_ofs, $location) {
	$triples[] = array(uri($opo_uri), "a", "opo:OnlinePresence");
	$triples[] = array("opo:declaredOn", uri($user_uri));
	$triples[] = array("opo:declaredBy", uri($foaf_uri));
	$triples[] = array("opo:StartTime", literal($ts));
	$triples[] = array("opo:customMessage", uri($post_uri));
	if($location) {
		$location_pure=substr($location, 0, stripos($location,","));
		$location_uri=find_geo_uri($location_pure);
		$triples[] = array("opo:currentLocation", uri($location_uri));
	}

	return $triples;
}


function publish($content, $srv, $location) {
	global $foaf_uri, $sioc_nick, $root, $servers;
	
	if(get_magic_quotes_gpc()) {
		$content = stripslashes($content);
	}
	
	$reply_ofs = array();
	if ($_GET['sioc:reply_of'])
		$reply_ofs[] = $_GET['sioc:reply_of'];
		
	$ts = date('c');

	print "<h2>Publishing your message...</h2>\n";
		
	$post_uri = "$root/client/post/$ts";
	$opo_uri = "$post_uri#presence";	
	$user_uri = user_uri();

	$sioc = post_template($post_uri, $user_uri, $foaf_uri, $ts, $content, $reply_ofs);
	$opo = opo_template($opo_uri, $post_uri, $user_uri, $foaf_uri, $ts, $content, $reply_ofs, $location);
	$rdf = render_sparql_triples(array_merge($sioc, $opo));	
	$query = "INSERT INTO <${post_uri}.rdf> { $rdf }";
	$res = do_query($query);

	print "<ul>\n";	
	print "<li> Messaged stored locally.</li>\n";
	
	if($srv) {
		$ex = explode(' ', $srv);
		print_r($srv);
		foreach($ex as $server) {
			if(in_array($server, array_keys($servers))) {
				print "<li> ";
				load_post($post_uri, $server);
				print "</li>\n";
			} elseif (substr($server, 0, 2) == 'tw') {
				$twitter_user = substr($server, 3);
				global $twitter_pass;
				print "<li> Relaying your message to Twitter as <a href='http://twitter.com/$twitter_user'>$twitter_user</a>.\n";
				twitter_post($content, $twitter_user, $twitter_pass);
				print "</li>";				
			}
		}
	}
	if($_GET['laconica']) {
		foreach($_POST['laconica'] as $service => $v) {
			$user = $laconica[$service];
			$laconica_user = $user['user'];
			$laconica_pass = $user['pass'];
			print "<li> Relaying your message to $service as <a href='$service/$laconica_user'>$laconica_user</a>.\n";
			laconica_post($service, $content, $laconica_user, $laconica_pass);
			print "</li>";
		}
 	}
	print "</ul>";

	return $post_uri;
}

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
$srv = $_GET['servers'];
$post = $_GET['post'];
$location= $_GET['location'];


// In case we need to publish content
if($content) {

print_r($_GET);

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
$id = publish($content, $srv, $location);

// Output the results to let the user chose his URIs -- if there are some tags
if($users || $tags) {
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
?>