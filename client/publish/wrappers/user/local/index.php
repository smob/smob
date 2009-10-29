<?php

// Wrappers for users in the local store

class LocalUserWrapper extends SMOBURIWrapper {

	function get_uri() {
		$rs = do_query("
	SELECT DISTINCT ?person ?user WHERE {
	  ?post rdf:type sioct:MicroblogPost .
	  ?post sioc:has_creator ?user .
	  ?post foaf:maker ?person .
	  ?person foaf:nick '$user' .
	}
		");
		if ($rs) {
			foreach($rs as $row) {
				$r[$row['person']] = $row['user'];
			}
		}
		return $r;
	}
}
