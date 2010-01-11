<?php

class GeonamesLocationWrapper extends SMOBURIWrapper {
	
	function get_uris() {
		$uri = "http://ws.geonames.org/searchJSON?q=".urlencode($this->item)."&maxRows=10";
		$res = SMOBTools::do_curl($uri);
		$json = json_decode($res[1], true);
		foreach($json['geonames'] as $j) {
			$uri= "http://sws.geonames.org/" . $j['geonameId'] . "/";
			$name = $j['name'] .'('. $j['countryName'] . ')';
			$r[$name] = $uri;
		}
		return $r;
	}
}

?>

