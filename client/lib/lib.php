<?php

require_once(dirname(__FILE__)."/../../arc/ARC2.php");

function smob_header() {
  global $sioc_nick;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" 
  "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">

<html
  xmlns="http://www.w3.org/1999/xhtml" 
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:foaf="http://xmlns.com/foaf/0.1/" 
  xmlns:sioc="http://rdfs.org/sioc/ns#"
  xmlns:sioct="http://rdfs.org/sioc/types#"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
xml:lang="fr">
 
<head profile="http://ns.inria.fr/grddl/rdfa/">
  <title>SMOB - <?php echo $sioc_nick; ?></title>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>

<ul id="menu">
  <li><a href="">home</a></li>
  <li><a href="data">browse RDF data</a></li>
  <li><a href="publish">publish</a></li>
</ul>

<div id="main">

<?
}

function smob_footer() {
?>

</div>

</body>

</html>
<?
}

function show_networks() {
  global $servers, $twitter_user;
  echo "<h1>My networks</h1>\n\n";
  echo "<ul>\n";
  foreach($servers as $server => $key) {
    echo "  <li><a href='$server'>$server</a></li>\n";
  }
  if ($twitter_user) {
    echo "  <li>Twitter as " .
       "<a href='http://twitter.com/$twitter_user'>$twitter_user</a></li>\n";
  }
  echo "</ul>\n\n";
}

function show_post($id) {
  global $sioc_nick, $foaf_uri;
  $authority = "http://" . $_SERVER['HTTP_HOST'];
  $root = $authority . dirname($_SERVER['SCRIPT_NAME']); 
  $post = substr("$root/data/$id", 0, -4);

  $parser = ARC2::getRDFParser();
  $parser->parse(dirname(__FILE__)."/../data/$id");
  $triples = $parser->getSimpleIndex();
  $datapost = $triples[$post];
  $date = $datapost['http://purl.org/dc/terms/created'][0];
  $content = $datapost['http://rdfs.org/sioc/ns#content'][0];
  
  echo "<div class=\"post\" typeof=\"sioct:MicroblogPost\" about=\"$post\">\n";
  echo "  <span class=\"content\" property=\"sioc:content\">$content</span>\n";
  echo "  (<span class=\"author\" rel=\"foaf:maker\" href=\"$foaf_uri\">$sioc_nick</span> - \n";
  echo "  <span class=\"date\" property=\"dcterms:created\">$date</span>)\n";
  echo "</div>\n\n";
}

function show_posts($start=0, $limit=20) {
  $data = get_posts($start, $limit);
  echo "<h1>Latest updates</h1>\n\n";
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


