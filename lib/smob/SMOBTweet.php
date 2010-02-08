<?

/* 
	Interacting with the Twitter API to retrieve twitter timeline in SMOB
*/

class SMOBTweet {

	var $user;
	var $pass;
	
	public function __construct() {
		$this->user = TWITTER_USER;
		$this->pass = TWITTER_PASS;
	}
		
	public function getposts() {
		$url  = 'http://twitter.com/statuses/home_timeline.json';
		$userpwd = $this->user.':'.$this->pass;
		$res = SMOBTools::do_curl($url, '', $userpwd, 'GET');
		$tweets = json_decode($res[0]);
		foreach($tweets as $tweet) {
			$id = $tweet->id;
			$username = $tweet->user->screen_name;			
			$uri = "http://twitter.com/$username/status/$id";
			if (!SMOBStore::ask("<$uri> a sioct:MicroblogPost")) {
				echo "LOADING $uri<br/>";
				$this->load_tweet($tweet);
			}
		}
	}
		
	private function load_tweet($tweet) {	
		$id = $tweet->id;
		$username = $tweet->user->screen_name;

		$local = $this->user;
		
		$uri = "http://twitter.com/$username/status/$id";
		
		$content = $tweet->text;
		$userid = $tweet->user->id;
		$name = $tweet->user->name;
		$ts = date('c', time($tweet->created_at));
		$user_uri = "http://twitter.com/$username";
		$user_foaf_uri = "http://twitter.com/$username#me";
		$depiction = $tweet->user->profile_image_url;
			
		$triples = array();
		$triples[] = array(SMOBTools::uri($uri), "a", "sioct:MicroblogPost");
		$triples[] = array("sioc:has_container", SMOBTools::uri('http://twitter.com/'));
		$triples[] = array("sioc:has_creator", SMOBTools::uri($user_uri));
		$triples[] = array("foaf:maker", SMOBTools::uri($user_foaf_uri));
		$triples[] = array("dct:created", SMOBTools::date($ts));
		$triples[] = array("dct:title", SMOBTools::literal("Update - ".$ts));
		$triples[] = array("sioc:content", SMOBTools::literal($content));
		if(strpos($content, '@'.$this->user)!==false) {
			$triples[] = array("sioc:addressed_to", SMOBTools::uri(FOAF_URI));
			$triples[] = array("sioc:addressed_to", SMOBTools::uri('http://twitter.com/'.$this->user.'#me'));
			$triples[] = array(SMOBTools::uri(FOAF_URI), 'sioc:name', SMOBTools::literal($this->user));
			$triples[] = array(SMOBTools::uri('http://twitter.com/'.$this->user.'#me'), 'sioc:name', SMOBTools::literal($this->user));
		}
				
		$triples[] = array(SMOBTools::uri($user_foaf_uri), "foaf:name", SMOBTools::literal($name));
		$triples[] = array("foaf:depiction", SMOBTools::uri($depiction));
				
		$opo_uri = $uri.'#presence';
		$triples[] = array(SMOBTools::uri($opo_uri), "a", "opo:OnlinePresence");
		$triples[] = array("opo:declaredOn", SMOBTools::uri($user_uri));
		$triples[] = array("opo:declaredBy", SMOBTools::uri($user_foaf_uri));
		$triples[] = array("opo:StartTime", SMOBTools::date($ts));
		$triples[] = array("opo:customMessage", SMOBTools::uri($uri));
		
		$graph = SMOB_ROOT."data/twitter/$id";
		$rdf = SMOBTools::render_sparql_triples($triples);	
		
		$query = "INSERT INTO <$graph> { $rdf }";	
		$res = SMOBStore::query($query);
	}		
	
}

/*

Data example - need to expose more RDF data in upcoming releases (RT, location, etc.)

[in_reply_to_user_id] => 
   [in_reply_to_status_id] => 
   [in_reply_to_screen_name] => 
   [contributors] => 
   [created_at] => Sun Jan 24 10:43:02 +0000 2010
   [source] => <a href="http://apiwiki.twitter.com/" rel="nofollow">API</a>
   [retweeted_status] => stdClass Object
       (
           [in_reply_to_user_id] => 
           [in_reply_to_status_id] => 
           [in_reply_to_screen_name] => 
           [contributors] => 
           [created_at] => Sat Jan 23 22:16:48 +0000 2010
           [source] => web
           [user] => stdClass Object
               (
                   [url] => http://www.twitter-twatter.com
                   [description] => 
                   [time_zone] => Copenhagen
                   [profile_sidebar_fill_color] => e0ff92
                   [followers_count] => 662
                   [statuses_count] => 706
                   [notifications] => 
                   [created_at] => Wed May 16 22:24:12 +0000 2007
                   [friends_count] => 96
                   [profile_sidebar_border_color] => 87bc44
                   [contributors_enabled] => 
                   [favourites_count] => 1
                   [profile_image_url] => http://a3.twimg.com/profile_images/62511499/IMG_9542_normal.jpg
                   [profile_text_color] => 000000
                   [lang] => en
                   [verified] => 
                   [geo_enabled] => 
                   [profile_background_image_url] => http://a3.twimg.com/profile_background_images/7203357/ZZ5F91DA46.jpg
                   [protected] => 
                   [screen_name] => overdrev
                   [following] => 
                   [profile_link_color] => 0000ff
                   [location] => JÃ¸rlunde, Denmark
                   [name] => Michael Jackson
                   [profile_background_tile] => 
                   [id] => 6092232
                   [utc_offset] => 3600
                   [profile_background_color] => 9ae4e8
               )

           [truncated] => 
           [geo] => 
           [id] => 8125802200
           [favorited] => 
           [text] => After a day at MIDEM, a conference that looks to the future, I can only conclude that the Music industry still hates the Internet.
       )

   [user] => stdClass Object
       (
           [url] => http://www.soufron.com
           [description] => Jean-Baptiste is a Lawyer in Paris, and a tech entrepreneur, he was CLO for the Wikimedia Foundation and works regularly for Free Software and Free Content 
           [time_zone] => Paris
           [profile_sidebar_fill_color] => FFF7CC
           [followers_count] => 2469
           [statuses_count] => 3828
           [notifications] => 
           [created_at] => Sat Mar 31 22:18:13 +0000 2007
           [friends_count] => 2179
           [profile_sidebar_border_color] => F2E195
           [contributors_enabled] => 
           [favourites_count] => 8
           [profile_image_url] => http://a1.twimg.com/profile_images/422219064/jbradio4_normal.jpg
           [profile_text_color] => 0C3E53
           [lang] => en
           [verified] => 
           [geo_enabled] => 1
           [profile_background_image_url] => http://s.twimg.com/a/1264119427/images/themes/theme12/bg.gif
           [protected] => 
           [screen_name] => soufron
           [following] => 
           [profile_link_color] => FF0000
           [location] => ÃœT: 48.873458,2.327646
           [name] => soufron
           [profile_background_tile] => 
           [id] => 3083881
           [utc_offset] => 3600
           [profile_background_color] => BADFCD
       )

   [truncated] => 1
   [geo] => 
   [id] => 8146007146
   [favorited] => 
   [text] => RT @overdrev: After a day at MIDEM, a conference that looks to the future, I can only conclude that the Music industry still hates the I ...

*/
