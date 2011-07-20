<?php

class DBPediaTagWrapper extends SMOBURIWrapper {
		
	function get_uris() {
		$uri = "http://lookup.dbpedia.org/api/search.asmx/KeywordSearch?QueryString=".urlencode($this->item)."&QueryClass=&MaxHits=10";
		$res = SMOBTools::do_curl($uri,null, null, 'GET');
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
