<?php

require_once(dirname(__FILE__).'/../lib/smob/SMOB.php'); 
require_once(dirname(__FILE__)."/../config/config.php");

$np = $_GET['np'];
if(!$np) die();

$query = "
SELECT count(?post) as ?c
WHERE {
	?post rdf:type sioct:MicroblogPost .
}
";

$res = SMOBStore::query($query);
$num = $res[0]['c'];
if($num > $np) {
	$diff = $num - $np;
	print "$diff new update(s) since last time ! [<a href='.'>Reload</a>]";
}

?>
