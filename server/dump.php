<?php

include_once(dirname(__FILE__).'/../config.php');
include_once(dirname(__FILE__).'/../lib/smob/lib.php');

$q = "
select distinct ?g ?s ?p ?o
where { 
  graph ?g {?s ?p ?o}
}
";

$rs = do_query($q);

// var_dump($rs);

$graphs = array();

foreach ($rs as $row) {
  // import the bindings of the result row as php variables
  foreach ($row as $k => $v) {
    $kn = 'row_' . str_replace(' ', '_', $k);
    $$kn = $v;
  }

  $triple = array($row_s, $row_p, $row_o);
  if ($graphs[$row_g])
    $graphs[$row_g][] = $triple;
  else
    $graphs[$row_g] = array($triple);
}

print "<h1>HTML dump of the documents aggregated</h1>\n";

foreach ($graphs as $g => $triples) {
  print "<h2>$g</h2>\n<table border=1>\n";
  array_multisort($triples);

  foreach ($triples as $triple) {
    echo "<tr><td>$triple[0]</td><td>$triple[1]</td><td>$triple[2]</td></tr>\n";
  }
  print "</table>\n";
}
?>
