<?php
function debuglog($msg){
    if (array_key_exists("debug", $_GET) && $_GET['debug'] == true){
        if (is_array($msg)){
            $msg = implode(", ", $msg);
        }
        wfDebug("DEBUGLOG: $msg");
    }
}

