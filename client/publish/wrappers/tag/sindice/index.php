<?php

class SindiceTagWrapper extends SMOBURIWrapper {
	
	function get_uri() {
		$uri = "http://api.sindice.com/v2/search?page=1&q=".urlencode($this->item)."&qt=term&format=json";
		$res = curl_get($uri);
		$json = json_decode($res[0], true);
		foreach($json['entries'] as $j) {
			$r[$j['title'][0]] = $j['link'];
		}
		return $r;
	}
}


?>