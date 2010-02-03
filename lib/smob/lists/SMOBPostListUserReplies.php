<?php

class SMOBPostListUserReplies extends SMOBPostList {

	public function title() {
		global $foaf_uri;
		return "Posts addressed to $foaf_uri";
	}
	
	public function load_pattern() {
		global $foaf_uri;
		return "
	?post sioc:addressed_to <$foaf_uri> .";
	}

}
