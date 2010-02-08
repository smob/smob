<?php

require_once(dirname(__FILE__).'/lib/smob/SMOB.php'); 
require_once(dirname(__FILE__)."/config/config.php");	

// Get tweets
if(TWITTER_READ) {
	echo 'on lit twitter';
	$tweet = new SMOBTweet();
	$tweet->getposts();
}

// Purge messages
if(PURGE > 0) {
	SMOBTools::purge(PURGE);
}

?>