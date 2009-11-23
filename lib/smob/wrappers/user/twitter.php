<?php

// Wrappers for users in Sindice

class TwitterUserWrapper extends SMOBURIWrapper {
	
	function get_uri() {
		return array($this->item => get_uri_if_found("http://twitter.com/".$this->item));
	}
		
}


?>