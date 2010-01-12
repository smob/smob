<?

/*
	The class responsible of all the actions towards the local triple-store
*/

class SMOBStore {
	
	var $store;
	
	function query($query) {
		global $arc_config;
		$store = ARC2::getStore($arc_config);
		if (!$store->isSetUp()) {
			$store->setUp();
		}
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
	PREFIX ctag: <http://commontag.org/ns#>
	PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

		$query";		
		
		$rs = $store->query($query);
		
		if ($errors = $store->getErrors()) {
			error_log("SMOB SPARQL Error:\n" . join("\n", $errors));
			return array();
		}
		
		return $rs['result']['rows'];
	}
	
	
}

?>