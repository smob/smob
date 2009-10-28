<?php

function get_store() {
	global $arc_config;
	$config = $arc_config + array(
	  'sem_html_formats' => 'rdfa' 
	);
	return ARC2::getStore($config);
}

function do_query($query) {
	$store = get_store();
	$query = "
PREFIX sioc: <http://rdfs.org/sioc/ns#>
PREFIX sioct: <http://rdfs.org/sioc/types#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX dct: <http://purl.org/dc/terms/>
	$query";
	$rs = $store->query($query);
	return $rs['result']['rows'];
}

?>