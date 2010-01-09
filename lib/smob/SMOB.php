<?

/* 
	The main class - controls the action, launches all the stuff
*/

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
	var $commands = array('data', 'following', 'followers', 'post', 'posts', 'replies', 'resource', 'user');
	
	// Construct - save parameters and setup the RDF store
	public function __construct($type, $uri, $page, $publish) {
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
		if($publish) {
			$this->publish = $publish;
		}
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
		SMOBTemplate::header($this->publish);
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

	// Browsing a list of posts
	private function resource() { return $this->posts(); }
	private function posts() {
		$class = 'SMOBPostList'.ucfirst($this->type);
		$list = new $class($this->uri, $this->page);
		return $list->render();		
	}

	// List of followers
	private function followers() {
		$pattern = '?uri sioc:follows <' . SMOBTools::user_uri() . '>';
		return $this->people('followers', $pattern);
	}

	private function following() {
		$pattern = '<' . SMOBTools::user_uri() . '> sioc:follows ?uri';
		return $this->people('following', $pattern);
	}
	
	private function people($type, $pattern) {
		$query = "SELECT * WHERE { $pattern }";
		return SMOBTemplate::users($type, SMOBStore::query($query));	
	}
	
}

