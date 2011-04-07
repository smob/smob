<?php
            
require_once(dirname(__FILE__).'/../lib/smob/SMOB.php'); 

if(file_exists($arc)) {
	include_once($arc);
}

if(isset($_GET['cmd'])){ 
	$cmd = $_GET['cmd'];

	if($cmd =="create-db") {
		echo createDB();         
	} 
	elseif($cmd =="setup-smob") {
		setupSMOB();         
	}
	
	elseif($cmd =="setup-user") {
		setupUser();         
	}
	
	else echo "<p>Sorry, I didn't understand the command ...</p>";            
}

function createDB(){
	$host =  urldecode($_GET['host']);
	$name =  $_GET['name'];
	$user = $_GET['user'];
	$pwd = $_GET['pwd'];
	$store = $_GET['store'];

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
			return createStore($host, $name, $user, $pwd, $store); 
		}
		else {
			$ret .= "Error creating database: " . mysql_error() . "</p>";
		}
	}
	else $ret .= "The database '$name' already exists. We are ready to create an RDF store.</p>";
	
	mysql_close($con);
	
	return createStore($host, $name, $user, $pwd, $store);

}

function createStore($host, $name, $user, $pwd, $store_name){

	include_once(dirname(__FILE__).'/../lib/arc/ARC2.php');
		 
	$config = array(
	  'db_host' => $host,
	  'db_name' => $name,
	  'db_user' => $user,
	  'db_pwd' => $pwd,
	  'store_name' => $store_name,
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
include_once(dirname(__FILE__).'/../lib/xmlrpc/lib/xmlrpc.inc');

define('DB_HOST', '$host');
define('DB_NAME', '$name');
define('DB_USER', '$user');
define('DB_PASS', '$pwd');
define('DB_STORE', '$store_name');
	
";
	$f = fopen(dirname(__FILE__).'/../config/config.php', 'w');
	fwrite($f, $config);
	fclose($f);
	
}

function setupSMOB() {
	$smob_root = $_GET['smob_root'];
	if(substr($smob_root, -1) != '/') {
		$smob_root = "$smob_root/";
	}		
	$purge = $_GET['purge'];
	
// Default HUB_URL for the publisher
// @TODO: ask the user about the Hub?, or where is it better to store this global?
	$config = "
define('SMOB_ROOT', '$smob_root');
define('PURGE', '$purge');
define('HUB_URL', 'http://pubsubhubbub.appspot.com/');
";

	$f = fopen(dirname(__FILE__).'/../config/config.php', 'a');
	fwrite($f, $config);
	fclose($f);
	
	print "<p>Settings saved.</p>";
}


function setupUser() {
	
	include_once(dirname(__FILE__).'/../config/config.php');
	
	$foaf_uri = $_GET['foaf_uri'];
		
	$twitter_read = ($_GET['twitter_read'] == 'on') ? 1 : 0;
	$twitter_post = ($_GET['twitter_post'] == 'on') ? 1 : 0;

	$twitter_login = $_GET['twitter_login'];
	$twitter_pass = $_GET['twitter_pass'];
	
	$auth = $_GET['auth'];
	
	if($foaf_uri) {
		if(!SMOBTools::checkFoaf($foaf_uri)) {
			print "<p>An error occurred with your FOAF URI. <b>Please ensure that it dereferences to an RDF file and that this file contains information about your URI.<b><br/>You will have to <a href='$smob_root'>restart the install process<a/></p>";
			unlink(dirname(__FILE__).'/../config/config.php');
			die();
		}
	} else {
		if(!$foaf_uri) {
			$foaf_uri = SMOB_ROOT.'me#id';
			$username = $_GET['username'];
			$depiction = $_GET['depiction'];
			$profile = "
INSERT INTO <".SMOB_ROOT."/profile> {			
<$foaf_uri> a foaf:Person ; 
	foaf:name \"$username\" ;
	foaf:depiction <$depiction> .
}";
			SMOBStore::query($profile);
		}
	}
			
	$config = "
define('FOAF_URI', '$foaf_uri');

define('TWITTER_READ', '$twitter_read');
define('TWITTER_POST', '$twitter_post');

define('TWITTER_USER', '$twitter_login');
define('TWITTER_PASS', '$twitter_pass');

define('AUTH', '$auth');
		
?>";

	$f = fopen(dirname(__FILE__).'/../config/config.php', 'a');
	fwrite($f, $config);
	fclose($f);
	
	print "<p>Enjoy, you can now access your <a href='.'>SMOB Hub</a> !<br/>
	Log-in using the 'Authenticate' link and start writing microblog po.<br/>
	Also, be sure to restrict access to the <code>config/</code> directory.</p>";
}

?>
