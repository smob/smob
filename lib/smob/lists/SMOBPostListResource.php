<?php

class SMOBPostListPosts extends SMOBPostList {

	public function title() {
		return 'Posts linked to ' . $this->uri;
	}
	
	public function load_pattern() {
		return "
	?post rdf:type sioct:MicroblogPost ;
		sioc:content ?content ;
		foaf:maker ?author ;
		dct:created ?date .
	?tagging a tags:RestrictedTagging ;
		tags:taggedResource ?post ;
		moat:tagMeaning <$uri> .";
	}

/*	
	private function posts_poer_reousrce() {
				$uri = $this->uri;
			
				$posts = SMOBStore::query($query);
				foreach($posts as $post) {
					$uri = $post['post'];
					$this->process($post, $uri);
					$ht .= SMOBTemplate::post($post, '', $is_auth);
				}
				return $ht;
	}
	*/