<?php

/*
	Representing posts
*/
	
class SMOBPost {
	
	var $uri;
	var $data;
	var $ts;
	var $content;
	var $triples = array();
	
	public function __construct($uri = null, $data = null) {		
		global $smob_root;
		if($uri) {
			$this->uri = $uri;
			if($data) {
				$this->data = $data;
				$this->process_content();
			} else {
				$this->process();
			}
		}
	}
	
	// Get the post data from the RDF store
	private function process() {
		$uri = $this->uri;
		$query = "
SELECT ?content ?author ?date ?reply_of ?reply_of_of ?depiction
WHERE {
<$uri> rdf:type sioct:MicroblogPost ;
	sioc:content ?content ;
	foaf:maker ?author ;
	dct:created ?date .
	OPTIONAL { <$uri> sioc:reply_of ?reply_of. }
	OPTIONAL { ?reply_of_of sioc:reply_of <$uri> . }
	OPTIONAL { ?author foaf:depiction ?depiction. } 
	OPTIONAL { ?author foaf:img ?depiction . }
} ";
		$res = SMOBStore::query($query);
		$this->data = $res[0];
		$this->process_content();
	}
	
	// Process the content to get #tags and @replies and embeds sioc:topic in it
	private function process_content() {
		$users = $this->get_users();
		if($users) {
			foreach($users as $t) {
				$user = $t['user'];
				$name = $t['name'];
				$enc = "<a class=\"topic\" rel=\"sioc:topic\" href=\"$user\"><a href=\"$enc\">@$name</a></a>";
				$this->data['content'] = str_replace("@$name", $r, $this->data['content']);
			}
		}
		$tags = $this->get_tags();
		if($tags) {
			foreach($tags as $t) {
				$tag = $t['tag'];
				$resource = $t['uri'];
				$enc = SMOBTools::get_uri($resource, 'resource');
				$r = "<span class=\"topic\" rel=\"sioc:topic\" href=\"$resource\"><a href=\"$enc\">#$tag</a></span>";
				$this->data['content'] = str_replace("#$tag", $r, $this->data['content']);
			}
		}
		return;
		}
	
	// Render the post in RDFa/XHTML
	public function render() {
		global $sioc_nick, $smob_root;

		$uri = $this->uri;
		
		$ocontent = $content = $this->data['content'];
		$author = $this->data['author'];
		$date = $this->data['date'];
		$reply_of = $this->data['reply_of'];
		$reply_of_of = $this->data['reply_of_of'];

		$pic = SMOBTools::either($this->data['depiction'], "${smob_root}img/avatar-blank.jpg");

		$ht .= "<div class=\"post\" typeof=\"sioct:MicroblogPost\" about=\"$uri\">\n";
		$ht .= "<span style=\"display:none;\" rel=\"sioc:has_container\" href=\"$smob_root\"></span>\n";
		$ht .= "<img src=\"$pic\" class=\"depiction\" alt=\"Depiction for $foaf_uri\"/>";
		$ht .= "  <span class=\"content\">$content</span>\n";
		$ht .= "  <span style=\"display:none;\" property=\"sioc:content\">$ocontent</span>\n";
		$ht .= '  <div class="infos">';
		$ht .= "  (by <span class=\"author\" rel=\"foaf:maker\" href=\"$author\"><a href=\"$enc\">$sioc_nick</a></span> - \n";
		$ht .= "  <span class=\"date\" property=\"dcterms:created\">$date</span>)\n";
		$ht .= " [<a href=\"$uri\">Permalink</a>]\n";
		$data = str_replace('post', 'data', $uri);
		$ht .= " [<a href=\"$data\">RDF</a>]\n";
		if(SMOBAuth::check()) {
			$enc2 = $this->get_publish_uri();
			$ht .= " [<a href=\"$enc2\">Post a reply</a>]\n";
		}
		if ($reply_of) {
			$enc3 = SMOBTools::get_uri($reply_of, 'post');
			$ht .= " [<a href=\"$enc3\">Parent</a>]\n";
		}
		if ($reply_of_of) {
			$enc4 = SMOBTools::get_uri($reply_of_of, 'post');
			$ht .= " [<a href=\"$enc4\">Child</a>]\n";
		}
		$ht .= '  </div>';
		$ht .= "</div>\n\n";
		return $ht;
	}
	
	// URI for publishing
	private function get_publish_uri() {
		global $smob_root;
		return "${smob_root}?r=" . urlencode($this->uri);
	}
		
	// Get the users mentioned in that post	
	private function get_users() {
		$post = $this->uri;
		$query = "
SELECT ?user ?name
WHERE {
	<$post> sioc:topic ?user .
	?user sioc:name ?name .
}";
		return SMOBStore::query($query);
	}
	
	// Get the tags mentioned in that post	
	private	function get_tags() {
		$post = $this->uri;
		$query = "
SELECT ?tag ?uri
WHERE {
	?tagging a tags:RestrictedTagging ;
		tags:taggedResource <$post> ;
		tags:associatedTag ?tag ;
		moat:tagMeaning ?uri .
}";
		return SMOBStore::query($query);
	}
	
	public function set_data($ts, $content, $reply_ofs, $location, $mappings) {
		global $foaf_uri, $smob_root;

		$user_uri = SMOBTools::user_uri();
		$this->ts = $ts;
		$this->content = $content;
		$this->uri();
		
		$triples[] = array(SMOBTools::uri($this->uri), "a", "sioct:MicroblogPost");
		$triples[] = array("sioc:has_creator", SMOBTools::uri($user_uri));
		$triples[] = array("foaf:maker", SMOBTools::uri($foaf_uri));
		$triples[] = array("dct:created", SMOBTools::literal($this->ts));
		$triples[] = array("dct:title", SMOBTools::literal("Update - ".$this->ts));
		$triples[] = array("sioc:content", SMOBTools::literal($content));

		foreach ($reply_ofs as $reply_of)
			$triples[] = array("sioc:reply_of", SMOBTools::uri($reply_of));

		$opo_uri = $this->uri.'#presence';
		$triples[] = array(SMOBTools::uri($opo_uri), "a", "opo:OnlinePresence");
		$triples[] = array("opo:declaredOn", SMOBTools::uri($user_uri));
		$triples[] = array("opo:declaredBy", SMOBTools::uri($foaf_uri));
		$triples[] = array("opo:StartTime", SMOBTools::literal($this->ts));
		$triples[] = array("opo:customMessage", SMOBTools::uri($this->uri));
		if($location) {
			$location_pure=substr($location, 0, stripos($location,","));
			$location_uri=find_geo_uri($location_pure);
			$triples[] = array("opo:currentLocation", SMOBTools::uri($location_uri));
		}
		
		if($mappings) {
			$mp = explode(' ', $mappings);
			foreach($mp as $m) {
				$mapping = explode('--', $m);
				if($mapping[0] == 'user') {
					$user = $mapping[1];
					$uri = $mapping[2];
					$triples[] = array(SMOBTools::uri($this->uri), "sioc:addressed_to", SMOBTools::uri($uri));
					$triples[] = array(SMOBTools::uri($uri), "sioc:name", SMOBTools::literal($user));
				}
				elseif($mapping[0] == 'tag') {
					$tag = $mapping[1];
					$uri = $mapping[2];
					$tagging = "${smob_root}tagging/".uniqid();
					$triples[] = array(SMOBTools::uri($tagging), "a", "tags:RestrictedTagging");
					$triples[] = array(SMOBTools::uri($tagging), "tags:taggedResource", SMOBTools::uri($this->uri));
					$triples[] = array(SMOBTools::uri($tagging), "tags:associatedTag", SMOBTools::literal($tag));
					$triples[] = array(SMOBTools::uri($tagging), "moat:tagMeaning", SMOBTools::uri($uri));
					$triples[] = array(SMOBTools::uri($this->uri), "moat:taggedWith", SMOBTools::uri($uri));
				}
			}
		}

		$this->triples = $this->triples + $triples;
	
	}
	
	private function uri() {
		global $smob_root;
		$this->uri = "${smob_root}post/".$this->ts;
		return $this->uri;
	}
	
	public function save() {
		$post_uri = $this->uri();
		$rdf = SMOBTools::render_sparql_triples($this->triples);	
		$query = "INSERT INTO <${post_uri}.rdf> { $rdf }";
		SMOBStore::query($query);
		print '<li>Message saved locally !</li>';
	}
	
	public function notify() {
		$followers = SMOBTools::followers();
		foreach($followers as $follow) {
			$endpoint = str_replace('user/owner', 'sparql.php', $follow['uri']);
			$query = 'query='.urlencode('LOAD <'.$this->uri.'>');
			$res = SMOBTools::do_curl($endpoint, $query);
		}
		print '<li>Message sent to your followers</li>';
	}
	
	public function tweet() {
		global $twitter_user, $twitter_pass;
		$dest = 'http://twitter.com/statuses/update.xml';
		$postfields = 'status='.urlencode($this->content).'&source=smob';
		$userpwd = $twitter_user.':'.$twitter_pass;
		SMOBTools::do_curl($dest, $postfields, $userpwd);
		return 'Notified on Twitter !';
	}
	
	public function raw() {	
		$uri = $this->uri;
		$query = "
SELECT *
WHERE { 
	GRAPH <$uri.rdf> {
		?s ?p ?o
	}
}";
		$data = SMOBStore::query($query);
		header('Content-Type: text/turtle; charset=utf-8'); 
		foreach($data as $triple) {
			$s = $triple['s'];
			$p = $triple['p'];
			$o = $triple['o'];	
			$ot = $triple['o type'];	
			echo "<$s> <$p> ";
			echo ($ot == 'uri') ? "<$o> " : "\"$o\"";
			echo ".\n" ;
		}
		exit();
	}
		
}
