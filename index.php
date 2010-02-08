<?php 

parse_str($_SERVER['QUERY_STRING']);

require_once(dirname(__FILE__).'/lib/smob/SMOB.php'); 

if(!SMOBTools::check_config()) {
	$installer = new SMOBInstaller();
	$installer->go();
} else {
	require_once(dirname(__FILE__)."/config/config.php");	
	// Follower / followings
	if($a && $a == 'add') {
		$u = str_replace('http:/', 'http://', $u);
		// Add a new follower
		if($t == 'follower') {
			$remote_user = SMOBTools::remote_user($u);
			if(!$remote_user) die();
			$local_user = SMOBTools::user_uri();
			$follow = "<$remote_user> sioc:follows <$local_user> . ";	
			$local = "INSERT INTO <".SMOB_ROOT."data/followers> { $follow }";
			SMOBStore::query($local);
		} 
		// Add a new following
		elseif($t == 'following') {
			if(!SMOBAuth::check()) die();
			$remote_user = SMOBTools::remote_user($u);
			if(!$remote_user) {
				SMOBTemplate::header('');
				print "<a href='$u'>$u</a> is not a valid Hub, user cannot be added";
				SMOBTemplate::footer();	
			} else {
				// Store the new relationship locally
				$local_user = SMOBTools::user_uri();			
				$follow = "<$local_user> sioc:follows <$remote_user> . ";
				$local = "INSERT INTO <".SMOB_ROOT."data/followings> { $follow }";
				SMOBStore::query($local);
				SMOBTemplate::header('');
				print "<a href='$remote_user'>$remote_user</a> was added to your following list and was notified about your subscription";
				SMOBTemplate::footer();	
				// And ping to update the followers list remotely
				$ping = "{$u}add/follower/$local_user";
				SMOBTools::do_curl($ping);
			}
		}
	}
	elseif($a && $a == 'remove') {
		if(!SMOBAuth::check()) die();
		$u = str_replace('http:/', 'http://', $u);
		// Remove a follower
		if($t == 'follower') {
			$remote_user = $u;
			$local_user = SMOBTools::user_uri();
			$follow = "<$remote_user> sioc:follows <$local_user> . ";	
			$local = "DELETE FROM <".SMOB_ROOT."data/followers> { $follow }";
			SMOBStore::query($local);
		} 
		// Remove a following
		elseif($t == 'following') {
			$remote_user = $u;
			$local_user = SMOBTools::user_uri();
			$follow = "<$local_user> sioc:follows <$remote_user> . ";			
			$local = "DELETE FROM <".SMOB_ROOT."data/followings> { $follow }";
			SMOBStore::query($local);
		}
		header("Location: ".SMOB_ROOT."${t}s");
	}	
	elseif($t == 'rss_owner') {
		header ("Content-type: text/xml");
		$tweet = new SMOBFeed();
		$tweet->rss();
	}
	elseif($t == 'sparql') {
		if($_POST) {
			SMOBTools::checkAccess($_POST);
		}
		print_r(SMOBTools::arc_config());
		$ep = ARC2::getStoreEndpoint(SMOBTools::arc_config());
		$ep->go();	
	} else {
		$smob = new SMOB($t, $u, $p);
		$smob->reply_of($r);
		$smob->go();
	}
}
