<?php

require_once(dirname(__FILE__).'/../lib/smob/SMOB.php'); 
require_once(dirname(__FILE__)."/../config/config.php");

$uri = str_replace(' ', '+', $_GET['u']);
if(!$uri) die();

$pattern = "{ <$uri> rev:rating \"1\"^^xsd:integer . }";

$star = SMOBStore::query("ASK $pattern", true);

$query = ($star ? 'DELETE FROM' : 'INSERT INTO') . " <".SMOB_ROOT."data/stars> $pattern";

echo $query;

SMOBStore::query($query);

