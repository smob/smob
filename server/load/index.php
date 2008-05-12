<?php

include_once(dirname(__FILE__).'/../config.php');

$store = ARC2::getStore($arc_config);
if (!$store->isSetUp()) {
  $store->setUp();
}

$data = $_GET['data'];
if($data) {
  if($auth_key && (!$_GET['key'] || $_GET['key'] != $auth_key)) die(); 
  $rs = $store->query("LOAD <$data>");
}

?>
