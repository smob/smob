<?php
            
$arc = dirname(__FILE__)."/../arc/ARC2.php";
if(file_exists($arc)) {
	include_once($arc);
}
  
$DEBUG = false;

if(isset($_GET['cmd'])){ 
	$cmd = $_GET['cmd'];

	if($cmd =="get-files") {
		echo getFiles();
	}
	elseif($cmd =="create-db") {
		echo createDB();         
	} 
	elseif($cmd =="setup-smob") {
			setupSMOB();         
	}
	else echo "<p>Sorry, I didn't understand the command ...</p>";            
}

function getFiles() {
	global $DEBUG;
	$wget =  $_GET['wget'];
	$curl = $_GET['curl'];
	$tar = $_GET['tar'];
	
	$arc = "http://code.semsol.org/source/arc.tar.gz";
	$local = dirname(__FILE__);
	
	if($tar && file_exists("${tar}tar") && ($wget && file_exists("${wget}/wget") || $curl && file_exists("${curl}/curl"))) {
		if($wget && file_exists("${wget}/wget")) {
			$getfile = "${wget}wget";
		}
		else {
			$getfile = "${curl}curl -O";
		}
		exec("cd $local/../ ; $getfile $arc ; tar -xf arc.tar.gz");
		return "ARC2 properly downloaded and installer";
	}
	return "Cannot download the files, please install ARC2 manually";
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
			return createStore($host, $name, $user, $pwd, $store_name); 
		}
		else {
			$ret .= "Error creating database: " . mysql_error() . "</p>";
		}
	}
	else $ret .= "The database '$name' already exists. We are ready to create an RDF store.</p>";
	
	mysql_close($con);
	
	return createStore($host, $name, $user, $pwd, $store_name);

}

function createStore($host, $name, $user, $pwd, $store_name){
	
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
	$config = "
<?php

	\$arc_config = array(
	  'db_host' => '$host', 
	  'db_name' => '$name',
	  'db_user' => '$user',
	  'db_pwd' => '$pwd',
	  'store_name' => '$store_name',
	);
	
";
	$f = fopen(dirname(__FILE__).'/../config.php', 'w');
	fwrite($f, $config);
	fclose($f);
	
}

function setupSMOB() {
	global $DEBUG;
	$server_key = $_GET['server_key'];
	$server_gmap = $_GET['server_gmap'];
	$client_ping = $_GET['client_ping'];
	$client_url = $_GET['client_url'];
	$client_uri = $_GET['client_uri'];
	$client_nick = $_GET['client_nick'];
	$client_twitter_login = $_GET['client_twitter_login'];
	$client_twitter_pass = $_GET['client_twitter_pass'];
	
	$config = "

	\$servers = array(
	  $client_ping
	);

	\$foaf_url = '$client_url';
	\$foaf_uri = '$client_uri';
	\$sioc_nick = '$client_nick';
	
	\$twitter_user = '$client_twitter_login';
	\$twitter_pass = '$client_twitter_pass';
	
	// @@TODO
	// \$laconica['http://identi.ca/'] = array('user' => '', 'pass' => '');


?>";
	
	$f = fopen(dirname(__FILE__).'/../config.php', 'a');
	fwrite($f, $config);
	fclose($f);
	
	print "<p>Enjoy, you can now access the SMOB <a href='../client'>client</a> and <a href='../server'>server</a> !</p>";
}

?>
