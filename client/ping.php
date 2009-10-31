<?php

parse_str($_SERVER['QUERY_STRING']);
$u = str_replace('http:/', 'http://', $u);

require_once(dirname(__FILE__)."/../config.php");
require_once(dirname(__FILE__)."/../lib/smob/lib.php");

if($t == 'follower') {
	$authority = "http://" . $_SERVER['HTTP_HOST'];
	$root = $authority . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); 

	// Do sanity check over the following URI to ensure it exists, etc.
	$remote_user = $u;
	$local_user = user_uri();
	$follow = "<$remote_user> sioc:follows <$local_user> . ";	
	$local = "INSERT INTO <$root/data/followers> { $follow }";
	echo $local;
	do_query($local);
	
}