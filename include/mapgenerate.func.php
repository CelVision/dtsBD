<?php


//plsinfo还是暂时先用着吧
function get_plsinfo()
{
    global $mapinfo, $plsinfo;
    $plsinfo = Array();
    foreach( $mapinfo as $mkey => $mlist )
    {
       $plsinfo[$key] = $mlist[$mkey]['plsinfo'];
       unset($mlist);
    }
    return $plsinfo;
}

?>