<?php

include_once(dirname(__FILE__).'/../config.php');

$store = ARC2::getStore($arc_config);
if (!$store->isSetUp()) {
  $store->setUp();
}

$data = $_GET['data'];
if ($data) {
  if ($_GET['key'] != $auth_key) {
    header("HTTP/1.0 403 Forbidden");
    header("Content-Type: text/html"); 
    print "No correct API key given!\n";
    exit;
  }
  $rs = $store->query("LOAD <$data>");
} else {
  header("HTTP/1.0 404 Not found");
  header("Content-Type: text/html"); 
  print "No data URI to load given!\n";
  exit;
}

if ($errs = $store->getErrors()) {
  header("HTTP/1.0 500 Internal Server Error");
  header("Content-Type: text/html"); 
  print "<p>The following operation failed: ";
  print htmlspecialchars("LOAD <$data>\n\n");
  print "\n\n<ul>\n";
  foreach ($errs as $err)
    print "<li>" . htmlspecialchars($err) . "</li>\n";
  print "</ul>";
  exit;
}

print "Done."

?>
