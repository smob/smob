<?php

require_once(dirname(__FILE__)."/../../config.php");
require_once(dirname(__FILE__)."/../../lib/smob/client.php");

$authority = "http://" . $_SERVER['HTTP_HOST'];
$root = $authority . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); 

$uri = $_GET['uri'];
error_log("LOAD <$uri>");
do_query("LOAD <$uri>");
header("Location: " . get_view_uri($uri));

