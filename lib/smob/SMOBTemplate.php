<?	

/*
	Deals with all the rendering aspect, from the complete application to individual posts
*/

class SMOBTemplate {
		
	public function publisher_header($reply_of = null) {
		global $servers, $twitter_user, $twitter_pass, $laconica, $smob_root;
		
		$contentblock = $reply_of ? "$('.content-details').show();" : "
		$('#content').focus(function() {
	      $('.content-details').show();
	    });";
	
		$form_js = <<<__END__
		<script type="text/javascript">
		  $(document).ready(function() {
		    $("#location").autocomplete("../../lib/geonames/geo_complete.php");
		    $("#publish").click(function () {
		      publish();
		    });
			$contentblock
			numwords = 0;
		    // XXX form.blur doesn't work :-/
		    $('#content-form').blur(function() {
		      if ($('#content').val().length == 0) {
  			    $('.content-details').hide();
		      }
		    });
		    $('#content').keyup(function(){
			  interlink();
		      return charsleft();
		    });
		  });
		</script>
__END__;
		$form = <<<__END__
			<h2>What&apos;s on your mind?</h2>
			<span class="content-details" style="display: none;">
			(You have <span id="charsleft">140</span> characters left)
			</span>
			<form id="content-form">
			<textarea name="content" id="content" rows="5" cols="82"></textarea>
			<div class="content-details" style="display: none;">
			
			<fieldset><legend>Reply</legend>
			Replying to post (if any)
			<input type="text" name="reply_of" id="reply_of" value="$reply_of">
			<br/>
			(The <a href="javascript:window.location='$smob_root/client/publish/bookmarklet.php?uri='+encodeURIComponent(window.location)">In My SMOB!</a> bookmarklet helps fill this in automatically.)
			</fieldset>
			
			<fieldset><legend>Broadcast</legend>
__END__;
			if($twitter_user && $twitter_pass) {
			  	$form .= "<input type='checkbox' name='twitter' id='twitter' checked='true' />Twitter as $twitter_user<br/>";
			}
//			$form .= "<div id='servers-form'>";
//			if($laconica) {
//			  foreach($laconica as $service => $user) {
//			    $username = $user['user'];
//		   		$form .= "<input type='checkbox' name='laconica[$service]' value='twit' />$service as $twitter_user<br/>";
//			  }
//			}
//			$form .= '</div>';
			$form .= <<<_END_
			</fieldset>
			
			<fieldset><legend>Presence Data</legend>
			Location: <input type="text" name="location" id="location" value="$location"  size="35">
			</fieldset>
			
			<fieldset><legend>Interlinking</legend>
			<div id="lod-form">Links will be suggested while typing ...</div>
			</fieldset>
			
			</div>
			</form>

			<button id="publish" class="content-details" style="display: none;">SMOB it!</button>

			<div id="smob-publish" style="display: none;">
				<br/><em>Publishing content ...</em>
			</div>
_END_;
		return array($form_js, $form);
	}
			
	public function header($publisher, $reply_of = null) {
		if($publisher) {
			list($form_js, $form) = SMOBTemplate::publisher_header($reply_of);
		}
		global $smob_root;
		$root = $smob_root ? $smob_root : './';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" 
  "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">

<html
  xmlns="http://www.w3.org/1999/xhtml" 
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:foaf="http://xmlns.com/foaf/0.1/" 
  xmlns:sioc="http://rdfs.org/sioc/ns#"
  xmlns:sioct="http://rdfs.org/sioc/types#"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
xml:lang="fr">

<head profile="http://ns.inria.fr/grddl/rdfa/">
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <title>SMOB</title>
  <link rel="stylesheet" type="text/css" href="<?php echo $root; ?>css/style.css" />
  <link rel="icon" type="image/png" href="<?php echo $root; ?>/img/smob-icon.png" />
  <script type="text/javascript" src="<?php echo $root; ?>js/jquery-1.3.2.min.js"></script>
  <script type="text/javascript" src="<?php echo $root; ?>js/jquery.timers-1.2.js"></script>
  <script type="text/javascript" src="<?php echo $root; ?>js/jquery.bgiframe.min.js"></script>
  <script type="text/javascript" src="<?php echo $root; ?>js/jquery.autocomplete.min.js"></script>
  <script type="text/javascript" src="<?php echo $root; ?>js/ui.core.js"></script>
  <script type="text/javascript" src="<?php echo $root; ?>js/smob.js"></script>
  <script type="text/javascript">
	var state = 0;
	var maxstate = 4;
	$(function() {
		$("#step").click(function () {
			process();
		});
	});
	$(function() { 
		$("#ts").everyTime(5000,function(i) {
			getnews();
		});
	});
  </script>
  <?php echo $form_js; ?>
</head>

<body about="<?php echo $smob_root; ?>" typeof="sioc:Microblog">

<div id="full">

<div id="header">
<h1><a href="<?php echo "$smob_root"; ?>">SMOB</a></h1>
<h2><span class="smob">S</span>emantic-<span class="smob">M</span>icr<span class="smob">OB</span>logging</h2>
</div>

<div id="main">

<div class="left">	
	
<?php echo $form; ?>	

<?		
	}
	
	public function footer() {
		global $smob_root;
?>
</div>

<div class="right"> 

<h2>Navigation</h2>
<ul>
<li><a href='<?php echo $smob_root; ?>'>Home</a></li>
<li><a href='<?php echo $smob_root; ?>following'>Following</a></li>
<li><a href='<?php echo $smob_root; ?>followers'>Followers</a></li>
<li><a href='<?php echo $smob_root; ?>sparql'>SPARQL</a></li>
</ul>

<h2>Hub owner ?</h2>
<ul>
<li><a href='<?php echo $smob_root; ?>auth'>Authenticate</a></li>
</ul>
	
</div>

<div style="clear: both;"> </div>
</div>

<div id="footer">
Powered by <a href="http://smob.sioc-project.org/">SMOB</a> thanks to <a href="http://www.w3.org/2001/sw/">Semantic Web</a> and <a href="http://linkeddata.org">Linked Data</a> technologies.<br/>
This page is valid <a href="http://validator.w3.org/check?uri=referer">XHTML</a> and <a href="http://www.w3.org/2007/08/pyRdfa/extract?uri=referer">contains RDFa markup</a>.
<br/>
</div>

</div>

</body>

</html>

<?		
	}
	
	public function users($type, $users) {
		global $smob_root;
		$ht = '<h2>'.ucfirst($type).'</h2>';
		if($users) {
			$ht .= '<ul>';
			foreach($users as $u) {
				$user = $u['uri'];
				$ht .= "<li><a href='$user'>$user</a>";
			}
			$ht .= '</ul>';
		} else {
			$ht .= 'No one at the moment';
		}
		if($type == 'following') {
			$ht .= "<p>If you want to follow new people, use the <a href=\"javascript:window.location='${smob_root}ping/following/'+window.location\">Follow in my SMOB!</a> bookmarklet.</p>";
		}
		return $ht;
	}
	
	public function person($person, $uri) {
		global $smob_root;
		$names = SMOBTools::either($person['names'], array("Anonymous"));
		$imgs = SMOBTools::either($person['images'], array("$smob_root/../img/avatar-blank.jpg"));
		$homepage = $person['homepage'];
		$weblog = $person['weblog'];
		$knows = $person['knows'];

		$name = $names[0];
		$pic = $imgs[0];

		$ht = "<div class=\"person\">\n";
		$ht .= "<img src=\"$pic\" class=\"depiction\" alt=\"Depiction\" />";
		$ht .= "$name\n";

		foreach ($homepage as $h) {
			$ht .= " [<a href=\"$h\">Website</a>]\n";
		}
		foreach ($weblog as $w) {
			$ht .= " [<a href=\"$w\">Blog</a>]\n";
		}
		foreach ($knows as $k) {
			$enc = get_uri($k, 'user');
			$ht .= " [<a href=\"$enc\">Friend</a>]\n";
		}

		$ht .= "</div>\n\n";
		return $ht;
	}
	
}