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
			// Do sanity check for the uri
			$remote_user = $u;
			$local_user = SMOBTools::user_uri();
			$follow = "<$remote_user> sioc:follows <$local_user> . ";	
			$local = "INSERT INTO <${smob_root}data/followers> { $follow }";
			SMOBStore::query($local);
		} 
		// Add a new following
		elseif($t == 'following') {
			if(substr($u, -1) != '/') {
				$u = "$u/";
			}
			$remote_user = "${u}user/owner";
			// Store the new relationship locally
			$local_user = SMOBTools::user_uri();
			$follow = "<$local_user> sioc:follows <$remote_user> . ";
			$local = "INSERT INTO <${smob_root}data/following> { $follow }";
			SMOBStore::query($local);
			SMOBTemplate::header('');
			print "$remote_user was added to your following list and was notified about your subscription";
			SMOBTemplate::footer();	
			// And ping to update the followers list remotely
			$ping = "{$u}ping/follower/$local_user";
			SMOBTools::do_curl($ping);
		}
	}
	elseif($t == 'sparql') {
		if($_POST) {
			SMOBTools::checkLoad($_POST);
		}
		$ep = ARC2::getStoreEndpoint($arc_config);
		$ep->go();	
	} else {
		$smob = new SMOB($t, $u, $p);
		$smob->reply_of($r);
		$smob->go();
	}
}
