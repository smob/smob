<?php

require_once(dirname(__FILE__)."/../../config.php");
require_once(dirname(__FILE__)."/../../lib/smob/client.php");

require_once(dirname(__FILE__)."/pub.php");

$authority = "http://" . $_SERVER['HTTP_HOST'];
$root = $authority . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); 

// Do sanity check over the following URI to ensure it exists, etc.
$remote = $_GET['uri'];
$remote_user = "{$remote}owner";

$local_user = user_uri();
$follow = "<$local_user> sioc:follows <$remote_user> . ";

$local = "INSERT INTO <$root/data/followers> { $follow }";
do_query($local);

$ping = "{$remote}ping/follower/";
$post = 'follower='.urlencode($local_user);
do_curl_post($ping, $pos);

