<?php

require_once(dirname(__FILE__)."/../../config.php");
require_once(dirname(__FILE__)."/../../lib/smob/lib.php");

$content = $_GET['content'];

$ex = explode(' ', $content);

foreach($ex as $e) {
	if(substr($e, 0, 1) == '@') {
		$user = substr($e, 1);
		$users[$user] = find_user_uris($user);
	}
	elseif(substr($e, 0, 1) == '#') {
		$tag = substr($e, 1);
		$tags[$tag] = find_tag_uris($user);
	}
}

foreach($users as $user=>$uris) {
	print '<fieldset>';
	print "<legend>$user</legend>";
	foreach($uris as $uri) {
		print "<input type='checkbox' name'@@TODO' id='@@TODO'/>$uri";
	}
	print '</fieldset>';
}

function curl_get($url) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  $response = curl_exec($ch);

  if ($error = curl_error($ch))
    return array("$error.", "", 0);

  $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $status_line = substr($response, 0, strcspn($response, "\n\r"));
  $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
  $body = substr($response, $header_size);

  curl_close($ch);

  return array($body, $status_line, $status_code);
}

function get_uri_if_found($uri) {
  list ($resp, $status, $code) = curl_get($uri);
  if ($code == 200)
    return $uri;
  else if ($code == 404)
    return "";
  else {
    print "Error fetching $uri: ";
    if ($status)
      print $status;
    else
      print $resp;
    return "";
  }
}

function get_locally_known_microbloggers($user) {
  $rs = do_query("
SELECT DISTINCT ?user WHERE {
  ?post rdf:type sioct:MicroblogPost .
  ?post sioc:has_creator ?user .
  ?post foaf:maker ?person .
  ?person foaf:nick '$user' .
}
  ");

  if ($rs) {
    foreach($rs as $row) {
      print $row['user'] . "\n";
    }
  }
}

function find_user_uris($user) {
	$users[] = get_locally_known_microbloggers($user) . "\n";

	# XXX ask the known aggregators (via the sparql endpoint?)

	# XXX find the sioc:User from the identi.ca profile URI
	# ie. <http://identi.ca/user/11736#acct> from <http://identi.ca/johnbreslin>
	$users[] = get_uri_if_found("http://identi.ca/$user") . "\n";
	$users[] = get_uri_if_found("http://twitter.com/$user") . "\n";
	return $users;
}

function find_tag_uris($tag) {
	return null;
	// TODO
}
