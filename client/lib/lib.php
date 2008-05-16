<?php

require_once(dirname(__FILE__)."/ARC2/ARC2.php");

function smob_header() {
  global $sioc_nick;
?>

<html>

<head>
  <title>SMOB - <?php echo $sioc_nick; ?></title>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>

<ul id="menu">
<li><a href="data">browse data</a></li>
<li><a href="publish">publish</a></li/>
</ul>

<div id="main">

<?
}

function smob_footer() {
?>
</body>

</html>
<?
}

function show_networks() {
  global $servers, $twitter_user;
  echo "<h1>My networks</h1>";
  echo "<ul>";
  foreach($servers as $server => $key) {
    echo "<li><a href='$server'>$server</a></li>";
  }
  if ($twitter_user) {
    echo "<li>Twitter as " .
       "<a href='http://twitter.com/$twitter_user'>$twitter_user</a>";
  }
  echo "</ul>";
}

function show_post($id) {

  $authority = "http://" . $_SERVER['HTTP_HOST'];
  $root = $authority . dirname(dirname($_SERVER['SCRIPT_NAME'])); 
  $post = substr("$root/client/data/$id", 0, -4);

  $parser = ARC2::getRDFParser();
  $parser->parse(dirname(__FILE__)."/../data/$id");
  $triples = $parser->getSimpleIndex();
  $datapost = $triples[$post];
  $date = $datapost['http://purl.org/dc/terms/created'][0];
  $content = $datapost['http://rdfs.org/sioc/ns#content'][0];
  
  echo '<div id="post">';
  echo "<div id=\"content\">$content</div>";
  echo "<div id=\"date\">$date</div>";
  echo '</div>';
}

function show_posts($start=0, $limit=20) {
  $data = get_posts($start, $limit);
  echo "<h1>Latest updates</h1>";
  foreach($data as $post) {
    show_post($post);
  }
}

function get_posts($start=0, $limit=20) {
  if ($handle = opendir(dirname(__FILE__).'/../data')) {
    while (false !== ($file = readdir($handle))) {
      if(substr($file, -4) == '.rdf') $files[] = $file;
    }
  }
  rsort($files);
  return array_slice($files, $start, $limit);
}

?>

