<?php

require_once(dirname(__FILE__)."/../lib/smob/SMOB.php");
require_once(dirname(__FILE__)."/../config/config.php");
require_once(dirname(__FILE__)."/../lib/geonames/geonames.php");

// TODO: Check authentication to avoid hijacking
$content = $_GET['content'];
$location = $_GET['location'];
$twitter = $_GET['twitter'];
$mappings = $_GET['lod'];

if($content) {
	if(get_magic_quotes_gpc()) {
		$content = stripslashes($content);
	}

	$replies = array();
	if ($_GET['sioc:reply_of']) {
		$replies = $_GET['sioc:reply_of'];
	}

	$post = new SMOBPost();
	$post->set_data(date('c'), $content, $replies, $location, $mappings);
		
	print "<h2>Publishing your message...</h2>\n";
	print "<ul>\n";	
	print '<li>'.$post->save().'</li>';
	print '<li>'.$post->notify().'</li>';
	if($twitter) {
	//	print '<li>'.$post->tweet().'</li>';		
	}
	print "</ul>\n";	
}
