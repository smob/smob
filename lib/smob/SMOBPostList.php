<?php

/*
 	Create posts lists
*/

require_once(dirname(__FILE__).'/lists/SMOBPostListPosts.php');
require_once(dirname(__FILE__).'/lists/SMOBPostListResource.php');
require_once(dirname(__FILE__).'/lists/SMOBPostListUser.php');

class SMOBPostList {
	
	var $posts;
	var $page;
	var $limit = 20;
	
	public function __construct($uri, $page) {
		$this->uri = $uri;
		$this->page = $page;
		$this->process();
	}
	
	public function process() {
		$limit = $this->limit;
		$start = ($this->page-1)*$limit;
		// The load_pattern() function must be defined in the inherited classes
		$pattern = $this->load_pattern();
		// Weird ARC2 bug iw adding ?creator in the following varlist !
		$query = "
SELECT DISTINCT ?post ?content ?author ?date ?presence ?reply_of ?reply_of_of ?depiction ?name ?location ?locname 
WHERE {
	?post rdf:type sioct:MicroblogPost ;
		sioc:content ?content ;
		foaf:maker ?author ;
		dct:created ?date .
	?presence opo:customMessage ?post .
	$pattern
	OPTIONAL { ?post sioc:reply_of ?reply_of. }
	OPTIONAL { ?reply_of_of sioc:reply_of ?post . }
	OPTIONAL { ?author foaf:depiction ?depiction . } 
	OPTIONAL { ?author foaf:img ?depiction . }
	OPTIONAL { ?author foaf:name ?name . }
	OPTIONAL {
		?presence opo:currentLocation ?location .
		?location rdfs:label ?locname .
	}
} 
ORDER BY DESC(?date) OFFSET $start LIMIT $limit
";	
		$posts = SMOBStore::query($query);
		$uris = array();
		foreach($posts as $post) {
			$uri = $post['post'];
			if(!in_array($uri, $uris)) {
				$this->posts[] = new SMOBPost($uri, $post);
				$uris[] = $uri;
			}
		}
		return;		
	}
		
	public function render() {
		// The title() function must be defined in the inherited classes
		$ts = date('c');
		$ht = '<h2>'.$this->title().'</h2>';
		$ht .= "<div id=\"ts\" style=\"display:none;\">$ts</div><div id=\"news\"></div>";
		if($this->posts) {
			foreach($this->posts as $post) {
				$ht .= $post->render();
			}
		}
		$ht .= $this->pager();
		return $ht;
	}

	function pager() {
		$page = $this->page;
		if(!$page || $page == 1) {
			return "<div><a href='?p=2'>Previous posts</a></div>";
		} else {
			$previous = $page + 1;
			$next = $page - 1;
			return "<div><a href='?p=$next'>Next posts</a> -- <a href='?p=$previous'>Previous posts</a></div>";
		}
	}

}
