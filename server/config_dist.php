<?php

include_once(dirname(__FILE__).'/lib/ARC2/ARC2.php');

// SQL database configuration for storing the postings:
$arc_config = array(
  'db_host' => 'localhost', /* optional, default is localhost */
  'db_name' => 'smob',
  'db_user' => '****',
  'db_pwd' => '****',
  'store_name' => 'smob',
);

// Global authentication API key - set this to something hard to guess if
// you want to stop publishers getting their postings aggregated unless
// they provide it too.
$auth_key = '';

// Google Maps API key from http://code.google.com/apis/maps/signup.html
$gmap_key = '';

?>
