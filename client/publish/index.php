<?php

// Edit - this is the path where this file can be accessed - no trialing slash

require_once(dirname(__FILE__).'/../lib/sioc_inc.php');
require_once(dirname(__FILE__).'/../config.php');

function twitter_post($content, $user, $pass)  {
  $dest = 'http://twitter.com/statuses/update.xml';
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

if($content=$_POST['content']) {
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
  $user = new SIOCUser($sioc_nick, "$root/user/$sioc_nick", 'name', 'mail', 'page', $foaf_uri, '', '', '', $foaf_url);
  $ex->addObject(new SIOCPost($post, $ts, $content, '', $user, $ts, '', '', '', 'sioct:MicroBlogPost'));
  $rdf = $ex->makeRDF();
  $f = fopen(dirname(__FILE__)."/../data/$ts.rdf", 'w');
  fwrite($f, $rdf);
  fclose($f);
  print "<ul>\n";
  if($_POST['servers']) {
    foreach($_POST['servers'] as $k => $server) {
      print "<li> ";
      send_data("$post.rdf", $server);
      print "</li>\n<li> ";
      // The FOAF file should not be sent everytime - fix it
      send_data($foaf_url, $server);
      print "</li>\n";
    }
  }
  if($_POST['twitter']) {
    print "<li> Relaying your message to Twitter as <a href='http://twitter.com/$twitter_user'>$twitter_user</a>.\n";
    twitter_post($content, $twitter_user, $twitter_pass);
    print "</li>";
  }
  print "</ul>\n";
}

?>
<!-- XXX hack to make browsers send the posting as utf-8 -->
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

<h2>New content</h2>
<form action="index.php" method="POST">
<textarea name="content"></textarea>
<br/>
<?php
foreach($servers as $server => $key) {
  echo"<input type='checkbox' name='servers[]' value='$server' />$server<br/>";
}
if($twitter_user && $twitter_pass) {
  echo "<input type='checkbox' name='twitter' value='twit' />" .
       "Twitter as $twitter_user<br/>";
}

?>
<input type="submit" value="SMOB it!"/>
</form>
