<?php 

require_once(dirname(__FILE__).'/../lib/smob/client.php'); 

if(!file_exists(dirname(__FILE__)."/../config.php")) {
	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'../install';
	header("Location: $url");
} 

require_once(dirname(__FILE__)."/../config.php");

parse_str($_SERVER['QUERY_STRING']);

if($view) {
	$title = "Update $view";
	$content = show_post($view);
} else {
	$title = "Post for $sioc_nick";
	$content = show_posts();
}

smob_go($title, $content);

?>
