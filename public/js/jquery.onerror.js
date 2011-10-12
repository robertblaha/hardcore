(function( $ ){
  $.fn.jsErrorHandler = function(options) {

	var settings = {
		from: "chyby@rbc.cz",
		website: 'mfp.cz'
	}
	if (options) $.extend(settings, options);


    window.onerror = function (msg, url, line) {

		$.ajax({
			type:"GET",
			cache:false,
			url:"/admin/tools/jserrorhandler",
			data: $.param({'message':msg, 'url': url, userAgent: navigator.userAgent, 'line': line, 'from':settings.from, 'website': settings.website, 'debug': dojo.byId('error-debug').value}),
			success: function(test){
				if(window.console) console.log("Report sent about the javascript error")
			}
		})
	    return true;
	}


  };
})( jQuery );



