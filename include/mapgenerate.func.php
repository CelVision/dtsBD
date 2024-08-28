<?php


//生成出一个0和1组成的array，每个位置对应一个地图
function generate_random_map_list()
{
    $mapid = Array();
    //无月，雏菊，英灵不动他
    $mapid[0] = 0;
    $mapid[33] = 0;
    $mapid[34] = 0;
    for($id=1 ; $id<33 ; $id++)
    {
        $dice = rand(0,1);
        $map[$id] = $dice;
    }
    //tada!地图序号表
    return $mapid;
}

//从resource表里面摘出来生成的地图代码放进表里头
function create_map_list()
{
    global $db,$gtablepre,$mapinfo,$groomid;
    include_once GAME_ROOT."./gamedata/cache/mapresource_1.php";
    $mapinfo = Array();
    $mapconfig = generate_random_map_list();
    for($id=0 ; $id<35 ; $id++)
    {
        if (isset($maps[$id][$mapconfig[$id]]))
        {
            $mapinfo[$id] = $maps[$id][$mapconfig[$id]];
        }else{//双重保险措施啊啊啊啊啊啊啊啊32个地图我还没有构思好
            $mapinfo[$id] = $maps[$id][$mapconfig[0]];
        }
    }
    save_gameinfo();
}

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