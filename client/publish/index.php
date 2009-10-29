<?php

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/../../lib/smob/client.php'); 

require_once(dirname(__FILE__).'/../../lib/foaf-ssl/libAuthentication.php');

function publish_interface() {
	global $servers, $twitter_user, $twitter_pass, $laconica;
	
$form = <<<__END__
	
	<!-- XXX hack to make browsers send the posting as utf-8 -->
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

	<script type="text/javascript" src="http://jqueryui.com/jquery-1.3.2.js"></script>
	<script type="text/javascript" src="http://jqueryui.com/ui/ui.core.js"></script>
	<script type="text/javascript" src="publish.js"></script>
	<script type="text/javascript">
	$(function() {
		$("#publish").click(function () {
			publish();
		});
	});
	</script>

	<h2>New content</h2>
	<form>
	<textarea name="content" id="content" rows="5" cols="80"></textarea>
	<br/>
	Replying to post (if any)
	<input type="text" name="sioc:reply_of" id="reply_of" value="$reply_of">
	(The <a href="javascript:window.location='$root/publish/?sioc:reply_of='+window.location">SMOB Reply</a> bookmarklet fills this in automatically.)
	<br/>
	<fieldset><legend>Servers to ping</legend>
__END__;
	
	foreach($servers as $server => $key) {
		$form .= "<input type='checkbox' name='servers[]' value='$server' />$server<br/>";
	}
	if($twitter_user && $twitter_pass) {
	  	$form .= "<input type='checkbox' name='twitter' value='twit' />Twitter as $twitter_user<br/>";
	}
	if($laconica) {
	  foreach($laconica as $service => $user) {
	    $username = $user['user'];
   		$form .= "<input type='checkbox' name='laconica[$service]' value='twit' />$service as $twitter_user<br/>";
	  }
	}

	$form .= <<<_END_
		</fieldset></form>

	<button id="publish">SMOB it!</button>

	<div id="smob-uris" style="display: none;">
		<em>Publishing content ...</em>
	</div>
_END_;
return $form;
}

$auth = getAuth();
$do_auth = $auth['certRSAKey'];
$is_auth = $auth['isAuthenticated'];
$auth_uri = $auth['subjectAltName'];

if($do_auth) {
	if ($is_auth != 1 || $auth_uri != $foaf_uri) {
		print "Wrong credentials, try again !";
		die();
	} else {
		print "Welcome home, $auth_uri !";
	}
}

$reply_of = $_GET['sioc:reply_of'];

$content = publish_interface();
$title = "Publish a new post by $sioc_nick";
smob_go($title, $content);

?>
