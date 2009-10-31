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
  $(document).ready(function() {
    $("#location").autocomplete("../../lib/geonames/geo_complete.php");

    $("#publish").click(function () {
      publish();
    });

    $('#content').focus(function() {
      $('.content-details').show();
      charsleft();
    });
    // XXX form.blur doesn't work :-/
    $('#content-form').blur(function() {
      if ($('#content').val().length == 0)
	$('.content-details').hide();
    });
    $('#content').keyup(function(){
      return charsleft();
    });
  });
  </script>
	  
	<h2>What&apos;s on your mind?</h2>
	<span class="content-details" style="display: none;">
	(You have <span id="charsleft">140</span> characters left)
	</span>
	<form id="content-form">
	<textarea name="content" id="content" rows="5" cols="82"></textarea>
	<div class="content-details" style="display: none;">
	Replying to post (if any)
	<input type="text" name="sioc:reply_of" id="reply_of" value="$reply_of">
	(The <a href="javascript:window.location='$root/client/publish/bookmarklet.php?uri='+encodeURIComponent(window.location)">In My SMOB!</a> bookmarklet helps fill this in automatically.)
	<br/>
	<fieldset><legend>Networks</legend>
__END__;
	
	$form .= "<div id='servers-form'>";
	foreach($servers as $server => $key) {
		$form .= "<input type='checkbox' name='servers[]' value='$server' />$server<br/>";
	}
	if($twitter_user && $twitter_pass) {
	  	$form .= "<input type='checkbox' name='servers[]' value='tw-$twitter_user' />Twitter as $twitter_user<br/>";
	}
	if($laconica) {
	  foreach($laconica as $service => $user) {
	    $username = $user['user'];
   		$form .= "<input type='checkbox' name='laconica[$service]' value='twit' />$service as $twitter_user<br/>";
	  }
	}
	$form .= '</div>';
	

	$form .= <<<_END_
		</fieldset>
		
		<fieldset><legend>Presence Data</legend>
		Location: <input type="text" name="location" id="location" value="$location"  size="35">
		</fieldset>
		</div>
		</form>

	<button id="publish" class="content-details" style="display: none;">SMOB it!</button>


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
