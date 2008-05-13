<?php

// Addresses of the servers where you want to publish your data
// Use 'server' => 'apikey' format (leave API key empty if you didn't get one)
$servers = array(
  'http://smob.sioc-project.org/server' => '',
  'http://microplanet.sioc-project.org/' => '',
);

// The following settings configure your identity settings
// FOAF URL is the http location of your FOAF profile file
$foaf_url = 'http://apassant.net/foaf.rdf';
// FOAF URI identifies you and should be described in your FOAF profile
$foaf_uri = 'http://apassant.net/alex';
// SIOC nick is used as your microblogger user name
$sioc_nick = 'alex';

// If you want to be able to relay your messages to Twitter,
// you can provide your Twitter user name and password here
$twitter_user = '';
$twitter_pass = '';

?>
