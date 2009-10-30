<?php

// SCRIPT_URI isn't present on all servers, so we do this instead:
$authority = "http://" . $_SERVER['HTTP_HOST'];
$root = $authority . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); 

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/../../lib/smob/client.php'); 
require_once(dirname(__FILE__).'/../../lib/foaf-ssl/libAuthentication.php');

// authentication = TODO

function publish_interface($reply_of = NULL) {
	global $servers, $twitter_user, $twitter_pass, $laconica, $root;
	
$form = <<<__END__
	
	<!-- XXX hack to make browsers send the posting as utf-8 -->
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

	<script type="text/javascript" src="../../js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="../../js/ui.core.js"></script>
	<script type="text/javascript" src="../../js/smob.js"></script>
	
	<script type="text/javascript" src="http://dev.jquery.com/view/trunk/plugins/autocomplete/lib/jquery.bgiframe.min.js"></script>
  <script type="text/javascript" src="http://dev.jquery.com/view/trunk/plugins/autocomplete/lib/jquery.dimensions.js"></script>
  <script type="text/javascript" src="http://dev.jquery.com/view/trunk/plugins/autocomplete/jquery.autocomplete.js"></script>
  
  <script type="text/javascript">
  $(document).ready(function(){
    
$("#location").autocomplete("../../lib/geonames/geo_complete.php");
  });
  </script>
	
  
	<script type="text/javascript">
	$(function() {
		$("#publish").click(function () {
			publish();
		});
	});
	$(function(){
		$('#content').focus(function(){
		  $('.content-details').show()
		});
		$('#content').keyup(function(){
			charsleft();
		});
	});
	</script>

	<h2>What's on your mind?</h2>
	<span class="content-details" style="display: none;">
	(You have <span id="charsleft">140</span> characters left)
	</span>
	<form>
	<textarea name="content" id="content" rows="5" cols="82"></textarea>
	<div class="content-details" style="display: none;">
	Replying to post (if any)
	<input type="text" name="sioc:reply_of" id="reply_of" value="$reply_of">
	(The <a href="javascript:window.location='$root/client/publish/bookmarklet.php?uri='+encodeURIComponent(window.location)">In My SMOB!</a> bookmarklet helps fill this in automatically.)
	<br/>
	<fieldset><legend>Networks</legend>
__END__;
	
	foreach($servers as $server => $key) {
		$form .= "<div id='servers-form'>";
		$form .= "<input type='checkbox' name='servers[]' value='$server' />$server<br/>";
		$form .= '</div>';
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
		</fieldset>
		
		<fieldset><legend>Presence Data</legend>
		Location: <input type="text" name="location" id="location" value="$location"  size="35">
		</fieldset>
	        <button id="publish">SMOB it!</button>

		</div>
		</form>

	<div id="smob-publish" style="display: none;">
		<br/><em>Publishing content ...</em>
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
$location = $_GET['location'];

$content = publish_interface($reply_of);

$page = $_GET['page'];
if (!$page)
	$page = 0;
$content .= show_posts($page);

smob_go($content);

?>
