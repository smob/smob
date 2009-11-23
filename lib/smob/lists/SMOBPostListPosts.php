<?php

class SMOBPostListPosts extends SMOBPostList {

	public function title() {
		return 'Public timeline';
	}
	
	public function load_pattern() {
		return "
	?post rdf:type sioct:MicroblogPost ;
		sioc:content ?content ;
		foaf:maker ?author ;
		dct:created ?date .";
	}

}