<?php

// SCRIPT_URI isn't present on all servers, so we do this instead:
// $authority = "http://" . $_SERVER['HTTP_HOST'];
// $root = $authority . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); 

// require_once(dirname(__FILE__).'/../../config.php');
// require_once(dirname(__FILE__).'/../../lib/smob/client.php'); 
// require_once(dirname(__FILE__).'/../../lib/foaf-ssl/libAuthentication.php');

// authentication = TODO

/*
$auth = getAuth();
$do_auth = $auth['certRSAKey'];
$is_auth = $auth['isAuthenticated'];
$auth_uri = $auth['subjectAltName'];

if($do_auth) {
	if ($is_auth != 1 || $auth_uri != $foaf_uri) {
		print "Wrong credentials, try again !";
		die();
	} else {
		print "Welcome home, $auth_uri !";
	}
}

$reply_of = $_GET['sioc:reply_of'];
$location = $_GET['location'];

*/

/* DO NOT NEED THAT URI ANYMORE !!! EVERYTHNG GOES INTO THE HOMEPAGE */

/*
// SCRIPT_URI isn't present on all servers, so we do this instead:
$authority = "http://" . $_SERVER['HTTP_HOST'];
$root = $authority . dirname(dirname($_SERVER['SCRIPT_NAME'])); 

require_once(dirname(__FILE__).'/../../lib/smob/SMOB.php'); 

if(!file_exists(dirname(__FILE__)."/../../config.php")) {
	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'../install';
	header("Location: $url");
} 

require_once(dirname(__FILE__)."/../../config.php");

//is_auth();

parse_str($_SERVER['QUERY_STRING']);
$u = str_replace('http:/', 'http://', $u);

$t = 'publish';

$smob = new SMOB($t, $u, $p);
$smob->go();

*/

?>
