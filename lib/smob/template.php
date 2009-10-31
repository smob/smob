<?php

function smob_header() {
	global $sioc_nick, $root;
	
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" 
  "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">

<html
  xmlns="http://www.w3.org/1999/xhtml" 
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:foaf="http://xmlns.com/foaf/0.1/" 
  xmlns:sioc="http://rdfs.org/sioc/ns#"
  xmlns:sioct="http://rdfs.org/sioc/types#"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
xml:lang="fr">
 
<head profile="http://ns.inria.fr/grddl/rdfa/">
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <title>SMOB</title>
  <link rel="stylesheet" type="text/css" href="<?php echo "$root/css/style.css"; ?>" />
  <link rel="stylesheet" type="text/css" href="<?php echo "$root/install/install.css"; ?>" />
  <script type="text/javascript" src="<?php echo "$root/js/jquery-1.3.2.min.js"; ?>"></script>
  <script type="text/javascript" src="<?php echo "$root/js/jquery.timers-1.2.js"; ?>"></script>
  <script type="text/javascript" src="<?php echo "$root/js/ui.core.js"; ?>"></script>
  <script type="text/javascript" src="<?php echo "$root/js/smob.js"; ?>"></script>

  <script type="text/javascript">
	var state = 0;
	var maxstate = 6;
	$(function() {
		$("#step").click(function () {
			process();
		});
	});
	$(function() { 
		$("#ts").everyTime(5000,function(i) {
			getnews();
		});
	});
					
//	$(document).everyTime(1000, function(i) {
//	  alert('ok');
//	}, times);
</script>
</head>

<body>

<div id="full">

<div id="header">
<h1><a href="<?php echo "$root/client"; ?>">SMOB</a></h1>
<h2>This is the SMOB hub for <?php echo $sioc_nick; ?></h2>
</div>

<div id="main">

<div class="left"> 

<?
}

function smob_footer($blocks) {
?>

</div>

<div class="right"> 

<?php echo $blocks; ?>

</div>

<div style="clear: both;"> </div>
</div>

<div id="footer">
Powered by <a href="http://smob.siob-project.org/">SMOB</a> thanks to <a href="http://www.w3.org/2001/sw/">Semantic Web</a> technologies and <a href="http://linkeddata.org">Linked Data</a><br/>
</div>
</div>

</body>

</html>
<?
}