<?php

/* 
	Helper methods for the authentication
	FOAF-SSL methods from : https://foaf.me/testLibAuthentication.php	
*/


class SMOBAuth {
	
	function check() {
		session_start();
		return $_SESSION['grant'];
	}
	
	function grant() {
		session_start();
		if(AUTH == 'foafssl') {
		  error_log('foafssl authentication',0);
			$foafssl  = SMOBAuth::getAuth();
			if($foafssl['isAuthenticated']) {
				$_SESSION['grant'] = true;				
			} else {
				$_SESSION['grant'] = false;
			}
		} else {
			$_SESSION['grant'] = true;				
		}
		error_log($foafssl['authDiagnostic'],0);
	}
	
	/* Function to return the modulus and exponent of the supplied Client SSL Page */
	function openssl_pkey_get_public_hex()
	{
		if ($_SERVER[SSL_CLIENT_CERT])
		{
		  error_log('got a certificate',0);
			$pub_key = openssl_pkey_get_public($_SERVER[SSL_CLIENT_CERT]);
			$key_data = openssl_pkey_get_details($pub_key);

			$key_len   = strlen($key_data[key]);
			$begin_len = strlen('-----BEGIN PUBLIC KEY----- ');
			$end_len   = strlen(' -----END PUBLIC KEY----- ');

			$rsa_cert = substr($key_data[key], $begin_len, $key_len - $begin_len - $end_len);

			$rsa_cert_struct = `echo "$rsa_cert" | openssl asn1parse -inform PEM -i`;

			$rsa_cert_fields = split("\n", $rsa_cert_struct);
			$rsakey_offset   = split(":",  $rsa_cert_fields[4]);

			$rsa_key = `echo "$rsa_cert" | openssl asn1parse -inform PEM -i -strparse $rsakey_offset[0]`;

			$rsa_keys = split("\n", $rsa_key);
			$modulus  = split(":", $rsa_keys[1]);
			$exponent = split(":", $rsa_keys[2]);

			return( array( 'modulus'=>$modulus[3], 'exponent'=>$exponent[3] ) );
		}
		error_log('no certificate',0);
	}

	/* Returns an array holding the subjectAltName of the supplied SSL Client Certificate */
	function openssl_get_subjectAltName()
	{
		if ($_SERVER[SSL_CLIENT_CERT])
		{
			$cert = openssl_x509_parse($_SERVER[SSL_CLIENT_CERT]);
			if ($cert[extensions][subjectAltName])
			{
				$list = split("[,]", $cert[extensions][subjectAltName]);

				for ($i = 0, $i_max = count($list); $i < $i_max; $i++) 
				{
					if (strcasecmp($list[$i],"")!=0)
					{
						$value = split(":", $list[$i], 2);
						if ($subject_array)
							$subject_array = array_merge($subject_array, array(trim($value[0]) => trim($value[1])));
						else
							$subject_array = array(trim($value[0]) => trim($value[1]));
					}
				}

				return $subject_array;
			}
		}
	}

	/* Function to clean up teh supplied hex and convert numbers A-F to uppercase characters eg. a:f => AF */
	function cleanhex($hex)
	{
		$hex = eregi_replace("[^a-fA-F0-9]", "", $hex); 
		$hex = strtoupper($hex);
		return($hex);
	}

	/* Returns an array of the modulus and exponent in the supplied RDF */
	function get_foaf_rsakey($uri)
	{
		if ($uri)
		{
			SMOBStore::query('LOAD <'.$uri.'>');

			/* list names */
			$q = '
			  PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
			  PREFIX rsa: <http://www.w3.org/ns/auth/rsa#> 
			  PREFIX cert: <http://www.w3.org/ns/auth/cert#>
			  PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
			  SELECT ?mod ?exp  WHERE {
				?sig cert:identity ?person .
				?sig a rsa:RSAPublicKey;
					rsa:modulus [ cert:hex ?mod ] ;
					rsa:public_exponent [ cert:decimal ?exp ] .
			  }';
			// for some reason the previous query doesn't work in ARC
			// sig is not needed, replazed by bnode
			// eliminate #me as it should be in the certificate URI
			$q = '
			  PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
			  PREFIX rsa: <http://www.w3.org/ns/auth/rsa#> 
			  PREFIX cert: <http://www.w3.org/ns/auth/cert#>
			  PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
			  SELECT ?mod ?exp  WHERE {
				[] a rsa:RSAPublicKey;
          cert:identity <'.$uri.'#me>;
					rsa:modulus ?mod ;
					rsa:public_exponent ?exp .
			  }';
			$res = SMOBStore::query($q);
			if ($res) {
			  // TODO: support several keys for webid uri?
		    $modulus =  SMOBAuth::cleanhex($res[0]['mod']);
		    $exponent =  SMOBAuth::cleanhex($res[0]['exp']);
		    error_log('modulus: ',0);
		    error_log($modulus, 0);
		    error_log('exponent: ',0);
		    error_log($exponent, 0);
      }
			if ($modulus && $exponent)
				return ( array( 'modulus'=>$modulus, 'exponent'=>dechex($exponent) ) );
		}
	}

	/* Function to compare two supplied RSA keys */
	function equal_rsa_keys($key1, $key2)
	{
		if ( $key1 && $key2 && ($key1['modulus'] == $key2['modulus']) && ($key1['exponent'] == $key2['exponent']) )
			return TRUE;

		return FALSE;
	}

	function getAuth($foafuri = NULL)
	{
		if (!$_SERVER[HTTPS])
			return ( array( 'isAuthenticated'=>0 , 'authDiagnostic'=>'No client certificate supplied on an unsecure connection') );

		if (!$_SERVER[SSL_CLIENT_CERT])
			return ( array( 'isAuthenticated'=>0 , 'authDiagnostic'=>'No client certificate supplied') );

    error_log('certificate:',0);
    error_log($_SERVER[SSL_CLIENT_CERT], 0);
		$certrsakey = SMOBAuth::openssl_pkey_get_public_hex();

		if (!$certrsakey)
			return ( array( 'isAuthenticated'=>0 , 'authDiagnostic'=>'No RSA Key in the supplied client certificate') );
    error_log('rsa key:',0);
    error_log($certrsakey['modulus'],0);

		$result = array('certRSAKey'=>$certrsakey);

		$san     = SMOBAuth::openssl_get_subjectAltName();
		$foafuri = $san[URI];
    error_log('foaf ur:',0);
    error_log($foafuri,0);
    
		$result = array_merge($result, array('subjectAltName'=>$foafuri));

		$foafrsakey = SMOBAuth::get_foaf_rsakey($foafuri);

		$result = array_merge($result, array('subjectAltNameRSAKey'=>$foafrsakey));

		if ( SMOBAuth::equal_rsa_keys($certrsakey, $foafrsakey) )
			$result = array_merge($result, array( 'isAuthenticated'=>1,  'authDiagnostic'=>'Client Certificate RSAkey matches SAN RSAkey'));
		else
			$result = array_merge($result, array( 'isAuthenticated'=>0,  'authDiagnostic'=>'Client Certificate RSAkey does not match SAN RSAkey'));

		return $result;
	}

}
