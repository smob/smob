<?php

require_once(dirname(__FILE__).'/../lib/smob/client.php'); 
require_once(dirname(__FILE__)."/../config.php");

$config = $arc_config + array(
  'endpoint_features' => array(
    'select', 'construct', 'ask', 'describe', 
    'load', 'insert', 'delete'
  ),
  'endpoint_read_key' => '',
  'endpoint_write_key' => 'somekey', 
);

$ep = ARC2::getStoreEndpoint($config);

$ep->go();

?>
