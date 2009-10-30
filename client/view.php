<?php 

// SCRIPT_URI isn't present on all servers, so we do this instead:
$authority = "http://" . $_SERVER['HTTP_HOST'];
$root = $authority . dirname(dirname($_SERVER['SCRIPT_NAME'])); 

require_once(dirname(__FILE__).'/../lib/smob/client.php'); 

require_once(dirname(__FILE__)."/../config.php");

$uri = substr($_SERVER['PATH_INFO'], 1);
$title = "Info about $uri";
$content = show_uri($uri);

smob_go($title, $content);

?>
