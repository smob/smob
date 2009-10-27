<?php 

require_once(dirname(__FILE__).'/lib/lib.php'); 
if(!file_exists(dirname(__FILE__)."/../config.php")) {
	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'../install';
	header("Location: $url");
} 

require_once(dirname(__FILE__)."/../config.php");

smob_header();

show_posts();
show_networks();

smob_footer();

?>
