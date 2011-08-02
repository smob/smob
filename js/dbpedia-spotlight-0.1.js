/**
 * If you use this script, please give credit to DBpedia Spotlight by: 
 * - adding "powered by DBpedia Spotlight" somewhere in the page. 
 * - add a link to http://spotlight.dbpedia.org. 
 *
 * @author pablomendes
 *
 */

(function( $ ){

  $.fn.annotate = function( options ) {  

    var powered_by = "<div style='font-size: 9px; float: right'><a href='http://spotlight.dbpedia.org'>Powered by DBpedia Spotlight</a></div>";
    
    var settings = {      
      'endpoint' : 'http://spotlight.dbpedia.org/rest/annotate',
      'confidence' : 0.4,
      'support' : 20,
      'powered_by': 'yes'
    };
    
    function update(response) { 
    	var content = $(response).find("div");  //the div with the annotated text

    	if (settings.powered_by == 'yes') { 
    		$(content).append($(powered_by)); 
    	}; 
    	
    	var entities = $(content).find("a/[about]")    	
    	
    	$(this).replaceWith(content);  
    	
    }
    
    
    return this.each(function() {        
    
      // If options exist, lets merge them with our default settings
      if ( options ) { 
        $.extend( settings, options );
      }
  
      var params = {'text': this.innerHTML, 'confidence': settings.confidence, 'support': settings.support };
      
      $.ajax({ 'url': settings.endpoint, 
      	       'data': params,
      	       'context': this,
      	       'headers': {'Accept': 'application/xhtml+xml'},
      	       'success': update
      	     });
	
    });

  };
})( jQuery );
