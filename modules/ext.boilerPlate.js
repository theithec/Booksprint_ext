show_status = function(response){
    alert("RESP " + response);
}
//alert("sh " + show_status);
( function () {
    $("#publishbookbutton").click(function(e){
        e.preventDefault();
        alert("x");

    });
    /**
     * @class mw.boilerPlate
     * @singleton
     */
    mw.boilerPlate = {
    };
    
    wait_for_result = function(){
        console.log("wait");
    }

    $(document).ready(function(){
        wait_for_result();
    });




}() );
