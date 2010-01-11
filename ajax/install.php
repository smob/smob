<?php
            
require_once(dirname(__FILE__).'/../lib/smob/SMOB.php'); 

if(file_exists($arc)) {
	include_once($arc);
}
  
$DEBUG = false;

if(isset($_GET['cmd'])){ 
	$cmd = $_GET['cmd'];

	if($cmd =="create-db") {
		echo createDB();         
	} 
	elseif($cmd =="setup-smob") {
			setupSMOB();         
	}
	else echo "<p>Sorry, I didn't understand the command ...</p>";            
}

function createDB(){
	global $DEBUG;
	$host =  urldecode($_GET['host']);
	$name =  $_GET['name'];
	$user = $_GET['user'];
	$pwd = $_GET['pwd'];

	$ret = "<p>";
	$dbExists = false;
	
	$con = mysql_connect($host, $user, $pwd); // try to connect
	if (!$con){
	  die('Could not connect: ' . mysql_error());
	}
	
	$dblist = mysql_list_dbs($con); // check if the database already exists
	while ($row = mysql_fetch_object($dblist)) {
	     $db = $row->Database;
		 if ($db == $name) $dbExists = true;
	}
	
	if(!$dbExists) {
		if (mysql_query("CREATE DATABASE " . $name, $con)) {
			return createStore($host, $name, $user, $pwd); 
		}
		else {
			$ret .= "Error creating database: " . mysql_error() . "</p>";
		}
	}
	else $ret .= "The database '$name' already exists. We are ready to create an RDF store.</p>";
	
	mysql_close($con);
	
	return createStore($host, $name, $user, $pwd);

}

function createStore($host, $name, $user, $pwd){

	include_once(dirname(__FILE__).'/../lib/arc/ARC2.php');
	
	$store_name = 'smob';
	 
	$config = array(
	  'db_host' => $host,
	  'db_name' => $name,
	  'db_user' => $user,
	  'db_pwd' => $pwd,
	  'store_name' => $store_name
	);
	
	$store = ARC2::getStore($config);

	if (!$store->isSetUp()) {
		$store->setUp();
		print "<p>Database correctly set-up.</p>";
	} else {
		print "<p>The store was already set up.</p>";
	}
	
	// write databsed information in the config file
	$config = "<?php

	include_once(dirname(__FILE__).'/../lib/arc/ARC2.php');

	\$arc_config = array(
		'db_host' => '$host', 
		'db_name' => '$name',
		'db_user' => '$user',
		'db_pwd' => '$pwd',
		'store_name' => '$store_name',
	
		'store_triggers_path' => dirname(__FILE__).'/../lib/smob/',
		'store_triggers' => array(
			'insert' => array('loadTrigger'),
		),
		'endpoint_features' => array(
	    	'select', 'construct', 'ask', 'describe', 'load'
		),
		'sem_html_formats' => 'rdfa',
	);
	
";
	$f = fopen(dirname(__FILE__).'/../config/config.php', 'w');
	fwrite($f, $config);
	fclose($f);
	
}

function setupSMOB() {
	global $DEBUG;
	$smob_root = $_GET['smob_root'];
	$client_uri = $_GET['client_uri'];
	$client_twitter_login = $_GET['client_twitter_login'];
	$client_twitter_pass = $_GET['client_twitter_pass'];
	$server_gmap = $_GET['server_gmap'];
	
	$config = "
	\$smob_root = '$smob_root';
	\$foaf_uri = '$client_uri';

	\$twitter_user = '$client_twitter_login';
	\$twitter_pass = '$client_twitter_pass';

	\$gmap_key = '$server_gmap;
	
?>";
	
	$f = fopen(dirname(__FILE__).'/../config/config.php', 'a');
	fwrite($f, $config);
	fclose($f);
	
	print "<p>Enjoy, you can now access your <a href='.'>SMOB Hub</a> !<br/>
	Also, be sure to restrict access to the <code>config/</code> directory.</p>";
}

?>
