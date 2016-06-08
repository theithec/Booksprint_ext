(function () {
	mw.booksprint_ext = {
        show_result: function(result, msg){
            $("#ajax-loader").remove();
            msg = msg || result;
            $("#result").html('<p class="' + result + '">' + msg + "</p>");
            $(".booklink").show();
            
        },
		watch_status:  function(key){
			var self = this;
			//console.log("watch");
			mw.loader.using( 'mediawiki.api', function () {
				( new mw.Api() ).post( {
					action: 'bmaker',
					cmd:  'status',
					args: key,
					token: mw.user.tokens.get( 'editToken' )
				} )
				.done( function ( data ) {
					try{
						var json = JSON.parse(data.Result);
					} catch(err){
                        console.error(data);
                        self.show_result("error", err);
                        return;
					}
					var res = json.result;

					if (res == "PENDING" ){
						setTimeout(function(){self.watch_status(key);}, 5000);
                    }else {
                        var classname  = res == "SUCCESS" || res =="PENDING" ? res.toLowerCase():  "error" ;
                        self.show_result(classname);
                    
                    }
				})
				.fail( function ( error ) {
                    self.show_result("error", error);
					console.log( 'API failed :(', error );
                } );
            } );
		},

		wait_for_result: function(){
			var self = this;
			var key = $("#result").data("key");
			if (key == undefined){
				setTimeout(self.wait_for_result, 5000);
			} else if (key=="SUCCESS"){
				$("#result").append('<p class="success">SUCCESS</p>');
			}else {
				self.watch_status(key);
			}
		}
	};
	$(document).ready(function(){
        $("#result").append(
                '<img id="ajax-loader" src="' + mw.config.get( 'wgExtensionAssetsPath' ) +
                '/Booksprint_ext/modules/images/ajax-loader.gif" >');
        $(".booklink").hide();
		mw.booksprint_ext.wait_for_result();
	});


}() );
