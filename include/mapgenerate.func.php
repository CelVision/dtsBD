<?php


//plsinfo还是暂时先用着吧
function get_plsinfo()
{
    global $mapinfo, $mapinfo['plsinfo'];
    $mapinfo['plsinfo'] = Array();
    foreach( $mapinfo as $mkey => $mlist )
    {
       $mapinfo['plsinfo'][$key] = $mlist[$mkey]['plsinfo'];
       unset($mlist);
    }
    return $mapinfo['plsinfo'];
}

?>