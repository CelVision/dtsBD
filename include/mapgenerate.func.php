<?php


//plsinfo还是暂时先用着吧
function get_plsinfo()
{
    global $mapinfo, $mapinfo[0];
    $mapinfo[0] = Array();
    foreach( $mapinfo as $mkey => $mlist )
    {
       $mapinfo[0][$key] = $mlist[$mkey]['plsinfo'];
       unset($mlist);
    }
    return $mapinfo[0];
}

?>