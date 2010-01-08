<?

/* 
	The installer class
*/

class SMOBInstaller {
	
	// Construct
	public function __construct() {
	}
	
	// Main method - analyse the query type, get the content and render it
	public function go() {
		SMOBTemplate::header('');
		print $this->core();
		SMOBTemplate::footer();
	}
	
	private function core() {
		$root = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		return <<<_END_
	<div id="head">
		<p>
			Welcome to the installer of your SMOB Hub. 
			Before starting, you must ensure that you have the rights to create files in your Web server. 
			If not, the install will fail and you have to run it manually.
		</p>
		<button id="step">START!</button>
	</div>
	<div id="main">
		<div id="get-files-pane">
			<h2>1. Install SMOB dependencies (i.e. ARC2 library)</h2>
			<div id="get-files-pane-in">
				<form>
					<fieldset>
						<legend>Download dependencies</legend>
						<label for="path-wget"><code>wget</code> path:</label> <input type="text" id="path-wget" name="path-curl" value="/usr/bin/" size="50"><br />
						<label for="path-tar"><code>tar</code> path:</label> <input type="text" id="path-tar" name="path-tar" value="/usr/bin/" size="10"><br />
						<label for="path-curl"><code>curl</code> path:</label> <input type="text" id="path-curl" name="path-curl" value="/usr/bin/" size="10"><br />
					</fieldset>
				</form>
				<p class="note">
				The ARC2 library will be automatically downloaded and extracted from the <a href="http://arc.semsol.org">project website</a>. 
				</p>
			</div>
			<div id="get-files-pane-out">
				<em>Request sent...</em>
			</div>
		</div>
		<div id="create-db-pane">
			<h2>2. Database setup of SMOB</h2>
			<div id="create-db-pane-in">
				<form>
					<fieldset>
						<legend>MySQL database settings</legend>
						<label for="db-host">database host:</label> <input type="text" id="db-host" name="db-host" value="localhost" size="50"><br />
						<label for="db-name">database name:</label> <input type="text" id="db-name" name="db-name" value="smob" size="20"><br />
						<label for="db-user">user name:</label> <input type="text" id="db-user" name="db-user" value="root" size="10"><br />
						<label for="db-pwd">password:</label> <input type="password" id="db-pwd" name="db-pwd" value="root" size="10"><br />
					</fieldset>
				</form>
				<p class="note">
				If the database does not exist yet, it will be created it for you.
				</p>
			</div>
			<div id="create-db-pane-out">
				<em>Request sent...</em>
			</div>
		</div>
		<div id="smob-config-pane">
			<h2>2. SMOB config</h2>
			<div id="smob-config-pane-in">
				<form>
					<fieldset>
						<legend>SMOB settings</legend>
						<label for="smob-root">SMOR Hub address:</label> <input type="text" id="smob-root" name="smob-root" value="$root" size="50"><br />
						<label for="smob-gmap">GoogleMap API key (optional):</label> <input type="text" id="smob-gmap" name="smob-gmap" value="" size="50"><br />
						<label for="smob-uri">FOAF URI:</label> <input type="text" id="smob-uri" name="smob-uri" value="" size="50"><br />
						<label for="smob-twitter-login">Twitter login:</label> <input type="text" id="smob-twitter-login" name="smob-twitter-login" value="" size="50"><br />
						<label for="smob-twitter-pass">Twitter pass:</label> <input type="password" id="smob-twitter-pass" name="smob-twitter-pass" value="" size="50"><br />
					</fieldset>	
				</form>
				<p class="note">
				Your FOAF URI is required, and will be used as well to authenticate via FOAF-SSL. Twitter login / password is optional.
				</p>
			</div>
			<div id="smob-config-pane-out">
				<em>Request sent...</em>
			</div>
		</div> 
		<div id="done-pane">
		</div>
	</div>
_END_;
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
	private function posts() {
		$class = 'SMOBPostList'.ucfirst($this->type);
		$list = new $class($this->uri, $this->page);
		return $list->render();		
	}

}

