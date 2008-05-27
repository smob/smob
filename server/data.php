<?php

include_once(dirname(__FILE__).'/config.php');

// PHP before 5.2.0 doesn't have JSON support built-in
// This is based on the diagram on http://json.org/
function json_quote($text) {
  $encoding = array(
    "\\" => "\\\\",
    "\"" => "\\\"",
    "\b" => "\\b",
    "\f" => "\\f",
    "\n" => "\\n",
    "\r" => "\\r",
    "\t" => "\\t",
  );
  $text = str_replace(array_keys($encoding), array_values($encoding), $text);

  // convert remaining ASCII control characters to json unicode escapes
  $text = preg_replace_callback("/[\\x00-\\x1f\\x7f]/", create_function( 
              '$s', 'return sprintf("\\u%04x", ord($s[0]));'), $text);
  return $text;
}

function curl_get_content($url) {
  $ch = curl_init();
  $timeout = 5; 
  curl_setopt ($ch, CURLOPT_URL, $url);
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

$store = ARC2::getStore($arc_config);
if (!$store->isSetUp()) {
  $store->setUp();
}

$q = "
PREFIX sioc: <http://rdfs.org/sioc/ns#>
PREFIX sioct: <http://rdfs.org/sioc/types#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX dct: <http://purl.org/dc/terms/>
select distinct ?post ?date ?content ?maker ?name ?depiction
where { 
  ?post rdf:type sioct:MicroBlogPost ;
    foaf:maker ?maker ;
    sioc:content ?content ;
    dct:created ?date .
  { ?maker foaf:name ?name } union { ?post sioc:has_creator ?user . 
                                     ?user sioc:name ?name } .
  { ?maker foaf:img ?depiction } union { ?maker foaf:depiction ?depiction } 
    union { ?maker foaf:thumbnail ?depiction }
} ORDER BY DESC(?date) LIMIT 200
";

$rs = $store->query($q);

// delete duplicate posts XXX we should pick a non-random row :-)
$distinct_rows = array();
foreach ($rs['result']['rows'] as $row) 
  $distinct_rows[$row['post']] = $row;

foreach ($distinct_rows as $row) {
  // import the bindings of the result row as php variables
  foreach ($row as $k => $v) { $kn = str_replace(' ', '_', $k); $$kn = $v; }

  $day = date("Y-m-d", strtotime($date));

  // Prettify the plain text content

  // trying to be smart in extracting URIs, 
  // based on http://tuukka.iki.fi/irclogs
  $content = preg_replace("#(http(s)?://[^\s]*[^\s,.:])(\)(,?\s|$))?#", 
                          "<a href='\\1'>\\1</a>\\3", $content);


  // Retrieve topics from content
  // should be added in the triple store directly 
  // (when inserting new data ?)

  // XXX which unicode characters should be allowed in tags?
  preg_match_all("/(^|\s)#([\w\pL]+(:[\w\pL]+)?)/su", $content, $match);
  $topics = '';
  $locations = '';
  $latlng = '';
  foreach($match[2] as $t) {
    $ex = explode(':', $t);
    if($ex[0]=='geo') {
      $place = $ex[1];
      $locations .= '"'.$ex[1].'", ';
      $geo = "http://ws.geonames.org/search?q=$place&maxrows=1&type=json&maxRows=1";
      $data = curl_get_content($geo);
      $d = json_decode($data);
      $loc = $d->geonames[0];
      $id = $loc->geonameId;
      $url = "http://www.geonames.org/$id";
      $lng = $loc->lng;
      $lat = $loc->lat;
      $latlng = "$lat,$lng";
      $content = str_replace($t, "<a href='$url'>$t</a>", $content);
    } elseif($ex[0]=='dbp') {
      $url = 'http://dbpedia.org/resource/'.$ex[1];
      $content = str_replace($t, "<a href='$url'>$t</a>", $content);
      $topics .= "\"$t\", "; 
    } else {
      $topics .= '"' . mb_strtolower($t, "utf-8") . '", '; 
    }
  }
  $topics = substr($topics, 0, -2);
  $locations = substr($locations, 0, -2);
  $content = json_quote($content); // FIXME quote other parts properly too
  $json .= "\n{ type: \"MicroBlogPost\", label: \"$date\", date: \"$date\", day: \"$day\", content: \"$content\", name: \"$name\", depiction: \"$depiction\", topics: [$topics], locations: [$locations], latlng: \"$latlng\"},";
}

echo "{\"items\" : [";
echo substr($json, 0, -1);
echo '] }'; 

?>
