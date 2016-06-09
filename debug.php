<?php
function debuglog($msg){
    if (array_key_exists("debug", $_GET) && $_GET['debug'] == true){
            $msg = var_export($msg, $return=true); 
            wfDebug("DEBUGLOG: $msg");
    }
}

