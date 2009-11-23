<?php

class SindiceTagWrapper extends SMOBURIWrapper {
	
	function get_uris() {
		$uri = "http://api.sindice.com/v2/search?page=1&q=".urlencode($this->item)."&qt=term&format=json";
		$res = SMOBTools::do_curl($uri);
		$json = json_decode($res[1], true);
		foreach($json['entries'] as $j) {
			$r[$j['title'][0]] = $j['link'];
		}
		return $r;
	}
}

?>