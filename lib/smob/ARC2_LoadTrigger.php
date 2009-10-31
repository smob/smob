<?php

ARC2::inc('Class');

class ARC2_LoadTrigger extends ARC2_Class {

  function __construct($a = '', &$caller) {/* caller is a store */
    parent::__construct($a, $caller);
  }
  
  function ARC2_LoadTrigger($a = '', &$caller) {
    $this->__construct($a, $caller);
  }

  function __init() {
    parent::__init();
    $this->store = $this->caller;
  }

  function go() { /* automatically called by store or endpoint */
	$a = $this->a;
	$graph = $a['query_infos']['query']['target_graph'];
	$q = "
PREFIX foaf: <http://xmlns.com/foaf/0.1/>

SELECT ?p
WHERE {
	GRAPH <$graph> {
		?s foaf:maker ?p .
	} 
}";
	$res = $this->store->query($q);
	$author = $res['result']['rows'][0]['p'];
	if($author) {
		$res = $this->store->query("LOAD <$author>");
	}
	return;
	
  }

}