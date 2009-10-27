<?php 
if(!file_exists(dirname(__FILE__)."/../config.php")) {
	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'../install';
	header("Location: $url");
} 

require_once(dirname(__FILE__)."/../config.php");

?>
<html>
 <head>
    <title>SMOB server</title>
    <link rel="alternate" type="application/rdf+xml" title="SIOC" href="http://smob.sioc-project.org/server/sparql?query=CONSTRUCT+%7B%3C%3E+rdfs%3AseeAlso+%3Fg%7DWHERE%7BGRAPH%3Fg%7B%3Fs%3Fp%3Fo%7D%7D" />
    <link href="data.php" type="application/json" rel="exhibit/data" />
    <script src="http://static.simile.mit.edu/exhibit/api-2.0/exhibit-api.js" type="text/javascript"></script>
    <script src="http://static.simile.mit.edu/exhibit/extensions-2.0/time/time-extension.js" type="text/javascript"></script>
    <script src="http://static.simile.mit.edu/exhibit/extensions-2.0/map/map-extension.js?gmapkey=<?php echo $gmap_key; ?>"></script>
    <style type="text/css">
body {
  font-family: Helvetica, Tahoma, Arial, sans serif;
  font-size: 80%;
  width: 80%;
  margin: auto;
}

table.lens {
  border-top: 1px solid #ccc;
  margin-bottom: 2px;
  padding: 2px;
}
table.lens.name {
  font-weight: bold;
}
.content {

}
.date {
font-size: 10px;
font-style: italic;
}
.viewPanel {
  background: #fff;
}
    </style>
 </head> 
 <body>
    <h1>Semantic MicroBlogging - demo timeline</h1>
    <table>
      <tr valign="top">
        <td ex:role="viewPanel" id="viewPanel">
          <table ex:role="lens" class="lens">
            <tr>
              <td><img ex:src-content=".depiction" width="56px" /></td>
              <td>
                 <div ex:content=".name" class="name"></div>
                 <div ex:content=".content" class="content"></div>
                 <div ex:content=".date" class="date"></div>
              </td>
            </tr>
          </table>
          <div ex:role="view" 
	        ex:orders=".date"
                ex:directions="descending"></div>
          <div ex:role="view"
                ex:viewClass="Timeline"
                ex:start=".date"
                ex:colorKey=".name">
          </div>
          <div ex:role="view" 
                ex:viewClass="Map" 
                ex:latlng=".latlng">
          </div>
          </td>
          <td width="25%">
            <div ex:role="facet" ex:facetClass="TextSearch"></div>
            <div ex:role="facet" ex:expression=".day" ex:facetLabel="Date"></div>
            <div ex:role="facet" ex:expression=".name" ex:facetLabel="Name"></div>
            <div ex:role="facet" ex:expression=".topics" ex:facetLabel="Topic"></div>
            <div ex:role="facet" ex:expression=".locations" ex:facetLabel="Location"></div>
         </td>
      </tr>
   </table>
  </body>
 </html>
