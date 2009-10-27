<?php

include_once(dirname(__FILE__).'/../../config.php');

$config = $arc_config + array(
  'sem_html_formats' => 'rdfa' // From HTML documents, only load RDFa triples
);

$store = ARC2::getStore($config);
if (!$store->isSetUp()) {
  $store->setUp();
}

$key = $_GET['key'];
$data = $_GET['data'];

if ($key != $auth_key) {
  header("HTTP/1.0 403 Forbidden");
  header("Content-Type: text/html"); 
  print "No correct API key given!\n";
  exit;
}

if (!$data) {
  header("HTTP/1.0 404 Not found");
  header("Content-Type: text/html"); 
  print "No data URI to load given!\n";
  exit;
}

$store->query("DELETE FROM <$data>"); // delete any old data first
$rs = $store->query("LOAD <$data> INTO <$data>");

if ($errs = $store->getErrors()) {
  header("HTTP/1.0 500 Internal Server Error");
  header("Content-Type: text/html"); 
  print "Failed to load data:\n<ul>\n";
  foreach ($errs as $err)
    print "<li>" . htmlspecialchars($err) . "</li>\n";
  print "</ul>\n";
  exit;
}

if (!$rs['result']['t_count']) {
  header("HTTP/1.0 500 Internal Server Error");
  header("Content-Type: text/html"); 
  print "Couldn't extract any RDF information!\n";
  exit;
}

print "Done.\n";

?>
