<?php

require_once(dirname(__FILE__).'/lib/smob/SMOB.php'); 
require_once(dirname(__FILE__)."/config/config.php");	

$tweet = new SMOBTweet();
$tweet->getposts();

?>