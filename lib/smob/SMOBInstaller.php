<?php

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
		<h2>1. Server setup</h2>
		<p>
			Welcome to the installer of your SMOB hub. 
			Before starting, you must ensure that you have met the following requirements:
		</p>
		<ul>
			<li>Download <a href="http://arc.semsol.org/download">ARC2</a> and unzip it in the current <code>lib</code> folder and rename it from to <code>arc</code>;</li>
			<li>Download <a href="http://phpxmlrpc.sourceforge.net/">XML-RPC for PHP</a>, unzip it in the current <code>lib</code> folder and rename it from <code>xmlrpc-version</code> to <code>xmlrpc</code>;</li>
			<li>Make the <code>config</code> directory writable by your web server;
			<li>If your SMOB hub is not in the <code>/smob</code> directory of your website, please edit the <code>.htaccess</code> file accordingly;</li>
			<li>Edit the <code>auth/.htaccess</code> file for authentication purposes. If you use <code>htpasswd</code> authentication, do not forget to create this file. You can use the <a href="http://www.htaccesstools.com/htpasswd-generator/">htpasswd generator here</a>.</li>
		</ul>
	</div>
	<div id="smob-db-pane">
		<h2>2. Database setup of SMOB</h2>
		<div id="smob-db-pane-in">
			<form>
				<fieldset>
					<legend>MySQL database settings</legend>
					<label for="db-host">database host:</label> <input type="text" id="db-host" name="db-host" value="localhost" size="50"><br />
					<label for="db-name">database name:</label> <input type="text" id="db-name" name="db-name" value="smob" size="20"><br />
					<label for="db-store">RDF store name:</label> <input type="text" id="db-store" name="db-store" value="smob" size="20"><br />
					<label for="db-user">user name:</label> <input type="text" id="db-user" name="db-user" value="root" size="10"><br />
					<label for="db-pwd">password:</label> <input type="password" id="db-pwd" name="db-pwd" value="root" size="10"><br />
				</fieldset>
			</form>
			<p class="note">
			If the database does not exist yet, it will be created it for you.
			</p>
		</div>
		<div id="smob-db-pane-out">
			<em>Request sent...</em>
		</div>
	</div>
	<div id="smob-settings-pane">
		<h2>3. SMOB settings</h2>
		<div id="smob-settings-pane-in">
			<form>
				<fieldset>
					<legend>SMOB settings</legend>
					<label for="smob-root">SMOB hub address:</label> <input type="text" id="smob-root" name="smob-root" value="$root" size="50"><br />
					<label for="smob-purge">Purge posts after <input type="text" id="smob-purge" name="smob-purge" value="5" size="2"> days (0 to keep them)</label> <br />	
				</fieldset>	
			</form>
		</div>
		<div id="smob-settings-pane-out">
			<em>Request sent...</em>
		</div>
	</div> 
	<div id="smob-user-pane">
		<h2>4. User settings</h2>
		<div id="smob-user-pane-in">
			<form>
				<fieldset>
					<legend>FOAF settings</legend>
					<p class="note">
					Using your existing FOAF URI will provide distributed user-profile, and will be used to link to sign your posts. 
					(It will also be used as well to authenticate via FOAF-SSL if you wish to do so.).
					If you do not have a FOAF profile, you can create one <a href="http://foafbuilder.qdos.com/">here</a> or <a href="http://www.ldodds.com/foaf/foaf-a-matic">there</a>, or use your Twitter account via <a href="http://semantictweet.com">SemanticTweet</a>.
					</p>
					<p class="note">
					<b>Please also note that this is your personal URI and not the URL of your FOAF profile. For more information about the difference between both, you can check the <a href="http://pedantic-web.org/fops.html#inconsist">Pedantic Web page</a> on the topic. In addition, that URI must be dereferencable and must return RDF information about itself.</b
					</p>
					<label for="smob-uri">FOAF URI:</label> <input type="text" id="smob-uri" name="smob-uri" value="" size="50"><br />
					<p class="note">
					If you do not want to create a FOAF profile, you can simply fill-in the following details and SMOB will create one for you.
					</p>
					<label for="smob-username">Name:</label> <input type="text" id="smob-username" name="smob-username" value="" size="50"><br />
					<label for="smob-depiction">Picture:</label> <input type="text" id="smob-depiction" name="smob-depiction" value="" size="50"><br />
				</fieldset>
				<fieldset>
					<legend>Authentication method</legend>
					<input type="radio" name="smob-auth" id="smob-auth" value="htpasswd" checked="true"> htpasswd (default)<br/>
					<input type="radio" name="smob-auth" id="smob-auth" value="foafssl"> foafssl<br/>
				</fieldset>
				<fieldset>
					<legend>Twitter integration</legend>
					<input type="checkbox" id="smob-twitter-read" name="smob-twitter-read"> Integrate my Twitter messages in SMOB<br />
					<input type="checkbox" id="smob-twitter-post" name="smob-twitter-post"> Publish my SMOB updated to Twitter<br />					
					<label for="smob-twitter-login">Twitter login:</label> <input type="text" id="smob-twitter-login" name="smob-twitter-login" value="" size="50"><br />
					<label for="smob-twitter-pass">Twitter pass:</label> <input type="password" id="smob-twitter-pass" name="smob-twitter-pass" value="" size="50"><br />
					<p class="note">
					Twitter login / password is optional.
					</p>
				</fieldset>	
			</form>
		</div>
		<div id="smob-user-pane-out">
			<em>Request sent...</em>
		</div>
	</div>
	<div id="done-pane">
	</div>
	<button id="step">Ready ? Go !</button>
_END_;
	}
}

