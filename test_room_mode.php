<?php
/**
 * 房间游戏模式（房间绑定配置号）测试
 * 1. $roommodes 注册表数据断言（system.php）
 * 2. roommng_create_new_room：模式号写进INSERT（gamecfg列）；未注册模式回退1；默认1；已在房间不建
 * 3. roommng_verify_db_game_structure：gamecfg列自动升级ALTER
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');
error_reporting(E_ALL ^ E_NOTICE);
require GAME_ROOT.'./include/global.func.php';

$fail = 0;
function check($name, $cond) {
        global $fail;
        echo ($cond ? 'OK' : 'FAIL') . ": $name\n";
        if(!$cond) $fail++;
}

// ── stub db：记录query，按SQL片段映射num_rows/fetch_array ──
class room_test_db {
        public $log = Array();
        public $map = Array();
        function query($q) { $this->log[] = $q; return $q; }
        function num_rows($res) {
                foreach($this->map as $frag => $r) if(isset($r['num_rows']) && strpos($res, $frag) !== false) return $r['num_rows'];
                return 0;
        }
        function fetch_array($res) {
                foreach($this->map as $frag => $r) if(isset($r['fetch']) && strpos($res, $frag) !== false) return $r['fetch'];
                return Array();
        }
}

// ── 1. $roommodes注册表 ──
include GAME_ROOT.'./gamedata/system.php';
check('注册表:1=常规模式', isset($roommodes[1]) && $roommodes[1] == '常规模式');
check('注册表:2=快速模式', isset($roommodes[2]) && $roommodes[2] == '快速模式');

// ── 公共环境 ──
include GAME_ROOT.'./include/roommng.func.php';

function setup_room_env() {
        global $db, $gtablepre, $now, $startmin, $max_rooms, $ip_max_rooms, $rerror, $roommodes;
        $db = new room_test_db();
        $gtablepre = 'test_';
        $now = 1000000;
        $startmin = 1; $max_rooms = 10; $ip_max_rooms = 2; $rerror = '';
        // IP限制通过、无已建房间(→新房间号1)、gamenum查询、join时查到刚插入的行(groomnums=0)
        $db->map = Array(
                "roomid>0 AND ip" => Array('num_rows' => 0),
                "SELECT groomid FROM" => Array('num_rows' => 0),
                "max(gamenum)" => Array('num_rows' => 1, 'fetch' => Array('max_value' => 5)),
                "groomid='1'" => Array('num_rows' => 1, 'fetch' => Array('groomnums' => 0)),
        );
}

function find_insert_with_gamecfg($db) {
        foreach($db->log as $q) {
                if(strpos($q, 'INSERT INTO') !== false && strpos($q, 'gamecfg') !== false) return $q;
        }
        return NULL;
}

// ── 2. 建房：mode=2 写入 gamecfg ──
setup_room_env();
$udata = Array('roomid' => 0, 'ip' => '1.2.3.4', 'username' => 'tester');
roommng_create_new_room($udata, 2);
$ins = find_insert_with_gamecfg($db);
check('建房mode=2:INSERT带gamecfg列', $ins !== NULL);
check('建房mode=2:gamecfg值为2', $ins !== NULL && strpos($ins, ",'2')") !== false);
check('建房mode=2:加入房间(groomnums更新1)', (bool)array_filter($db->log, function($q){ return strpos($q, "groomnums='1'") !== false; }));
check('建房mode=2:无rerror', empty($rerror));

// ── 2b. 未注册模式号(99)回退常规(1) ──
setup_room_env();
$udata = Array('roomid' => 0, 'ip' => '1.2.3.4', 'username' => 'tester');
roommng_create_new_room($udata, 99);
$ins = find_insert_with_gamecfg($db);
check('建房mode=99:回退gamecfg=1', $ins !== NULL && strpos($ins, ",'1')") !== false);

// ── 2c. 未传模式号默认1 ──
setup_room_env();
$udata = Array('roomid' => 0, 'ip' => '1.2.3.4', 'username' => 'tester');
roommng_create_new_room($udata);
$ins = find_insert_with_gamecfg($db);
check('建房未传mode:默认gamecfg=1', $ins !== NULL && strpos($ins, ",'1')") !== false);

// ── 2d. 已在房间：报错且不INSERT ──
setup_room_env();
$udata = Array('roomid' => 3, 'ip' => '1.2.3.4', 'username' => 'tester');
roommng_create_new_room($udata, 2);
check('建房已在房间:rerror=alreay_in_room', $rerror == 'alreay_in_room');
check('建房已在房间:无INSERT', find_insert_with_gamecfg($db) === NULL);

// ── 3. verify_db_game_structure：gamecfg列自动升级 ──
setup_room_env();
ob_start();
roommng_verify_db_game_structure();
$verify_out = ob_get_clean();
$alter_found = false;
foreach($db->log as $q) {
        if(strpos($q, 'ADD gamecfg tinyint(3) unsigned NOT NULL DEFAULT \'1\'') !== false) { $alter_found = true; break; }
}
check('升级:缺gamecfg列时自动ALTER', $alter_found);
check('升级:提示信息含gamecfg', strpos($verify_out, 'gamecfg') !== false);

// ── 4. roommng_resolve_gamecfg：房间配置探测 ──
// 4a. 未登录
setup_room_env();
check('探测:未登录返0', roommng_resolve_gamecfg('') === 0);
// 4b. 登录但未进房（roomid=0）
setup_room_env();
$db->map = Array(
        "SELECT roomid FROM" => Array('num_rows' => 1, 'fetch' => Array('roomid' => 0)),
);
check('探测:未进房返0', roommng_resolve_gamecfg('tester') === 0);
// 4c. 用户不存在
setup_room_env();
$db->map = Array(
        "SELECT roomid FROM" => Array('num_rows' => 0),
);
check('探测:用户不存在返0', roommng_resolve_gamecfg('ghost') === 0);
// 4d. 进2号模式房间
setup_room_env();
$db->map = Array(
        "SELECT roomid FROM" => Array('num_rows' => 1, 'fetch' => Array('roomid' => 3)),
        "SELECT gamecfg FROM" => Array('num_rows' => 1, 'fetch' => Array('gamecfg' => 2)),
);
check('探测:进快速房间返2', roommng_resolve_gamecfg('tester') === 2);
// 4e. 进1号模式房间
setup_room_env();
$db->map = Array(
        "SELECT roomid FROM" => Array('num_rows' => 1, 'fetch' => Array('roomid' => 3)),
        "SELECT gamecfg FROM" => Array('num_rows' => 1, 'fetch' => Array('gamecfg' => 1)),
);
check('探测:进常规房间返1', roommng_resolve_gamecfg('tester') === 1);
// 4f. roomid指向已删除的房间（game表无行）
setup_room_env();
$db->map = Array(
        "SELECT roomid FROM" => Array('num_rows' => 1, 'fetch' => Array('roomid' => 3)),
        "SELECT gamecfg FROM" => Array('num_rows' => 0),
);
check('探测:房间不存在返0', roommng_resolve_gamecfg('tester') === 0);
// 4g. 房间存了未注册的配置号（99）→回退1
setup_room_env();
$db->map = Array(
        "SELECT roomid FROM" => Array('num_rows' => 1, 'fetch' => Array('roomid' => 3)),
        "SELECT gamecfg FROM" => Array('num_rows' => 1, 'fetch' => Array('gamecfg' => 99)),
);
check('探测:未注册配置号回退1', roommng_resolve_gamecfg('tester') === 1);

// ── 5. common.inc.php加载顺序：探测必须先于config()加载与groomid判定 ──
$common_src = file_get_contents(GAME_ROOT.'./include/common.inc.php');
$p_probe = strpos($common_src, 'roommng_resolve_gamecfg($cuser)');
$p_res = strpos($common_src, "require config('resources'");
$p_groomid = strpos($common_src, '$groomid = isset($udata');
check('顺序:探测在resources加载前', $p_probe !== false && $p_res !== false && $p_probe < $p_res);
check('顺序:探测在groomid判定前', $p_probe !== false && $p_groomid !== false && $p_probe < $p_groomid);

// ── 6. gameresource_2基线：与1号内容一致（仅头部标记不同） ──
$f1 = file_get_contents(GAME_ROOT.'./gamedata/cache/gameresource_1.php');
$f2 = file_get_contents(GAME_ROOT.'./gamedata/cache/gameresource_2.php');
$core1 = substr($f1, strpos($f1, '// gameresource'));
$core2 = substr($f2, strpos($f2, '// gameresource'));
check('基线:2号为有效数据文件', strpos($f2, '<?php') === 0 && strpos($f2, '$maps = ') !== false);
check('基线:2号头部含快速模式标记', strpos($f2, '快速模式配置') !== false);
check('基线:config(2)解析到2号文件', strpos(config('gameresource', 2), 'gameresource_2.php') !== false);
check('基线:config(3)缺号回退1号', strpos(config('gameresource', 3), 'gameresource_1.php') !== false);

// ── 7. gamecfg_2基线：快速模式派生配置（10分钟一禁）──
$g1 = file_get_contents(GAME_ROOT.'./gamedata/cache/gamecfg_1.php');
$g2 = file_get_contents(GAME_ROOT.'./gamedata/cache/gamecfg_2.php');
check('gamecfg:2号为有效配置文件', strpos($g2, '<?php') === 0 && strpos($g2, '$areahour') !== false);
check('gamecfg:2号areahour=10分钟一禁', strpos($g2, '$areahour = 10;') !== false);
check('gamecfg:1号areahour=20不变', strpos($g1, '$areahour = 20;') !== false);
check('gamecfg:2号头部含派生标记', strpos($g2, '快速模式房间派生配置') !== false);
// 归一化（剥头部+areahour换回20）后应与1号逐字节一致——落实"1号改动必须重派生2号"义务
$norm2 = substr($g2, strpos($g2, '//禁区间隔时间'));
$norm2 = str_replace('$areahour = 10;', '$areahour = 20;', $norm2);
$norm1 = substr($g1, strpos($g1, '//禁区间隔时间'));
check('gamecfg:2号归一化后与1号逐字节一致(同步义务)', $norm2 === $norm1);
check('gamecfg:config(2)解析到2号文件', strpos(config('gamecfg', 2), 'gamecfg_2.php') !== false);
check('gamecfg:config(3)缺号回退1号', strpos(config('gamecfg', 3), 'gamecfg_1.php') !== false);

// ── 8. valid.php入场经验：快速模式起点=普通模式2禁进入 ──
$vsrc = file_get_contents(GAME_ROOT.'./valid.php');
check('入场:公式含gamecfg==2加2禁分支', strpos($vsrc, '($areanum + ($gamecfg == 2 ? 2 : 0)) * 20') !== false);
// 复算语义：普通模式2禁进入 exp=2*20=40；快速模式开局进入即得40；普通模式开局仍0
$t_areanum = 2; $t_gamecfg = 1;
check('入场:普通2禁进入=40(基线语义)', $t_areanum * 20 == 40);
$t_areanum = 0; $t_gamecfg = 2;
check('入场:快速开局进入=40(=普通2禁)', ($t_areanum + ($t_gamecfg == 2 ? 2 : 0)) * 20 == 40);
$t_areanum = 0; $t_gamecfg = 1;
check('入场:普通开局进入=0(不变)', ($t_areanum + ($t_gamecfg == 2 ? 2 : 0)) * 20 == 0);
$t_areanum = 1; $t_gamecfg = 2;
check('入场:快速1禁后进=60', ($t_areanum + ($t_gamecfg == 2 ? 2 : 0)) * 20 == 60);

// ── 6b. gameresource_2固有差异：init池typeId14三女主num=0（断破灭之诗链） ──
$r1 = file_get_contents(GAME_ROOT.'./gamedata/cache/gameresource_1.php');
$r2 = file_get_contents(GAME_ROOT.'./gamedata/cache/gameresource_2.php');
// init14块上下文提取比对（init池内 14 => array(num, pls 99) 紧跟 15 =>）
function extract_init_block($src, $tid) {
        if(!preg_match('/\$npc_spawn_config\s*=\s*array.*?\'init\'\s*=>.*?' . $tid . '\s*=>\s*array\s*\(\s*\'num\'\s*=>\s*(\d+),\s*\'pls\'\s*=>\s*(\d+),/s', $src, $m)) return NULL;
        return Array('num' => $m[1], 'pls' => $m[2]);
}
$init14_1 = extract_init_block($r1, 14);
$init14_2 = extract_init_block($r2, 14);
check('断链:1号init14仍刷3个(num=3)', $init14_1 !== NULL && $init14_1['num'] == 3);
check('断链:2号init14不刷(num=0)', $init14_2 !== NULL && $init14_2['num'] == 0);
check('断链:2号头部记固有差异(防重派生丢失)', strpos($r2, 'typeId14') !== false && strpos($r2, 'num=0') !== false);
// end7幻境解离：G.A.M.E.O.V.E.R使用按gamecfg==2拒绝（链与三女主无关需单独断）
$itemsrc = file_get_contents(GAME_ROOT.'./include/game/item.func.php');
$p_gov = strpos($itemsrc, 'elseif ($itm == \'『G.A.M.E.O.V.E.R』\')');
$p_g2 = strpos($itemsrc, 'if($gamecfg == 2) {', $p_gov);
$p_end7 = strpos($itemsrc, "'end7'", $p_gov);
check('断链:end7拒绝分支在gamecfg==2内', $p_gov !== false && $p_g2 !== false && $p_end7 !== false && $p_g2 < $p_end7);

// ── 6c. 快速模式平摊：99池全图随机NPC/物品总量保持，落点直接随机本局图池 ──
include GAME_ROOT.'./include/game/npcdict.func.php';
// 模拟局：本局图集{0,5}，0号norandom_npc；模拟池(全量35图随机产物)全为废图33→摘出路径必丢弃
$mapinfo = Array('plsinfo' => Array(0 => '无月之影', 5 => '指挥中心'));
$norandnpc_pls = Array(0);
$deepzones = Array();
$gamevars = Array('sim_full_pls' => Array('npc' => Array(33), 'npcdeep' => Array(33), 'drop' => Array(33)));
$gamecfg = 1;
$r1 = sim_npc_pls(false);
check('平摊:普通模式摘出语义不变(模拟池全废图→NULL丢弃)', $r1 === NULL);
$gamecfg = 2;
$all_local = true; $all_never0 = true;
for($i = 0; $i < 200; $i++) {
        $r = sim_npc_pls(false);
        if($r !== 5) { if($r !== 0) $all_local = false; else $all_never0 = false; }
}
check('平摊:快速模式不丢弃(总量保持,永非NULL)', $all_local);
check('平摊:快速模式尊重norandom_npc(0号不落小兵)', $all_never0);
$rd = sim_npc_pls(true);
check('平摊:快速模式躲避类分支同样平摊', $rd === 5 || $rd === 0);
// rs_game 99池物品落图平摊分支（源码断言：gamecfg==2时sim_pool直接取本局差集）
$sys_src = file_get_contents(GAME_ROOT.'./include/system.func.php');
$p_c2 = strpos($sys_src, 'if($gamecfg == 2) {');
$p_flat = strpos($sys_src, "array_diff(array_keys(\$mapinfo['plsinfo']), \$noranddrop_pls);", $p_c2);
$p_else = strpos($sys_src, 'sim_full_pls', $p_c2);
check('平摊:rs_game 99池物品快速分支在摘出分支前', $p_c2 !== false && $p_flat !== false && $p_else !== false && $p_flat < $p_else);

// ── 结果 ──
echo $fail ? "\n*** {$fail} FAILURES ***\n" : "\nALL PASSED\n";
exit($fail ? 1 : 0);
?>
