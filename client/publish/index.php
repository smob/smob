<?php

// Edit - this is the path where this file can be accessed - no trialing slash

require_once(dirname(__FILE__).'/sioc_inc.php');
require_once(dirname(__FILE__).'/../config.php');

function send_data($url, $server) {
  global $servers;
  $key = $servers[$server];
  $dest = "$server/load/index.php?key=$key&data=".urlencode($url);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $dest);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $data = curl_exec($ch);
  curl_close($ch);
}

if($content=$_POST['content']) {
  $ts = date('c');
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
  foreach($_POST['servers'] as $k => $server) {
    print "<li> Telling <a href='$server'>$server</a>";
    print " about <a href='$post.rdf'>$post.rdf</a>...\n";
    send_data("$post.rdf", $server);
    print " done. </li>\n";
    print "<li> Telling <a href='$server'>$server</a>";
    print " about <a href='$foaf_url'>$foaf_url</a>...\n";
    // The FOAF file should not be sent everytime - fix it
    send_data($foaf_url, $server);
    print " done. </li>\n";
  }
  print "</ul>\n";
}

?>

<h2>New content</h2>
<form action="index.php" method="POST">
<textarea name="content"></textarea>
<br/>
<?php
foreach($servers as $server => $key) {
  echo"<input type='checkbox' name='servers[]' value='$server' />$server<br/>";
}
?>
<input type="submit" value="SMOB it!"/>
</form>
