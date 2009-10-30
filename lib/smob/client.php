<?php

require_once(dirname(__FILE__)."/../arc/ARC2.php");
require_once(dirname(__FILE__)."/lib.php");


function smob_go($title, $content) {
	global $root;
	smob_header($title);
	print $content;
	$n = get_networks();
	$n .= "<h2>Navigation</h2><ul><li><a href='$root/client'>Home</a></li><li><a href='$root/client/publish'>Publish</a></li></ul>";
	smob_footer($n);	
}

function smob_header($title) {
	global $sioc_nick, $root;
	
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
  <title>SMOB - <?php echo $title; ?></title>
  <link rel="stylesheet" type="text/css" href="<?php echo "$root/css/style.css"; ?>" />
</head>

<body>

<div id="full">

<div id="header">
<h1><a href="<?php echo "$root/client"; ?>">SMOB</a></h1>
<h2><?php echo $title; ?></h2>
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
		$ht .= do_post($post);
	}
	return $ht;
}

function do_post($post, $uri = null) {
	global $sioc_nick;
	if(!$uri) {
		$uri = $post['post'];		
	}
	$content = $post['content'];
	$author = $post['author'];
	$date = $post['date'];
	// Find the topics
	$ht .= "<div class=\"post\" typeof=\"sioct:MicroblogPost\" about=\"$uri\">\n";
	$users = get_users($uri);
	if($users) {
		foreach($users as $t) {
			$user = $t['user'];
			$name = $t['name'];
			$enc = urlencode($user);
			$r = "<a class=\"topic\" property=\"sioc:topic\" href=\"$user\"><a href=\"replies/$enc\">@$name</a></a>";
			$content = str_replace("@$name", $r, $content);
		}
	}
	$tags = get_tags($uri);
	if($tags) {
		foreach($tags as $t) {
			$tag = $t['tag'];
			$uri = $t['uri'];
			$enc = urlencode($uri);
			$r = "<span class=\"topic\" property=\"sioc:topic\" href=\"$uri\"><a href=\"resource/$enc\">#$tag</a></span>";
			$content = str_replace("#$tag", $r, $content);
		}
	}
	$enc = urlencode($author);
	$ht .= "  <span class=\"content\" property=\"sioc:content\">$content</span>\n";
	$ht .= "  (by <span class=\"author\" rel=\"foaf:maker\" href=\"$author\"><a href=\"user/$enc\">$sioc_nick</a></span> - \n";
	$ht .= "  <span class=\"date\" property=\"dcterms:created\">$date</span>)\n";
	$ht .= " [<a href=\"$uri\">P</a>]\n";
	$ht .= "</div>\n\n";
	return $ht;
}

function show_post($id) {
	global $root;
	$uri = "$root/client/post/" . str_replace(' ', '+', $id);
	$p = get_post($uri);
	return "<h1>$id</h1>\n\n" . do_post($p[0], $id);
}

function show_posts($page = 0) {
	$start = $page;
	$offset = 20;
	$posts = get_posts($start, $offset);
	return "<h1>Public timeline</h1>\n\n" . show_postss($posts ) . pager($start);
}

function pager($start) {
	if($start == 0) {
		return "<div><a href='?page=1'>Previous posts</a></div>";
	} else {
		$previous = $start + 1;
		$next = $start - 1;
		return "<div><a href='?page=$next'>Next posts</a> -- <a href='?page=$previous'>Previous posts</a></div>";
	}
}
function get_tags($post) {
	$query = "
	SELECT ?tag ?uri
	WHERE {
		?tagging a tags:RestrictedTagging ;
			tags:taggedResource <$post> ;
			tags:associatedTag ?tag ;
			moat:tagMeaning ?uri .
	}
	";
	return do_query($query);
}

function get_users($post) {
	$query = "
	SELECT ?user ?name
	WHERE {
		<$post> sioc:topic ?user .
		?user sioc:name ?name .
	}
	";
	return do_query($query);
}

function get_posts($start, $limit) {
	$query = "
	SELECT ?post ?content ?author ?date
WHERE {
	?post rdf:type sioct:MicroblogPost ;
		sioc:content ?content ;
		foaf:maker ?author ;
		dct:created ?date .
} 
ORDER BY DESC(?date)
OFFSET $start
LIMIT $limit
";
	return do_query($query);
}

function get_post($id) {
	global $root;
	$query = "
	SELECT ?content ?author ?date
WHERE {
	<$id> rdf:type sioct:MicroblogPost ;
		sioc:content ?content ;
		foaf:maker ?author ;
		dct:created ?date .
} ";
	return do_query($query);
}

?>


