<?php

require_once(dirname(__FILE__).'/../lib/smob/SMOB.php'); 
require_once(dirname(__FILE__)."/../config/config.php");

$ts = $_GET['ts'];

$query = "
SELECT count(?post) as ?c
WHERE {
	?post rdf:type sioct:MicroblogPost ;
		dct:created ?date .
FILTER (?date > \"$ts\")
}
";

$res = SMOBStore::query($query);
$news = $res[0]['c'];
if($news) {
	print "$news new update(s) since last time ! [<a href='.'>Reload</a>]";
}


?>