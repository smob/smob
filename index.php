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
		    // @TODO: has it sense that the user add a follower?. Then the follower should also be notified to add the current user as following
		    // When the request comes from another user adding a following, the action is ran as there authentication is not needed
			
			$remote_user = SMOBTools::remote_user($u);
			if(!$remote_user) die();
			$local_user = SMOBTools::user_uri();
			$follow = "<$remote_user> sioc:follows <$local_user> . ";	
			$local = "INSERT INTO <".SMOB_ROOT."data/followers> { $follow }";
			error_log("DEBUG: Added follower $remote_user with the query $local", 0);
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
		        // @TODO: check that the user were not already a following? 
			    // Store the new relationship in local repository
			    $local_user = SMOBTools::user_uri();			
			    $follow = "<$local_user> sioc:follows <$remote_user> . ";
			    $local = "INSERT INTO <".SMOB_ROOT."data/followings> { $follow }";
			    SMOBStore::query($local);
			    error_log("DEBUG: Added following $remote_user with the query: $local",0);
			    SMOBTemplate::header('');

			    // Subscribe to the hub

                // Get the Publisher (following) Hub
			    $remote_user_feed = $remote_user.FEED_URL_PATH;
			    $xml = simplexml_load_file($remote_user_feed);
                if(count($xml) == 0)
                    return;
                $link_attributes = $xml->channel->link->attributes();
                if($link_attributes['rel'] == 'hub') {
                    $hub_url = $link_attributes['href'];
                }
                $callback_url = urlencode(SMOB_ROOT."callback");
                $feed = urlencode($remote_user_feed);
                
                // Not using subscriber library as it does not allow async verify
                // Reusing do_curl function
                $result = SMOBTools::do_curl($hub_url, $postfields = "hub.mode=subscribe&hub.verify=async&hub.callback=$callback_url&hub.topic=$feed");
                // all good -- anything in the 200 range 
                if (substr($result[2],0,1) == "2") {
                    error_log("DEBUG: Successfully subscribed to topic $remote_user_feed using hubsub $hub_url",0);
                }
                error_log("DEBUG: Server answer: ".join(' ', $result),0);

			    print "<a href='$remote_user'>$remote_user</a> was added to your following list and was notified about your subscription";
			    SMOBTemplate::footer();	
			    
			    // And ping to update the followers list remotely
			    // @TODO: This will work only if $u doesn't have /me or something in the end
			    //$ping = str_replace("me", "add", $ping)."/follower/$local_user";
			    $ping = SMOBTools::host($remote_user)."/add/follower/$local_user";
			    $result = SMOBTools::do_curl($ping);
			    error_log("DEBUG: Sent $ping",0);
			    error_log("DEBUG: Server answer: ".join(' ', $result),0);
			 }
		}
	}
	elseif($a && $a == 'remove') {
	    //
		if(!SMOBAuth::check()) die();
		$u = str_replace('http:/', 'http://', $u);
		// Remove a follower
		if($t == 'follower') {
		    // @TODO: has it sense that the user remove a follower?. Then the follower should also be notified to remove the current user as following
		    // Instead, when the request comes from another user removing a following, the action will not be run as there is not authentication
			$remote_user = $u;
			$local_user = SMOBTools::user_uri();
			$follow = "<$remote_user> sioc:follows <$local_user> . ";	
			$local = "DELETE FROM <".SMOB_ROOT."data/followers> { $follow }";
			SMOBStore::query($local);
			error_log("DEBUG: Removed follower $remote_user with the query: $local",0);
		} 
		// Remove a following
		elseif($t == 'following') {
			$remote_user = $u;
			$local_user = SMOBTools::user_uri();
			$follow = "<$local_user> sioc:follows <$remote_user> . ";			
			$local = "DELETE FROM <".SMOB_ROOT."data/followings> { $follow }";
			SMOBStore::query($local);
			error_log("DEBUG: Removed following $remote_user with the query: $local",0);
			
			 // And ping to update the followers list remotely
		    //$ping = str_replace("me","remove", $u)."/follower/$local_user";
		    $ping = SMOBTools::host($u)."/remove/follower/$local_user";
			error_log("DEBUG: Sent $ping",0);
		    $result = SMOBTools::do_curl($ping);
			error_log("DEBUG: Server answer: ".join(' ', $result),0);
		    
		    // Unsubscribe to the Hub

            //@TODO: following Hub should be stored?, 
		    $remote_user_feed = $remote_user.FEED_URL_PATH;
		    $xml = simplexml_load_file($remote_user_feed);
            if(count($xml) == 0)
                return;
            $link_attributes = $xml->channel->link->attributes();
            if($link_attributes['rel'] == 'hub') {
                $hub_url = $link_attributes['href'];
            }
            $callback_url = urlencode(SMOB_ROOT."callback");
            $feed = urlencode($remote_user_feed);
            $result = SMOBTools::do_curl($hub_url, $postfields = "hub.mode=unsubscribe&hub.verify=async&hub.callback=$callback_url&hub.topic=$feed");
            // all good -- anything in the 200 range 
            if (substr($result[2],0,1) == "2") {
                    error_log("DEBUG: Successfully unsubscribed to topic $remote_user_feed using hubsub $hub_url",0);
            }
			error_log("DEBUG: Server answer: ".join(' ', $result),0);

	        //print "<a href='$remote_user'>$remote_user</a> was deleted from your following list and your subscription was removed";
	        SMOBTemplate::footer();	
            
		}
		header("Location: ".SMOB_ROOT."${t}s");
	}	
	elseif($t == 'rss_owner') {
		header ("Content-type: text/xml");
		//$tweet = new SMOBFeed();
		//$tweet->rss();
		error_log("DEBUG: rssfilepath: ".FEED_FILE_PATH);
		if (!file_exists(FEED_FILE_PATH)) {
		    error_log("DEBUG: initial RSS file does not exists", 0);
		    SMOBTools::initial_rss_file();
		}
        $rssfile = fopen(FEED_FILE_PATH, 'r'); 
        $rss = fread($rssfile, filesize(FEED_FILE_PATH));
        fclose($rssfile);
        echo($rss);
	}
	// function to server RDF inside item content
	// is not being used for now
	elseif($t == 'rssrdf_owner') {
		header ("Content-type: text/xml");
		$tweet = new SMOBFeed();
		$tweet->rssrdf();
	}
	elseif($t == 'sparql') {
		if($_POST) {
			SMOBTools::checkAccess($_POST);
		}
		$ep = ARC2::getStoreEndpoint(SMOBTools::arc_config());
		$ep->go();

	// callback script to process the incoming hub POSTs
	} elseif($t == 'callback') {
	    if (array_key_exists('REMOTE_HOST',$_SERVER)) {//&& ($_SERVER['REMOTE_HOST'] == HUB_URL_SUBSCRIBE)) {
	        error_log("DEBUG: request from host: ".$_SERVER['REMOTE_HOST']);
	    }
	    if (array_key_exists('HTTP_USER_AGENT',$_SERVER)) {
	        error_log("DEBUG: request from user_agent: ".$_SERVER['REMOTE_HOST']);
	    }
        // Getting hub_challenge from hub after sending it post subscription
        if(isset($_GET["hub_challenge"])) {
                // send confirmation to the hub
                echo $_GET["hub_challenge"];
                error_log("DEBUG: received and sent back hub challenge:".$_GET["hub_challenge"],0);
        }
        // Getting feed updates from hub
        elseif(isset($_POST)) {
                $post_data = file_get_contents("php://input");
	            error_log("DEBUG: received POST with content: $post_data",0);
                SMOBTools::get_rdf_from_rss($post_data) ;
        }
        elseif(isset($_DELETE)) {
            $post_data = file_get_contents("php://input");
	            error_log("DEBUG: received DELETE with content: $post_data",0);
        }
        elseif(isset($_PUT)) {
            $post_data = file_get_contents("php://input");
	            error_log("DEBUG: received PUT with content: $post_data",0);
        }
	// same as callback funcion, just to check subscriptions with a different callback URL
	} elseif($t == 'callbackrdf') {
	    if (array_key_exists('REMOTE_HOST',$_SERVER)) {//&& ($_SERVER['REMOTE_HOST'] == HUB_URL_SUBSCRIBE)) {
	        error_log("DEBUG: request from host: ".$_SERVER['REMOTE_HOST']);
	    }
	    if (array_key_exists('HTTP_USER_AGENT',$_SERVER)) {
	        error_log("DEBUG: request from user_agent: ".$_SERVER['REMOTE_HOST']);
	    }
        // Getting hub_challenge from hub after sending it post subscription
        if(isset($_GET["hub_challenge"])) {
                // send confirmation to the hub
                echo $_GET["hub_challenge"];
                error_log("DEBUG: received and sent back hub challenge:".$_GET["hub_challenge"],0);
        }
        // Getting feed updates from hub
        elseif(isset($_POST)) {
                $post_data = file_get_contents("php://input");
	            error_log("DEBUG: received POST with content: $post_data",0);
                SMOBTools::get_rdf_from_rss($post_data) ;
        }
        elseif(isset($_DELETE)) {
            $post_data = file_get_contents("php://input");
	            error_log("DEBUG: received DELETE with content: $post_data",0);
        }
        elseif(isset($_PUT)) {
            $post_data = file_get_contents("php://input");
	            error_log("DEBUG: received PUT with content: $post_data",0);
        }
	} else {
		$smob = new SMOB($t, $u, $p);
		$smob->reply_of($r);
		$smob->go();
	}
}
