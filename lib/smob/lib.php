<?php

function &either()
{
	$arg_list = func_get_args();
	foreach($arg_list as $i => $arg)
		if ( $arg ) return $arg_list[$i];
	return NULL;
}

function get_store() {
	global $arc_config;
	$config = $arc_config + array(
	  'sem_html_formats' => 'rdfa' 
	);
	$store = ARC2::getStore($config);
	if (!$store->isSetUp()) {
		$store->setUp();
	}
	return $store;
}

function do_query($query) {
	$store = get_store();
	$query = "
PREFIX sioc: <http://rdfs.org/sioc/ns#>
PREFIX sioct: <http://rdfs.org/sioc/types#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX tags: <http://www.holygoat.co.uk/owl/redwood/0.1/tags/>
PREFIX moat: <http://moat-project.org/ns#>
PREFIX opo: <http://ggg.milanstankovic.org/opo/ns#>
PREFIX opo-actions: <http://ggg.milanstankovic.org/opo-actions/ns#>

	$query";
	$rs = $store->query($query);

	if ($errors = $store->getErrors()) {
		error_log("smob sparql error:\n" . join("\n", $errors));
		return array();
	}

	return $rs['result']['rows'];
}

function curl_get($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$response = curl_exec($ch);

	if ($error = curl_error($ch)) {
		return array("$error.", "", 0);
	}

	$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$status_line = substr($response, 0, strcspn($response, "\n\r"));
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$body = substr($response, $header_size);

	curl_close($ch);

	return array($body, $status_line, $status_code);
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

?>