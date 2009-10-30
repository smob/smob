<?php

require_once(dirname(__FILE__).'/../lib/smob/client.php'); 
require_once(dirname(__FILE__)."/../config.php");

$config = $arc_config + array(
  
);

$ep = ARC2::getStoreEndpoint($config);

$ep->go();

?>
