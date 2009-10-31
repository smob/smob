<?php

$ts= $_GET['ts'];

require_once(dirname(__FILE__).'/../lib/smob/client.php'); 

require_once(dirname(__FILE__)."/../config.php");

$query = "
SELECT count(?post) as ?c
WHERE {
	?post rdf:type sioct:MicroblogPost ;
		dct:created ?date .
FILTER (?date > \"$ts\")
}
";

$res = do_query("$query");
$news = $res[0]['c'];
if($news) {
	print "$news new update(s) since last time ! [<a href='.'>Reload</a>]";
}

?>