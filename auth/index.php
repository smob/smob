<?php

require_once(dirname(__FILE__).'/../lib/smob/SMOBAuth.php'); 
require_once(dirname(__FILE__).'/../lib/smob/SMOBStore.php');
require_once(dirname(__FILE__).'/../lib/smob/SMOBTools.php'); 
require_once(dirname(__FILE__).'/../config/config.php'); 

error_log("going to authenticate");
SMOBAuth::grant();
error_log("authentication done");

if($_SESSION['grant'] && isset($_REQUEST['referer'])){
  error_log("has been authenticated and came from other page, going to the initial page");
  header("Location: ".$_REQUEST['referer']);
//if ($_SESSION['grant'] && $_GET["redirect"]) {
//  error_log("has been authenticated and came from other page, going to the initial page");
//  header("Location: ".SMOB_ROOT.$_GET["redirect"]);
} else {
  error_log("has no been authenticated or did not come from other page, going to the main page");
  header("Location: ".SMOB_ROOT);
}

?>
