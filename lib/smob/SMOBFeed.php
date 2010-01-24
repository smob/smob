<?

/* 
	Helper methods for the different SMOB Classes
*/

class SMOBFeed {
	
	var $posts;
	
	public function __construct() {
		global $foaf_uri;
		$r = new SMOBPostListUser($foaf_uri, 1);
		$this->posts = $r->posts;
	}

	public function rss() {
		$rss = $this->rss_header();
		foreach($this->posts as $post) {
			$rss .= "\t\t\t<rdf:li rdf:resource=\"" . $post->uri . "\" />\n";
			$items .= $post->rss();
		}
		$rss .= "\t\t</rdf:Seq>\n\t</items>\n</channel>\n";
		$rss .= $items;
		$rss .= "\n</rdf:RDF>";
		echo $rss;
	}
	
	public function rss_header() {
		global $smob_root;
		$version = SMOBTools::version();
		$owner = SMOBTools::ownername();
		$title = "SMOB Hub of $owner";
		$ts = date('c');
		return "<?xml version='1.0' encoding='utf-8'?>

<rdf:RDF
	xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'
	xmlns:dc='http://purl.org/dc/elements/1.1/'
	xmlns='http://purl.org/rss/1.0/'
	xmlns:dcterms='http://purl.org/dc/terms/'
	xmlns:cc='http://web.resource.org/cc/'
	xmlns:content='http://purl.org/rss/1.0/modules/content/'
	xmlns:admin='http://webns.net/mvcb/'
> 

<channel rdf:about='$smob_root'>
	<title>$title</title>
	<link>$smob_root</link>
	<description>$title</description>
	<dc:creator>$owner</dc:creator>
	<dc:date>$ts</dc:date>
	<admin:generatorAgent rdf:resource='http://smob.me/#smob?v=$version' />
	<items>
		<rdf:Seq>
";
	}
	
	
}