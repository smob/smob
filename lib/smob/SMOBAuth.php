<?

/* 
	Helper methods for the authentication
*/

class SMOBAuth {
	
	function check() {
		session_start();
		return $_SESSION['grant'];
	}
	
	function grant() {
		session_start();
		$_SESSION['grant'] = true;
	}
}