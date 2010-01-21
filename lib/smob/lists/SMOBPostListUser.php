<?php

class SMOBPostListUser extends SMOBPostList {

	public function title() {
		return 'Posts created by ' . $this->uri;
	}
	
	public function load_pattern() {
		$uri = $this->uri;
		return "
	?post foaf:maker <$uri> .";
	}

}
