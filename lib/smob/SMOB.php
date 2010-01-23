<?

/* 
	The main class - controls the action, launches all the stuff
*/

require_once(dirname(__FILE__).'/SMOBAuth.php');
require_once(dirname(__FILE__).'/SMOBInstaller.php');
require_once(dirname(__FILE__).'/SMOBPost.php');
require_once(dirname(__FILE__).'/SMOBPostList.php');
require_once(dirname(__FILE__).'/SMOBStore.php');
require_once(dirname(__FILE__).'/SMOBTemplate.php');
require_once(dirname(__FILE__).'/SMOBTools.php');
require_once(dirname(__FILE__).'/SMOBURIWrapper.php');

class SMOB {
	
	var $type = 'posts';
	var $page = 1;
	var $uri;
	var $publisher;
	var $reply_of;
	var $commands = array('data', 'followings', 'followers', 'post', 'posts', 'replies', 'resource', 'user');
	
	// Construct - save parameters and setup the RDF store
	public function __construct($type, $uri, $page) {
		if($type) {
			$this->type = $type;
		}
		if($uri) {
			$uri = str_replace('http:/', 'http://', $uri);
			$this->uri = $uri;	
		}
		if($page) {
			$this->page = $page;
		}
		$this->publish = SMOBAuth::check();
	}
	
	// Setup the reply_of elemnents
	public function reply_of($reply_of) {
		$this->reply_of = $reply_of;
	}
	
	// Main method - analyse the query type, get the content and render it
	public function go() {
		if(in_array($this->type, $this->commands)) {
			$func = $this->type;
			$content = $this->$func();
		} else {
			$content = "Cannot interpret that command";
		}
		// Passer ce publish parametre dans une list particuliere
		SMOBTemplate::header($this->publish, $this->reply_of);
		print $content;
		SMOBTemplate::footer();
	}
				
	// Browsing a single post
	private function post() {
		$post = new SMOBPost(SMOBTools::get_post_uri($this->uri, 'post'));
		return $post->render();
	}
	
	// RDF data for a single post
	private function data() {
		$post = new SMOBPost(SMOBTools::get_post_uri($this->uri, 'post'));
		return $post->raw();
	}

	// Browsing a list of posts from a user
	private function user() { 
		global $foaf_uri;
		if(!$this->uri) {
			$this->uri = $foaf_uri;
		} 
		return $this->posts(); 
	}
	
	// Browsing a list of posts
	private function resource() { return $this->posts(); }
	private function posts() {
		$class = 'SMOBPostList'.ucfirst($this->type);
		$list = new $class($this->uri, $this->page);
		return $list->render();		
	}
	
	private function followings() {
		return SMOBTemplate::users($this->type, SMOBTools::followings()); 
	}

	private function followers() {
		return SMOBTemplate::users($this->type, SMOBTools::followers());
	}

}

