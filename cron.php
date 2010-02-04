<?php

require_once(dirname(__FILE__).'/lib/smob/SMOB.php'); 
require_once(dirname(__FILE__)."/config/config.php");	

// Get tweets
$tweet = new SMOBTweet();
$tweet->getposts();

// Purge messages
SMOBTools::purge();


?>