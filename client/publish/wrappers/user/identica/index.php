<?php

// Wrappers for users in Sindice

class IdenticaUserWrapper extends SMOBURIWrapper {
	
	function get_uri() {
		return array($this->item => get_uri_if_found("http://identi.ca/".$this->item));
	}
		
}


?>