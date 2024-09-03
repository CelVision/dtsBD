<?php


//plsinfo还是暂时先用着吧
function get_plsinfo($mapinfo)
{
    file_put_contents( GAME_ROOT.'./gamedata/cache/maps_1.php','<?php'.PHP_EOL.'$plsinfo = Array('.PHP_EOL,);
    foreach( $mapinfo as $mkey => $mlist )
   {
    file_put_contents( GAME_ROOT.'./gamedata/cache/maps_1.php',var_export($mkey,1)."=>'",FILE_APPEND);
    file_put_contents( GAME_ROOT.'./gamedata/cache/maps_1.php',var_export($mlist[$mkey]['plsinfo'],1)."'",FILE_APPEND);
    file_put_contents( GAME_ROOT.'./gamedata/cache/maps_1.php',','.PHP_EOL,FILE_APPEND);
    unset($mlist);
   }
   file_put_contents( GAME_ROOT.'./gamedata/cache/maps_1.php',');'.PHP_EOL.'?>',FILE_APPEND);
}



?>

