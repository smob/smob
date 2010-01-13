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
	
	// Get the number of messages in that list
	private function count() {
		$pattern = $this->load_pattern();
		$query = "
SELECT COUNT(?post) as ?count
WHERE {
	?post rdf:type sioct:MicroblogPost .
	$pattern
} 
";	
		$count = SMOBStore::query($query);
		return $count[0]['count'];
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
		$curlimit = $this->page*$this->limit;
		$count = $this->count();
		if($count > $curlimit) {
			$previous = $page + 1;
			$older = "<a href='?p=$previous'>Older posts</a>";
		} 
		if($page > 1) {
			$next = $page - 1;
			$recent = "<a href='?p=$next'>More recent posts</a>";
		}
		if ($older && $recent) {
			return  "<div>$recent -- $older</div>";
		} elseif($recent) {
			return  "<div>$recent</div>";
		} elseif($older) {
			return  "<div>$older</div>";
		}
	} 

}
