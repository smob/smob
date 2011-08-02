/**
 * If you use this script, please give credit to DBpedia Spotlight by: 
 * - adding "powered by DBpedia Spotlight" somewhere in the page. 
 * - add a link to http://spotlight.dbpedia.org. 
 *
 * TODO people can also want to change the boundaries of the spotting
 * TODO showScores = [ list of score names ]
 *
 * @author pablomendes
 *
 */

(function( $ ){

   var powered_by = "<div style='font-size: 9px; float: right'><a href='http://spotlight.dbpedia.org'>Powered by DBpedia Spotlight</a></div>";

   var settings = {      
      'endpoint' : 'http://spotlight.dbpedia.org/dev/rest',
      'confidence' : 0.4,
      'support' : 20,
      'powered_by': 'yes'
    };

   var methods = {
      init : function( options ) { 	      
        // If options exist, lets merge them with our default settings
	if ( options ) { 
	    $.extend( settings, options );
	}  
      },
      annotate: function( options ) {
	    function update(response) { 
	   		var content = $(response).find("div");  //the div with the annotated text
	   		if (settings.powered_by == 'yes') { 
	   			$(content).append($(powered_by)); 
	   		};     	
	   		//var entities = $(content).find("a/[about]");   	
	   		$(this).html(content.html());      	
	   	    }    
	   
	   	    return this.each(function() {            
	   	      var params = {'text': $.quoteString($(this).text()), 'confidence': settings.confidence, 'support': settings.support };      
	   	      $.ajax({ 'url': settings.endpoint+"/annotate", 
	   		       'data': params,
	   		       'context': this,
	   		       'headers': {'Accept': 'application/xhtml+xml'},
	   		       'success': update
	   		     });	
	             });
       },
       candidates: function( options ) {
          function getSelectBox(resources) {
             var snippet =  "<select class='candidates'>";
             //console.log(resources);
             var options = ""; $.each(resources, function(i, r) { 
             	options += "<option value='" + r["@uri"] + "'>" + r["@label"];
             	//TODO settings.showScores = ["finalScore"] foreach showscores, add k=v
             	if (settings.showScores == 'yes') options += " (" + parseFloat(r["@finalScore"]).toPrecision(3) +")";
             	options += "</option>"; 
             });
             snippet += options;
             snippet += "</select>"
             return snippet;
          }

          
          function parseCandidates(json) {
             var text = json.annotation["@text"];
             var start = 0;
             var annotatedText = json.annotation.surfaceForm.map(function(e) {
                var name = e["@name"];
                var offset = parseInt(e["@offset"]);
                var sfLength = parseInt(name.length);
             	var snippet = text.substring(start, offset)
             	var surfaceForm = text.substring(offset,offset+sfLength);
             	start = offset+sfLength;
             	snippet += "<div id='"+(name+offset)+"' class='annotation'>";
             	//TODO instead of showing directly the select box, it would be cuter to just show a span, and onClick on that span, build the select box.
             	snippet += getSelectBox($(e.resource));
             	snippet += "</div>";
             	return snippet;
             }).join("");
             //snippet after last surface form
             annotatedText += text.substring(start, text.length);
             //console.log(annotatedText);
             return annotatedText;
	  }

       	    function update(response) { 
       	        //console.log(response);
       		var content = parseCandidates(response);
       		if (settings.powered_by == 'yes') { 
       			$(content).append($(powered_by)); 
       		};     	       		
       		$(this).html(content);      	
       	    }    
       
       	    return this.each(function() {            
       	      var params = {'text': $(this).val(), 'confidence': settings.confidence, 'support': settings.support };      
       	      $.ajax({ 'url': settings.endpoint+"/candidates", 
       		       'data': params,
       		       'context': this,
       		       'headers': {'Accept': 'application/json'},
       		       'success': update
       		     });	
       	    });
       }

  }; 
  
  $.fn.runDBpediaSpotlight = function(method) {    
      // Method calling logic
      if ( methods[method] ) {
        return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
      } else if ( typeof method === 'object' || ! method ) {
        return methods.init.apply( this, arguments );
      } else {
        $.error( 'Method ' +  method + ' does not exist on jQuery.spotlight' );
      } 
  };
  
})( jQuery );
