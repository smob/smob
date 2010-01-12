<?php

// Wrappers for users in Sindice

class TwitterUserWrapper extends SMOBURIWrapper {
	
	function get_uris() {
		return array($this->item => SMOBTools::get_uri_if_found("http://twitter.com/".$this->item));
	}
		
}

?>