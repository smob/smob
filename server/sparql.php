<?php

include_once(dirname(__FILE__).'/../config.php');

/* endpoint configuration */ 
$config = $arc_config + array(
  /* endpoint */
  'endpoint_features' => array(
    'select', 'construct', 'ask', 'describe', 
//    'load', 'insert', 'delete'
  ),
  'endpoint_timeout' => 60, /* not implemented in ARC2 preview */
  'endpoint_read_key' => '', /* optional */
  'endpoint_write_key' => 'somekey', /* optional */
//  'endpoint_max_limit' => 250, /* optional */
);

/* instantiation */
$ep = ARC2::getStoreEndpoint($config);

if (!$ep->isSetUp()) {
  $ep->setUp(); /* create MySQL tables */
}

/* request handling */
$ep->go();

?>

