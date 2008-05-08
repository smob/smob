<?php

include_once(dirname(__FILE__).'/config.php');

$store = ARC2::getStore($arc_config);
if (!$store->isSetUp()) {
  $store->setUp();
}

$data = $_GET['data'];
if($data) {
  $rs = $store->query("LOAD <$data>");
}

?>
