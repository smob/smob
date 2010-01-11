<?php

require_once(dirname(__FILE__)."/../lib/smob/SMOB.php");
require_once(dirname(__FILE__)."/../config/config.php");

// TODO: Check authentication to avoid hijacking
$content = $_GET['content'];
$location = $_GET['location'];
$location_uri = $_GET['location_uri'];
$twitter = $_GET['twitter'];
$mappings = $_GET['lod'];
$reply_of = $_GET['reply_of'];

if($content) {
	if(get_magic_quotes_gpc()) {
		$content = stripslashes($content);
	}

	$post = new SMOBPost();
	$post->set_data(date('c'), $content, $reply_of, $location, $location_uri, $mappings);
	
	print "<h2>Publishing your message...</h2>\n";
	print "<ul>\n";	
	$post->save();
	$post->notify();
	if($twitter) {
		print '<li>'.$post->tweet().'</li>';		
	}
	print "</ul>\n";	
}
