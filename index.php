<?php 

parse_str($_SERVER['QUERY_STRING']);

require_once(dirname(__FILE__).'/lib/smob/SMOB.php'); 
require_once(dirname(__FILE__).'/lib/subscriber.php');

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
		        // @TODO: check that the user were not already a following? 
			    // Store the new relationship in local repository
			    error_log("storing to local repository");
			    $local_user = SMOBTools::user_uri();			
			    $follow = "<$local_user> sioc:follows <$remote_user> . ";
			    $local = "INSERT INTO <".SMOB_ROOT."data/followings> { $follow }";
			    SMOBStore::query($local);
			    SMOBTemplate::header('');

			    // Subscribe to the hub

                // Get the Publisher (following) Hub
			    $remote_user_feed = $remote_user.'/rss';
			    $xml = simplexml_load_file($remote_user_feed);
                if(count($xml) == 0)
                    return;
                $link_attributes = $xml->channel->link->attributes();
                if($link_attributes['rel'] == 'hub') {
                    $hub_url = $link_attributes['href'];
			        error_log("hub url:",0);
                    error_log($hub_url,0);
                }
                //$hub_url = "http://pubsubhubbub.appspot.com";
                //$hub_url = HUB_URL;
                $callback_url = urlencode(SMOB_ROOT."callback");
                $feed = urlencode($remote_user_feed);
                error_log($callback_url,0);
                error_log($feed,0);
                
                // Not using subscriber library as it does not allow async verify
                // create a new subscriber
                //$s = new Subscriber($hub_url, $callback_url);
                /// subscribe to a feed
                //$s->subscribe($feed);
                
                // Directly with curl
                //$ch = curl_init($hub_url);
                //curl_setopt($ch, CURLOPT_POST, TRUE);
                //curl_setopt($ch,CURLOPT_POSTFIELDS,"hub.mode=subscribe&hub.verify=async&hub.callback=$callback_url&hub.topic=$feed");
                //$response = curl_exec($ch);
                //$info = curl_getinfo($ch);
        
                //// all good -- anything in the 200 range 
                //if (substr($info['http_code'],0,1) == "2") {
                //    error_log($response,0);
                //}

                // Reusing do_curl function
                $result = SMOBTools::do_curl($hub_url, $postfields = "hub.mode=subscribe&hub.verify=async&hub.callback=$callback_url&hub.topic=$feed");
                // all good -- anything in the 200 range 
                if (substr($result[2],0,1) == "2") {
                    error_log("Succesfullyl subscribed",0);
                }
                error_log(join(' ', $result),0);

			    print "<a href='$remote_user'>$remote_user</a> was added to your following list and was notified about your subscription";
			    SMOBTemplate::footer();	
			    
			    // And ping to update the followers list remotely
			    // @TODO: This will work only if $u doesn't have /me or something in the end
			    $ping = "$u/add/follower/$local_user";
			    $result = SMOBTools::do_curl($ping);
			    error_log(join(' ', $result),0);
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
			//@TODO: notify the remote_user to remove local_user as following?
			// Should also make the follower to send unsubscribe request to the Hub?
		} 
		// Remove a following
		elseif($t == 'following') {
			$remote_user = $u;
			$local_user = SMOBTools::user_uri();
			$follow = "<$local_user> sioc:follows <$remote_user> . ";			
			$local = "DELETE FROM <".SMOB_ROOT."data/followings> { $follow }";
			SMOBStore::query($local);
			
			//@TODO: notify the the remote_user to remove local_user as follower?
		    //$ping = "$u/remove/follower/$local_user";
		    //$result = SMOBTools::do_curl($ping);
		    //error_log(join(' ', $result),0);
		    
		    // Unsubscribe to the Hub

            //$hub_url = "http://pubsubhubbub.appspot.com";
            //@TODO: following Hub should be stored?, 
            // otherwise, how we get it again?, getting feed directly from the following
            // what if it changed?
            $hub_url = HUB_URL;
            $callback_url = urlencode(SMOB_ROOT."callback");
            $feed = urlencode($remote_user.'/rss');
            error_log($callback_url,0);
            error_log($feed,0);
            $result = SMOBTools::do_curl($hub_url, $postfields = "hub.mode=unsubscribe&hub.verify=async&hub.callback=$callback_url&hub.topic=$feed");
            // all good -- anything in the 200 range 
            if (substr($result[2],0,1) == "2") {
                error_log("Sucesfully unsubscribed",0);
            }
            error_log(join(' ', $result),0);
            
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
		$ep = ARC2::getStoreEndpoint(SMOBTools::arc_config());
		$ep->go();

	// callback script to process the incoming hub POSTs
	} elseif($t == 'callback') {
        // Getting hub_challenge from hub after sending it post subscription
        if(isset($_GET["hub_challenge"])) {
                // send confirmation to the hub
                echo $_GET["hub_challenge"];
                error_log("hub challenge:",0);
                error_log($_GET["hub_challenge"],0);
        }
        // Getting feed updates from hub
        if(isset($_POST)) {
                //error_log($HTTP_RAW_POST_DATA,0);
                //error_log(join(' ', $_POST),0);
                $post_data = file_get_contents("php://input");
                //@FIXME: this solution is a bit hackish
                $post_data = str_replace('dc:date', 'dc_date', $post_data);
                error_log($post_data,0);
                
                // Parsing the new feeds to load in the triple store
                // post data will contain something like:
                // <item rdf:about="http://smob.rhizomatik.net/post/2011-03-21T18:33:21+01:00">
                // and this subscriber must store the rdf in a url like:
                // http://smob.rhizomatik.net/data/2011-03-21T18:33:21+01:00
                $xml = simplexml_load_string($post_data);
                if(count($xml) == 0)
                    return;
                foreach($xml->item as $item) {
                    error_log($item,0);
                    $link = (string) $item->link;
                    error_log($link,0);
                    $date = (string) $item->dc_date;
                    error_log($date,0);
                    $description = (string) $item->description;
                    error_log($description,0);
                    $site = parse_url($link, PHP_URL_SCHEME) . "://" .  parse_url($link, PHP_URL_HOST) . "/";
                    error_log($site,0);
                    $author = $site . "me";
                    error_log($author,0);

                    $query = " INSERT INTO <$link> {
                    <$site> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://smob.me/ns#Hub> .
                    <$link> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://rdfs.org/sioc/types#MicroblogPost> .
                    <$link> <http://rdfs.org/sioc/ns#has_container> <$site> .
                    <$link> <http://rdfs.org/sioc/ns#has_creator> <$author> .
                    <$link> <http://xmlns.com/foaf/0.1/maker> <$author#id> .
                    <$link> <http://purl.org/dc/terms/created> \"$date\"^^<http://www.w3.org/2001/XMLSchema#dateTime> .
                    <$link> <http://purl.org/dc/terms/title> \"Update - $date\"^^<http://www.w3.org/2001/XMLSchema#string> .
                    <$link> <http://rdfs.org/sioc/ns#content> \"$description\"^^<http://www.w3.org/2001/XMLSchema#string> .
                    <$link#presence> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://online-presence.net/opo/ns#OnlinePresence> .
                    <$link#presence> <http://online-presence.net/opo/ns#declaredOn> <$author> .
                    <$link#presence> <http://online-presence.net/opo/ns#declaredBy> <$author#id> .
                    <$link#presence> <http://online-presence.net/opo/ns#StartTime> \"$date\"^^<http://www.w3.org/2001/XMLSchema#dateTime> .
                    <$link#presence> <http://online-presence.net/opo/ns#customMessage> <$link> . }"
                    
                    SMOBStore::query($query);
                }
        }
	} else {
		$smob = new SMOB($t, $u, $p);
		$smob->reply_of($r);
		$smob->go();
	}
}
