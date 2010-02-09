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
SELECT DISTINCT ?content ?author ?creator ?date ?presence ?reply_of ?reply_of_of ?depiction ?name ?location ?locname
WHERE {
<$uri> rdf:type sioct:MicroblogPost ;
	sioc:content ?content ;
	sioc:has_creator ?creator ;
	foaf:maker ?author ;
	dct:created ?date .
?presence opo:customMessage <$uri> .
	OPTIONAL { <$uri> sioc:reply_of ?reply_of. }
	OPTIONAL { ?reply_of_of sioc:reply_of <$uri> . }
	OPTIONAL { ?author foaf:depiction ?depiction. } 
	OPTIONAL { ?author foaf:img ?depiction . }
	OPTIONAL { ?author foaf:name ?name . }
	OPTIONAL {
		?presence opo:currentLocation ?location .
		?location rdfs:label ?locname .
	}
} ";
		$res = SMOBStore::query($query);
		$this->data = $res[0];
		$this->process_content();
	}
	
	// Process the content to get #tags and @replies and embeds sioc:topic in it
	private function process_content() {
		// Process hyperlinks
		$this->data['content'] = preg_replace( "`(http|ftp)+(s)?:(//)((\w|\.|\-|_)+)(/)?(\S+)?`i", "<a href=\"\\0\" target=\"_blank\">http://\\4</a>", $this->data['content']); 
		// Process users
		$users = $this->get_users();
		if($users) {
			foreach($users as $t) {
				$user = $t['user'];
				$name = $t['name'];
				$r = "<span class=\"topic\" rel=\"sioc:addressed_to\" href=\"$user\"><a href=\"$user\" target=\"_blank\">$name</a></span>";
				$this->data['content'] = str_replace("@$name", "@$r", $this->data['content']);
			}
		}
		// Process tags
		$tags = $this->get_tags();
		if($tags) {
			foreach($tags as $t) {
				$tag = $t['tag'];
				$resource = $t['uri'];
				$enc = SMOBTools::get_uri($resource, 'resource');
				$sigma = "http://sig.ma/search?q=$resource&raw=1";
				$r = "<span class=\"topic\" rel=\"moat:taggedWith sioc:topic ctag:isAbout\" href=\"$resource\"><a href=\"$enc\">$tag</a></span>";
				$this->data['content'] = str_replace("#$tag", "#$r", $this->data['content']);
				$this->data['content'] = str_replace("L:$tag", "L:$r", $this->data['content']);
			}
		}
		return;
		}
	
	// Render the post as RSS 1.0 item
	public function rss() {
		$uri = $this->uri;
		$content = $this->data['content'];
		$ocontent = strip_tags($content);
		$date = $this->data['date'];
		$name = $this->data['name'];
		
		$item = "	
<item rdf:about=\"$uri\">
	<title>$ocontent</title>
	<link>$uri</link>
	<description>$ocontent</description>
	<dc:creator>$name</dc:creator>
	<dc:date>$date</dc:date>
	<content:encoded><![CDATA[$content]]></content:encoded>
</item>
";
		return $item;
	}
	
	// Render the post in RDFa/XHTML
	public function render() {
		global $sioc_nick, $count;

		$uri = $this->uri;
		
		$content = $this->data['content'];
		$ocontent = strip_tags($content);
		$author = $this->data['author'];
		$creator = $this->data['creator'];
		$date = $this->data['date'];
		$name = $this->data['name'];
		$reply_of = $this->data['reply_of'];
		$reply_of_of = $this->data['reply_of_of'];
		$presence = $this->data['presence'];
		$location = $this->data['location'];
		$locname = $this->data['locname'];
		$star = $this->get_star();

		$pic = SMOBTools::either($this->data['depiction'], SMOB_ROOT.'img/avatar-blank.jpg');
		$class = strpos($uri, SMOB_ROOT) !== FALSE ? "post internal" : "post external";
		$ht .= "<div about=\"$presence\" rel=\"opo:customMessage\">\n";

		$ht .= "<div class=\"$class\" typeof=\"sioct:MicroblogPost\" about=\"$uri\">\n";

		$ht .= "<span style=\"display:none;\" rel=\"sioc:has_container\" href=\"".SMOB_ROOT."\"></span>\n";

		$ht .= "<img about=\"$author\" rel=\"foaf:depiction\" href=\"$pic\" src=\"$pic\" class=\"depiction\" alt=\"Depiction for $name\"/>";
		$ht .= "  <span class=\"content\" property=\"content:encoded\">$content</span>\n";
		$ht .= "  <span style=\"display:none;\" property=\"sioc:content\">$ocontent</span>\n";
		$ht .= '  <div class="infos">';
		$ht .= "  by <a class=\"author\" rel=\"foaf:maker\" href=\"$author\"><span property=\"foaf:name\">$name</span></a> - \n";
		if($location) {
			$ht .= "  location: <span about=\"$presence\"><a rel=\"opo:currentLocation\" href=\"$location\"><span property=\"rdfs:label\">$locname</span></a></span><br/>\n";	
		} else {
			$ht .= "  location: <span about=\"$presence\">unspecified</span><br/>\n";	
		}
		$ht .= "  <div style=\"margin: 2px;\"></div> ";
		$ht .= "  <div id=\"star$count\" class=\"rating\">&nbsp;</div>";		
		$ht .= "  <span style=\"display:none;\" rel=\"sioc:has_creator\" href=\"$creator\"></span>\n";
		$ht .= "  <a href=\"$uri\" class=\"date\" property=\"dcterms:created\">$date</a>\n";
		if(strpos($uri, 'http://twitter.com/') !== FALSE) {
			$ex = explode('/', $uri);
			$data = SMOB_ROOT.'data/twitter/' . $ex[5];
		} else { 
			$data = str_replace('post', 'data', $uri);
		}
		$ht .= " [<a href=\"$data\">RDF</a>]\n";
		if(SMOBAuth::check()) {
			if(strpos($uri, SMOB_ROOT) !== FALSE) {
				$ex = explode('/', $uri);
				$action = SMOB_ROOT.'delete/'.$ex[5];
				$ht .= " [<a href=\"$action\" onclick=\"javascript:return confirm('Are you sure ? This cannot be undone.')\">Delete post</a>]";			
			} 
			$action = $this->get_publish_uri();
			$ht .= " [<a href=\"$action\">Post a reply</a>]\n";
		}
		if ($reply_of) {
			$action = SMOBTools::get_uri($reply_of, 'post');
			$ht .= " [<a href=\"$action\">Replied message</a>]\n";
		}
		if ($reply_of_of) {
			$action = SMOBTools::get_uri($reply_of_of, 'post');
			$ht .= " [<a href=\"$action\">Replies</a>]\n";
		}		
		$ht .= '  </div>';
		$ht .= '</div>';
		$ht .= "</div>\n\n";
		$ht .= "<script>
$(document).ready(function(){
	$('#star$count').rating('ajax/star.php?u=$uri', {maxvalue: 1, curvalue: $star});
	});
</script>";		
		return $ht;
	}
	
	private function get_star() {
		$uri = $this->uri;
		$pattern = "{ <$uri> rev:rating \"1\"^^xsd:integer . }";
		$res = SMOBStore::query("ASK $pattern", true);
		return ($res==1) ? $res : 0;
	}
	
	// URI for publishing
	private function get_publish_uri() {
		return SMOB_ROOT.'?r=' . urlencode($this->uri);
	}
		
	// Get the users mentioned in that post	
	private function get_users() {
		$post = $this->uri;
		$query = "
SELECT ?user ?name
WHERE {
	<$post> sioc:addressed_to ?user .
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
	
	public function set_data($ts, $content, $reply_of, $location, $location_uri, $mappings) {

		$user_uri = SMOBTools::user_uri();
		$this->ts = $ts;
		$this->content = $content;
		$this->uri($ts);
		
		$triples[] = array(SMOBTools::uri($this->uri), "a", "sioct:MicroblogPost");
		$triples[] = array("sioc:has_container", SMOBTools::uri(SMOB_ROOT));
		$triples[] = array("sioc:has_creator", SMOBTools::uri($user_uri));
		$triples[] = array("foaf:maker", SMOBTools::uri(FOAF_URI));
		$triples[] = array("dct:created", SMOBTools::date($this->ts));
		$triples[] = array("dct:title", SMOBTools::literal("Update - ".$this->ts));
		$triples[] = array("sioc:content", SMOBTools::literal($content));
		if($reply_of) {
			$triples[] = array("sioc:reply_of", SMOBTools::uri($reply_of));			
		}

		$triples[] = array(SMOBTools::uri(SMOB_ROOT), "a", "smob:Hub");

		$opo_uri = $this->uri.'#presence';
		$triples[] = array(SMOBTools::uri($opo_uri), "a", "opo:OnlinePresence");
		$triples[] = array("opo:declaredOn", SMOBTools::uri($user_uri));
		$triples[] = array("opo:declaredBy", SMOBTools::uri(FOAF_URI));
		$triples[] = array("opo:StartTime", SMOBTools::date($this->ts));
		$triples[] = array("opo:customMessage", SMOBTools::uri($this->uri));
		if($location_uri) {
			$triples[] = array("opo:currentLocation", SMOBTools::uri($location_uri));
			$triples[] = array(SMOBTools::uri($location_uri), "rdfs:label", SMOBTools::literal($location));
			SMOBStore::query("LOAD <$location_uri>");
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
				elseif($mapping[0] == 'tag' || $mapping[0] == 'location') {
					$tag = $mapping[1];
					$uri = $mapping[2];
					$tagging = SMOB_ROOT.'tagging/'.uniqid();
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
		$this->uri = SMOB_ROOT.'post/'.$this->ts;
	}
	
	private function graph() {
		return str_replace('/post/', '/data/', $this->uri);
	}
	
	public function save() {
		$graph = $this->graph();
		$rdf = SMOBTools::render_sparql_triples($this->triples);	
		$query = "INSERT INTO <$graph> { $rdf }";
		SMOBStore::query($query);
		print '<li>Message saved locally !</li>';
	}
	
	public function delete() {
		$uri = $this->uri; 
		$graph = str_replace('/post/', '/data/', $uri);
		SMOBStore::query("DELETE FROM <$graph>");
		$this->notify('DELETE FROM');
	}
	
	public function notify($action = 'LOAD') {
		$followers = SMOBTools::followers();
		if($followers) {
			foreach($followers as $follow) {
				// In case some hubs are still in 2.0
				$uri = $follow['uri'];
				if (substr($uri, -2) == 'me') {
					$endpoint = substr($uri, 0, -2) . 'sparql';
				} else {
					$endpoint = $uri . 'sparql';
				}
				$graph = $this->graph();
				$query = 'query='.urlencode("$action <$graph>");
				$res = SMOBTools::do_curl($endpoint, $query);
			}
			if($action == 'LOAD') {
				print '<li>Notification sent to your followers !</li>';
			} else {
				return;
			}
		}
	}

	public function sindice() {
		$client = new xmlrpc_client("http://sindice.com/xmlrpc/api");
		$payload = new xmlrpcmsg("weblogUpdates.ping");
   
		$payload->addParam(new xmlrpcval($this->content));
		$payload->addParam(new xmlrpcval($this->uri));
   
		$response = $client->send($payload);
		$xmlresponsestr = $response->serialize();
   
		$xml = simplexml_load_string($xmlresponsestr);
		$result = $xml->xpath("//value/boolean/text()");
		if($result) {
			if($result[0] == "0"){
				print '<li>Message sent to Sindice !</li>';
 			}
		} else {
			$code = $response->faultCode();
			$err = $response->faultString();
			print '<li>Failed to submit to Sindice ($code: $err)</li>';
		}
	}
	
	public function tweet() {
		$dest = 'http://twitter.com/statuses/update.xml';
		$postfields = 'status='.urlencode($this->content).'&source=smob';
		$userpwd = TWITTER_USER.':'.TWITTER_PASS;
		SMOBTools::do_curl($dest, $postfields, $userpwd);
		print '<li>Notified on Twitter !</li>';
	}
	
	public function raw() {	
		$uri = $this->graph();
		$query = "
SELECT *
WHERE { 
	GRAPH <$uri> {
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
			$odt = in_array('o datatype', array_keys($triple)) ? '^^<'.$triple['o datatype'].'>' : '';
			echo "<$s> <$p> ";
			echo ($ot == 'uri') ? "<$o> " : "\"$o\"$odt ";
			echo ".\n" ;
		}
		exit();
	}
		
}
