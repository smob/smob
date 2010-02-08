<?php

class SMOBPostListMap extends SMOBPostList {

	public function title() {
		return 'Map view (last 100 geo-located posts)';
	}
	
	public function load_vars() {
		return ' ?lat ?long';
	}
	
	public function load_pattern() {
		return '
?presence opo:currentLocation ?location .
?location geo:lat ?lat  ; geo:long ?long . ';
	}

	private function get_location() {
		$loc = SMOBTools::location();
		if($loc) {
			$uri = $loc[0];
			$loc = SMOBStore::query("SELECT ?lat ?long WHERE { <$uri> geo:lat ?lat ; geo:long ?long .}");
			return array($loc[0]['lat'], $loc[0]['long']);
		} else {
			return array(0, 0);
		}
	}
	
	public function render() {
		if($posts = $this->posts) {
			foreach($posts as $post) {
				$lat = $post->data['lat'];
				$long = $post->data['long'];
				$uri = $post->data['post'];
				$content = $post->data['content'];
				$name = $post->data['name'];
				$geo[$lat][$long][] = array($uri, $content, $name);
			}
			foreach($geo as $lat=>$g) {
				foreach($g as $long=>$posts) {
					$content = '<ul>';
					foreach($posts as $p) {
						list($url, $txt, $name) = $p;
						$txt = strip_tags($txt);
						$txt = str_replace("'", "\'", $txt);
						$content .= "<li><a href=\"$url\">$txt</a> by $name</li>";
					}
					$content .= '</ul>';
			    	$js = "
	var location = new google.maps.LatLng($lat, $long);
	var marker = new google.maps.Marker({
		position: location, 
		map: map
	});
	var infowindow = new google.maps.InfoWindow({ 
		content: '$content'
	});
	google.maps.event.addListener(marker, 'click', function() { infowindow.open(map,marker); });
";
				}
			}
		}
		list($lat, $long) = $this->get_location();
		$ht = "<script type=\"text/javascript\">
function map() {
	var myLatlng = new google.maps.LatLng($lat, $long);
	var myOptions = {
		zoom: 2,
		center: myLatlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	}
	map = new google.maps.Map(document.getElementById(\"map_canvas\"), myOptions);
	$js
}
</script>";
		$ht .= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>';
		$ht .= '<div id="map_canvas" style="width:100%; height:60%"></div>';
		return $ht;
	}
	
}
