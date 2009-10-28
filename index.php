<?php

if(!file_exists(dirname(__FILE__)."/config.php")) {
	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'install';
	header("Location: $url");
} else {
	echo "Go to SMOB <a href='client'>client</a> and <a href='server'>server</a>";
}

?>