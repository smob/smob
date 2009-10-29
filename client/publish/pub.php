<?php

// SCRIPT_URI isn't present on all servers, so we do this instead:
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

function curl_post($dest, $content, $user, $pass) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $dest);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "status=$content&source=smob");
  curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
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

function local_post($post, $uri) {

	foreach(array($post, $uri) as $data) {
		do_query("DELETE FROM <$data>"); 
		do_query("LOAD <$data> INTO <$data>");
	}
	print "<li> Messaged stored locally.</li>\n";
    
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

	// @@ TODO -> Export HTML with content:encoded
	$triples[] = array(uri($post_uri), "a", "sioct:MicroblogPost");
	$triples[] = array("sioc:has_creator", uri($user_uri));
	$triples[] = array("foaf:maker", uri($foaf_uri));
	$triples[] = array("dct:created", literal($ts));
	$triples[] = array("dct:title", literal("Update - $ts"));
	$triples[] = array("sioc:content", literal($content));

	foreach ($reply_ofs as $reply_of)
		$triples[] = array("sioc:reply_of", uri($reply_of));

	return render_sparql_triples($triples);
}


function publish($content) {
	global $foaf_uri, $sioc_nick;
	
	if(get_magic_quotes_gpc()) {
		$content = stripslashes($content);
	}
	print "<h2>Publishing your message...</h2>\n";
	
	// date('c') isn't implemented by PHP4:
	$ts = date('Y-m-d\TH:i:s'). substr_replace(date('O'),':',3,0);
		
	$post_uri = "$root/post/$ts";
	$user_uri = "$root/user/$sioc_nick";

	$reply_ofs = array();
	if ($_GET['sioc:reply_of'])
		$reply_ofs[] = $_GET['sioc:reply_of'];

	$post_rdf = post_template($post_uri, $user_uri, $foaf_uri, $ts, $content, $reply_ofs);

	$query = "INSERT INTO <${post_uri}.rdf> { $post_rdf }";
	$res = do_query($query);
	print "<ul>\n";
	local_post("$posturi.rdf", $foaf_uri);
	if($_GET['servers']) {
		foreach($_GET['servers'] as $k => $server) {
			print "<li> ";
			send_data("$posturi.rdf", $server);
			print "</li>\n<li> ";
			// The FOAF file should not be sent everytime - fix it
			send_data($foaf_uri, $server);
			print "</li>\n";
		}
	}
  if($_GET['twitter']) {
    print "<li> Relaying your message to Twitter as <a href='http://twitter.com/$twitter_user'>$twitter_user</a>.\n";
    twitter_post($content, $twitter_user, $twitter_pass);
    print "</li>";
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

	return $post_uri;
}


?>
