<?php

class SMOBPostListResource extends SMOBPostList {

	public function title() {
		return 'Posts linked to ' . $this->uri;
	}
		
	public function load_pattern() {
		$uri = $this->uri;
		return "
	?post moat:taggedWith <$uri> .";
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
	
}