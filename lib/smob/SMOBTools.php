<?

/* 
	Helper methods for the different SMOB Classes
*/

class SMOBTools {
	
	// Get current location
	public function location() {
		$query = "
SELECT DISTINCT ?location ?name WHERE {
?presence opo:currentLocation ?location ;
	opo:StartTime ?time .
?location rdfs:label ?name
}
ORDER BY DESC(?time)
LIMIT 1";		
		$res = SMOBStore::query($query);
		$loc = $res[0]['location'];
		$locname = $res[0]['name'];
		return array($loc, $locname);
	}
	
	// List of followers
	function followers() {
		$pattern = '?uri sioc:follows <' . SMOBTools::user_uri() . '>';
		return SMOBTools::people('followers', $pattern);
	}

	function following() {
		$pattern = '<' . SMOBTools::user_uri() . '> sioc:follows ?uri';
		return SMOBTools::people('following', $pattern);
	}
	
	function people($type, $pattern) {
		$query = "SELECT * WHERE { $pattern }";
		return SMOBStore::query($query);
	}
	
	function &either() {
		$arg_list = func_get_args();
		foreach($arg_list as $i => $arg) {
			if ($arg) {
				return $arg_list[$i];
			}
		}
		return null;
	}
	
	function do_curl($url, $postfields = null, $userpwd = null) {
		$ch = curl_init(POSTURL);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		if($postfields) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		}
		if ($userpwd) {
			curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
		}
		
		$response = curl_exec($ch);
		if ($error = curl_error($ch)) {
			return array("$error.", "", 0);
		}

		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$status_line = substr($response, 0, strcspn($response, "\n\r"));
		curl_close($ch);

		return array($response, $status_line, $status_code);
	}
	

	function get_uri_if_found($uri) {
		list ($resp, $status, $code) = curl_get($uri);
		if ($code == 200)
			return $uri;
		else if ($code == 404)
			return "";
		else {
			print "Error fetching $uri: ";
			if ($status)
				print $status;
			else
				print $resp;
	    	return "";
		}
	}

	// TODO : A Dedicated SMOBAuth module
	function is_auth() {
		require_once(dirname(__FILE__).'/../foaf-ssl/libAuthentication.php');
		// need cookies here 
		global $foaf_ssl, $foaf_uri;
		if($foaf_ssl) {
			session_start();
			if($_COOKIE['auth']==1) {
				return true;
			}
			$auth = getAuth();
			$do_auth = $auth['certRSAKey'];
			$is_auth = $auth['isAuthenticated'];
			$auth_uri = $auth['subjectAltName'];
			if ($is_auth == 1) {
				setcookie("uri", "$auth_uri");
				setcookie("auth", "1");
				if ($auth_uri == $foaf_uri) {
					return true;
				}			
			}
		}
		return false;
	}

	function user_uri() {
		return SMOBTools::get_uri('owner', 'user');
	}
	
	function get_uri($uri, $type) {
		global $smob_root;
		$uri = urlencode($uri);
		$uri = str_replace("%2F", "/", $uri);
		return "${smob_root}$type/$uri";
	}
	
	function get_post_uri($uri) {
		global $smob_root;
		$uri = str_replace(' ', '+', $uri);
		return "${smob_root}post/$uri";
	}
	
	
	function optionals($subj, $props) {
		$r = "";
		foreach ($props as $p) {
			$name = substr($p, stripos($p, ":")+1);
			$r .= "UNION { <$subj> $p ?$name . }\n";
		}
		return $r;
	}

	function optionals_to_array_of_arrays($all, $rs) {
		$r = array();
		foreach ($all as $name) {
			$name = substr($name, stripos($name, ":")+1);
			$r[$name] = array();
		}
		foreach ($rs as $row) {
			foreach ($all as $name) {
				$name = substr($name, stripos($name, ":")+1);
				if ($row[$name])
					$r[$name][] = $row[$name];
			}
		}
		return $r;
	}

	function choose_optional($names, $rs) {
		foreach ($names as $name) {
			$name = substr($name, stripos($name, ":")+1);
			if ($rs[$name])
				return $rs[$name];
		}
		return array();
	}
	
	function check_config() {
		return file_exists(dirname(__FILE__)."/../../config/config.php");
	}
	
	function render_sparql_triple($triple) {
		return implode(" ", $triple);
	}

	function render_sparql_triples($triples) {
		if (!$triples)
			return "";
		$r = SMOBTools::render_sparql_triple($triples[0]);
		$i = 1;
		while ($i < count($triples)) {
			if (count($triples[$i]) == 1)
				$r .= " ,\n";
			else if (count($triples[$i]) == 2)
				$r .= " ;\n";
			else
				$r .= " .\n";
			$r .= SMOBTools::render_sparql_triple($triples[$i]);
			$i += 1;
		}
		$r .= " .";
		return $r;
	}
	
	# XXX maybe one day, someone writes the proper escaping functions for PHP...
	function uri($uri) {
		return "<" . $uri . ">";
	}

	function literal($literal) {
		return '"' . addslashes($literal) . '"';
	}
	
}

?>
