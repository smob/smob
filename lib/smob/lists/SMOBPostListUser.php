<?php

class SMOBPostListPosts extends SMOBPostList {

	public function title() {
		return 'Posts created by ' . $this->uri;
	}
	
	public function load_pattern() {
		return "
	?post rdf:type sioct:MicroblogPost ;
		sioc:content ?content ;
		foaf:maker ?author ;
		dct:created ?date .
	?tagging a tags:RestrictedTagging ;
		tags:taggedResource ?post ;
		moat:tagMeaning <$uri> .";
	}

/*	

	// Browsing a user's contribution
	private function user() {
		$uri = $this->uri;
		$names = explode(" ", "foaf:name foaf:firstName foaf:nick rdfs:label");
		$images = explode(" ", "foaf:depiction foaf:img");
		$misc = explode(" ", "foaf:homepage foaf:weblog foaf:knows");
		$all = array_merge($names, $images, $misc);
		$optionals = SMOBTools::optionals($uri, $all);
		$query = "
SELECT *
WHERE {
{ <$uri> rdf:type foaf:Person . }
$optionals
} ";
		$rs = do_query($query);
		if (!$rs)
			return $rs;
		$rs = SMOBTools::optionals_to_array_of_arrays($all, $rs);
		$rs['names'] = SMOBTools::choose_optional($names, $rs);
		$rs['images'] = SMOBTools::choose_optional($images, $rs);
		return $rs;
	}


	*/