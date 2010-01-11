<?php

class DBPediaTagWrapper extends SMOBURIWrapper {
		
	function get_uris() {
		$uri = "http://lookup.dbpedia.org/api/search.asmx/KeywordSearch";
		$res = SMOBTools::do_curl($uri, "QueryString=".urlencode($this->item)."&QueryClass=all&MaxHits=10");
		$xml = simplexml_load_string($res[0]);
		foreach($xml->Result as $x) {
			// That shall work with an XML method 
			$vars = get_object_vars($x);
			$r[$vars['Label']] = $vars['URI'];
		}
		return $r;
	}
}

?>