<?php 

require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/lib/lib.php'); 

smob_header();

show_posts();
show_networks();

smob_footer();

?>
