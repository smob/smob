<?php require_once(dirname(__FILE__)."/../../config/config.php"); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" 
   "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"> 
<html
  xmlns="http://www.w3.org/1999/xhtml" 
  xmlns:v="urn:schemas-microsoft-com:vml"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:foaf="http://xmlns.com/foaf/0.1/" 
  xmlns:sioc="http://rdfs.org/sioc/ns#"
  xmlns:sioct="http://rdfs.org/sioc/types#"
  xmlns:ctag="http://commontag.org/ns#"
  xmlns:opo="http://online-presence.net/opo/ns#"
  xmlns:smob="http://smob.me/ns#"
  xmlns:moat="http://moat-project.org/ns#"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
  xmlns:rev="http://purl.org/stuff/rev#"
xml:lang="fr"> 
 
<head profile="http://ns.inria.fr/grddl/rdfa/"> 
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" /> 
  <title>SMOB</title> 
  <link rel="icon" type="image/png" href="<?php echo SMOB_ROOT; ?>img/smob-icon.png" /> 
  <link rel="stylesheet" type="text/css" href="<?php echo SMOB_ROOT; ?>css/style.css" /> 
 
  <link type="text/css" href="http://jqueryui.com/latest/themes/base/jquery.ui.all.css" rel="stylesheet" /> 
 
  <script type="text/javascript" src="http://www.google.com/jsapi"></script>  
  <script type="text/javascript">  
    google.load("jquery", "1.4.1");
    google.load("jqueryui", "1.7.2");
  </script> 
  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/jquery.timers-1.2.js"></script> 
  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/jquery.autocomplete-min.js"></script> 
  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/jquery.rating.js"></script> 
 
  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/smob.js"></script> 
  
  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/jquery-dynamic-form.js"></script>  
  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/jquery.form.js"></script> 
  <script type="text/javascript" src="http://dev.jquery.com/view/trunk/plugins/validate/jquery.validate.js"></script>
 
 
  <base href="" /> 
</head> 
<body about="<?php echo SMOB_ROOT; ?>" typeof="smob:Hub sioct:Microblog"> 
 
<div id="full"> 
 
<div id="header"> 
<h1><a href="<?php echo SMOB_ROOT; ?>">SMOB</a></h1> 
<h2><span class="smob">S</span>emantic-<span class="smob">M</span>icr<span class="smob">OB</span>logging</h2> 
</div> 
