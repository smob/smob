<?php

require_once(dirname(__FILE__)."/../arc/ARC2.php");
require_once(dirname(__FILE__)."/lib.php");
require_once(dirname(__FILE__)."/template.php");

function get_root() {
	$authority = "http://" . $_SERVER['HTTP_HOST'];
	return $authority . dirname(dirname($_SERVER['SCRIPT_NAME']));
}	

function get_uri_from_request_path() {
	$path = $_SERVER['REQUEST_URI'];
	$script = $_SERVER['SCRIPT_NAME'];
	for($common = 0; $common < min(strlen($path), strlen($script)); $common++) {
		if ($path[$common]  != $script[$common])
			break;
	}
	return urldecode(substr($path, $common+1));
}

function get_view_uri($uri) {
	global $root;
	$uri = urlencode($uri);
	$uri = str_replace("%2F", "/", $uri);
	return "$root/client/view/$uri";
}

function get_publish_uri($reply_of = NULL) {
	global $root;
	$uri = "$root/client/publish/";
	if ($reply_of)
		$uri .= "?sioc:reply_of=" . urlencode($reply_of);
	return $uri;
}

function smob_go($content) {
	global $root;
	smob_header();
	print $content;
	$n = get_networks();
	$n .= "
<h2>Navigation</h2>
<ul>
<li><a href='$root/client'>Home</a></li>
";
if(is_auth()) $n .= "<li><a href='$root/client/publish'>Publish</a></li>";
$n .= "
</ul>";
	smob_footer($n);	
}

function get_networks() {
	global $servers, $twitter_user;
	$ht = "<h2>Networks</h2>\n\n";
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
	$is_auth = is_auth();
	foreach($posts as $post) {
		$ht .= do_post($post, '', $is_auth);
	}
	return $ht;
}

function do_post($post, $uri = null, $is_auth = false) {
	global $sioc_nick, $root;
	if(!$uri) {
		$uri = $post['post'];		
	}
	$ocontent = $content = $post['content'];
	$author = $post['author'];
	$date = $post['date'];
	$reply_of = $post['reply_of'];
	$reply_of_of = $post['reply_of_of'];
	$pic = either($post['depiction'], $post['img'], "$root/img/avatar-blank.jpg");
	// Find the topics
	$ht .= "<div class=\"post\" typeof=\"sioct:MicroblogPost\" about=\"$uri\">\n";
	$ht .= "<img src=\"$pic\" class=\"depiction\" alt=\"Depiction for $foaf_uri\"/>";
	$users = get_users($uri);
	if($users) {
		foreach($users as $t) {
			$user = $t['user'];
			$name = $t['name'];
			$enc = "<a class=\"topic\" rel=\"sioc:topic\" href=\"$user\"><a href=\"$enc\">@$name</a></a>";
			$content = str_replace("@$name", $r, $content);
		}
	}
	$tags = get_tags($uri);
	if($tags) {
		foreach($tags as $t) {
			$tag = $t['tag'];
			$resource = $t['uri'];
			$enc = get_view_uri($uri);
			$r = "<span class=\"topic\" rel=\"sioc:topic\" href=\"$resource\"><a href=\"$enc\">#$tag</a></span>";
			$content = str_replace("#$tag", $r, $content);
		}
	}
	$enc = get_view_uri($author);
	$ht .= "  <span class=\"content\">$content</span>\n";
	$ht .= "  <span style=\"display:none;\" property=\"sioc:content\">$ocontent</span>\n";
	$ht .= "  (by <span class=\"author\" rel=\"foaf:maker\" href=\"$author\"><a href=\"$enc\">$sioc_nick</a></span> - \n";
	$ht .= "  <span class=\"date\" property=\"dcterms:created\">$date</span>)\n";
	$ht .= "<br />";
	$ht .= " [<a href=\"$uri\">Permalink</a>]\n";
	$enc2 = get_publish_uri($uri);
	if($is_auth) {
		$ht .= " [<a href=\"$enc2\">Post a reply</a>]\n";
	}
	if ($reply_of) {
		$enc3 = get_view_uri($reply_of);
		$ht .= " [<a href=\"$enc3\">Parent</a>]\n";
	}
	if ($reply_of_of) {
		$enc4 = get_view_uri($reply_of_of);
		$ht .= " [<a href=\"$enc4\">Child</a>]\n";
	}
	$ht .= "</div>\n\n";
	return $ht;
}

function show_post($id) {
	global $root;
	$uri = "$root/client/post/" . str_replace(' ', '+', $id);
	return show_uri($uri);
}

function show_uri($uri) {
	$p = get_post($uri);
	if ($p)
		return do_post($p[0], $uri);

	$p = get_person($uri);
	if ($p)
		return do_person($p, $uri);

	# TODO add same for other resource types here
	return "Error: Don't know how to show URI: <a href=\"$uri\">$uri</a>";
}

function show_posts($page = 0) {
	$limit = 20;
	$offset = $page * $limit;
	$posts = get_posts($offset, $limit);
	return "<h1>Public timeline</h1>\n\n" . show_postss($posts ) . pager($page);
}

function do_person($person, $uri) {
	global $root;
	$names = either($person['names'], array("Anonymous"));
	$imgs = either($person['images'], array("$root/img/avatar-blank.jpg"));
	$homepage = $person['homepage'];
	$weblog = $person['weblog'];
	$knows = $person['knows'];

	$name = $names[0];
	$pic = $imgs[0];

	$ht = "<div class=\"person\">\n";
	$ht .= "<img src=\"$pic\" class=\"depiction\" alt=\"Depiction\" />";
	$ht .= "$name\n";

	foreach ($homepage as $h) {
		$ht .= " [<a href=\"$h\">Website</a>]\n";
	}
	foreach ($weblog as $w) {
		$ht .= " [<a href=\"$w\">Blog</a>]\n";
	}
	foreach ($knows as $k) {
		$enc = get_view_uri($k);
		$ht .= " [<a href=\"$enc\">Friend</a>]\n";
	}

	$ht .= "</div>\n\n";
	return $ht;
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
	SELECT *
WHERE {
	?post rdf:type sioct:MicroblogPost ;
		sioc:content ?content ;
		foaf:maker ?author ;
		dct:created ?date .
	OPTIONAL { ?post sioc:reply_of ?reply_of. }
	OPTIONAL { ?reply_of_of sioc:reply_of ?post . }
	OPTIONAL { ?author foaf:depiction ?depiction. }
	OPTIONAL { ?author foaf:img ?img . }
} 
ORDER BY DESC(?date)
OFFSET $start
LIMIT $limit
";
	return do_query($query);
}

function get_post($id) {
	$query = "
	SELECT *
WHERE {
	<$id> rdf:type sioct:MicroblogPost ;
		sioc:content ?content ;
		foaf:maker ?author ;
		dct:created ?date .
	OPTIONAL { <$id> sioc:reply_of ?reply_of. }
	OPTIONAL { ?reply_of_of sioc:reply_of <$id> . }
	OPTIONAL { ?author foaf:depiction ?depiction. }
	OPTIONAL { ?author foaf:img ?img . }
} ";
	return do_query($query);
}

function optionals($subj, $props) {
	$r = "";
	foreach ($props as $p) {
		$name = substr($p, stripos($p, ":")+1);
		$r .= "UNION { <$subj> $p ?$name . }\n";
	}
	return $r;
}

function optionals_to_array_of_arrays($all, $rs) {
	$r = array();
	foreach ($all as $name) {
		$name = substr($name, stripos($name, ":")+1);
		$r[$name] = array();
	}
	foreach ($rs as $row) {
		foreach ($all as $name) {
			$name = substr($name, stripos($name, ":")+1);
			if ($row[$name])
				$r[$name][] = $row[$name];
		}
	}
	return $r;
}

function choose_optional($names, $rs) {
	foreach ($names as $name) {
		$name = substr($name, stripos($name, ":")+1);
		if ($rs[$name])
			return $rs[$name];
	}
	return array();
}

function get_person($uri) {
	$names = explode(" ", "foaf:name foaf:firstName foaf:nick rdfs:label");
	$images = explode(" ", "foaf:depiction foaf:img");
	$misc = explode(" ", "foaf:homepage foaf:weblog foaf:knows");
	$all = array_merge($names, $images, $misc);
	$optionals = optionals($uri, $all);
	$query = "
SELECT *
WHERE {
{ <$uri> rdf:type foaf:Person . }
$optionals
} ";
	$rs = do_query($query);
	if (!$rs)
		return $rs;
	$rs = optionals_to_array_of_arrays($all, $rs);
	$rs['names'] = choose_optional($names, $rs);
	$rs['images'] = choose_optional($images, $rs);

	return $rs;
}

