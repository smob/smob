<?php

require_once(dirname(__FILE__)."/../arc/ARC2.php");
require_once(dirname(__FILE__)."/lib.php");


function smob_go($title, $posts) {
	smob_header();
	show_posts();
	$n = get_networks();
	$n .= "<h2>Admin</h2><ul><li><a href='./publish'>Publish</a>";
	smob_footer($n);	
}

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
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <title>SMOB - <?php echo $sioc_nick; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/style.css" />
</head>

<body>

<div id="full">

<div id="header">
<h1><a href="#">SMOB</a></h1>
<h2>Posts for <?php echo $sioc_nick; ?></h2>
</div>

<div id="main">

<div class="left"> 

<?
}

function smob_footer($blocks) {
?>

</div>

<div class="right"> 

<?php echo $blocks; ?>

</div>

<div style="clear: both;"> </div>
</div>

<div id="footer">
Powered by <a href="http://smob.siob-project.org/">SMOB</a> thanks to <a href="http://www.w3.org/2001/sw/">Semantic Web</a> technologies and <a href="http://linkeddata.org">Linked Data</a><br/>
</div>
</div>

</body>

</html>
<?
}

function get_networks() {
	global $servers, $twitter_user;
	$ht = "<h2>My networks</h2>\n\n";
	$ht .= "<ul>\n";
	foreach($servers as $server => $key) {
		$ht .= "  <li><a href='$server'>$server</a></li>\n";
	}
	if ($twitter_user) {
		$ht .= "  <li>Twitter as <a href='http://twitter.com/$twitter_user'>$twitter_user</a></li>\n";
	}
	$ht .= "</ul>\n\n";
	return $ht;
}

function show_postss($posts) {
	global $sioc_nick;  
	foreach($posts as $post) {
		$uri = $post['post'];
		$content = $post['content'];
		$author = $post['author'];
		$date = $post['date'];
		echo "<div class=\"post\" typeof=\"sioct:MicroblogPost\" about=\"$uri\">\n";
		echo "  <span class=\"content\" property=\"sioc:content\">$content</span>\n";
		echo "  (<span class=\"author\" rel=\"foaf:maker\" href=\"$foaf_uri\">$sioc_nick</span> - \n";
		echo "  <span class=\"date\" property=\"dcterms:created\">$date</span>)\n";
		echo "</div>\n\n";
	}
}

function show_posts($start=0, $limit=20) {
	echo "<h1>Latest updates</h1>\n\n";
	$posts = get_posts($start, $limit);
	show_postss($posts);
}

function get_posts($start=0, $limit=20) {
	$query = "
	SELECT ?post ?content ?author ?date
WHERE {
	?post rdf:type sioct:MicroblogPost ;
		sioc:content ?content ;
		foaf:maker ?author ;
		dct:created ?date .
} ORDER BY DESC(?date)
";
	return do_query($query);
}

?>


