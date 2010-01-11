<?php

require_once(dirname(__FILE__).'/../lib/smob/SMOBAuth.php'); 
require_once(dirname(__FILE__).'/../config/config.php'); 

SMOBAuth::grant();

header("Location: $smob_root");

?>
