<?php

require_once(dirname(__FILE__).'/../../lib/sioc/sioc_inc.php');

require_once(dirname(__FILE__).'/../../config.php');


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

function local_post($post, $uri) {
	global $arc_config;
	
	$config = $arc_config + array(
	  'sem_html_formats' => 'rdfa' // From HTML documents, only load RDFa triples
	);

	$store = ARC2::getStore($config);
	if (!$store->isSetUp()) {
	  $store->setUp();
	}
	
	foreach(array($post, $uri) as $data) {
		$store->query("DELETE FROM <$data>"); // delete any old data first
		$rs = $store->query("LOAD <$data> INTO <$data>");
	}
	print "<li> Messaged stored locally.</li>\n";
    
}

if($content=$_GET['content']) {
  // We know what to quote better than you PHP, thank you very much:
  if(get_magic_quotes_gpc())
    $content = stripslashes($content);

  print "<h2>Publishing your message...</h2>\n";

  // date('c') isn't implemented by PHP4:
  $ts = date('Y-m-d\TH:i:s'). substr_replace(date('O'),':',3,0);
  // SCRIPT_URI isn't present on all servers, so we do this instead:
  $authority = "http://" . $_SERVER['HTTP_HOST'];
  $root = $authority . dirname(dirname($_SERVER['SCRIPT_NAME'])); 
  $post = "$root/data/$ts";
  $ex = new SIOCExporter();
  $user = new SIOCUser($sioc_nick, "$root/user/$sioc_nick", 'name', 'mail', 'page', $foaf_uri);
  $ex->addObject(new SIOCPost($post, $ts, $content, '', $user, $ts, '', '', '', 'sioct:MicroblogPost'));
  $rdf = $ex->makeRDF();
  $f = fopen(dirname(__FILE__)."/../data/$ts.rdf", 'w');
  fwrite($f, $rdf);
  fclose($f);
  print "<ul>\n";
  local_post("$post.rdf", $foaf_uri);
  if($_GET['servers']) {
    foreach($_GET['servers'] as $k => $server) {
      print "<li> ";
      send_data("$post.rdf", $server);
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
}

?>