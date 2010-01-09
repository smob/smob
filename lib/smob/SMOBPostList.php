<?php

/*
 	Create posts lists
*/

require_once(dirname(__FILE__).'/lists/SMOBPostListPosts.php');

class SMOBPostList {
	
	var $posts;
	var $page;
	var $limit = 20;
	
	public function __construct($uri, $page) {
		$this->page = $page;
		$this->process();
	}
	
	public function process() {
		$limit = $this->limit;
		$start = ($this->page-1)*$limit;
		// The load_pattern() function must be defined in the inherited classes
		$pattern = $this->load_pattern();
		$query = "
SELECT DISTINCT ?post ?content ?author ?date ?reply_of ?reply_of_of ?depiction
WHERE {
	$pattern
	OPTIONAL { ?post sioc:reply_of ?reply_of. }
	OPTIONAL { ?reply_of_of sioc:reply_of ?post . }
	OPTIONAL { 
		{ ?author foaf:depiction ?depiction. } UNION { ?author foaf:img ?depiction . }
	}
} 
ORDER BY DESC(?date)
OFFSET $start
LIMIT $limit
";	
		$posts = SMOBStore::query($query);
		foreach($posts as $post) {
			$uri = $post['post'];
			$this->posts[] = new SMOBPost($uri, $post);
		}
		return;		
	}
		
	public function render() {
		// The title() function must be defined in the inherited classes
		$ts = date('c');
		$ht = '<h1>'.$this->title().'</h1>';
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
		if($page == 0) {
			return "<div><a href='?page=1'>Previous posts</a></div>";
		} else {
			$previous = $page + 1;
			$next = $page - 1;
			return "<div><a href='?page=$next'>Next posts</a> -- <a href='?page=$previous'>Previous posts</a></div>";
		}
	}

}
