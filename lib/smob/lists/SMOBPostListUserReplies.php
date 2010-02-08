<?php

class SMOBPostListUserReplies extends SMOBPostList {

	public function title() {
		return "Posts addressed to ". FOAF_URI;
	}
	
	public function load_pattern() {
		return "
	?post sioc:addressed_to " . FOAF_URI . '.';
	}

}
