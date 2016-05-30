(function () {
  mw.booksprint_ext = {
    watch_status:  function(key){
      var self = this;
      console.log("watch");
      mw.loader.using( 'mediawiki.api', function () {
        ( new mw.Api() ).post( {
          action: 'bmaker',
          cmd:  'status',
          args: key,
          token: mw.user.tokens.get( 'editToken' )
        } )
        .done( function ( data ) {
          var json = JSON.parse(data.Result);
          var res = json.result;
          var classname  = res == "SUCCESS" || res =="PENDING" ? res.toLowerCase():  "error" ;
          $("#result").append('<p class="' + classname + '">' + res + "</p>");

          if (res == "PENDING" )
            setTimeout(function(){self.watch_status(key);}, 5000);
        })
        .fail( function ( error ) {
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
    mw.booksprint_ext.wait_for_result();
  });


}() );
