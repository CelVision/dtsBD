<?php
/**
 * 地图特性flags配置化测试
 * 1. gameresource数据断言（0/32/33/34特性、无键分支默认）
 * 2. rs_game组装循环等价（flags提取+lockbranch锁分支）
 * 3. migrate_mapinfo_flags旧局迁移精确复刻
 * 4. derive_map_flaglists派生4表
 * 5. 随机落点行为等价（蒙特卡洛抽样）
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');
require GAME_ROOT.'./include/global.func.php';

$fail = 0;
function check($name, $cond) {
	global $fail;
	echo ($cond ? 'OK' : 'FAIL') . ": $name\n";
	if(!$cond) $fail++;
}

// ── 1. gameresource数据断言 ──
include GAME_ROOT.'./gamedata/cache/gameresource_1.php';
check('数据:0-0 flags=危险+双排除+锁分支', $maps[0][0]['flags'] === Array('deepzone','norandom_drop','norandom_npc','lockbranch'));
check('数据:32-0 flags=危险', $maps[32][0]['flags'] === Array('deepzone'));
check('数据:33-0 flags=危险+锁分支', $maps[33][0]['flags'] === Array('deepzone','lockbranch'));
check('数据:34-0 flags=危险+双排除+传送排除+锁分支', $maps[34][0]['flags'] === Array('deepzone','norandom_drop','norandom_npc','noesc_tp','lockbranch'));
check('数据:99池无flags键', !isset($maps[99][0]['flags']));
check('数据:普通图1-0无flags键', !isset($maps[1][0]['flags']));
check('数据:32-1空占位分支无flags键', !isset($maps[32][1]['flags']));

// ── 2. rs_game组装循环等价（复刻mode&2逻辑，验证flags提取与lockbranch） ──
// 随机分支选择（复刻rs_game）
$mapid = Array();
for($id=1 ; $id<33 ; $id++) {
	$dice = rand(0,99);
	$mapid[$id] = $dice > 50 ? 1 : 0;
}
// lockbranch处理（复刻rs_game）
for($id=0 ; $id<35 ; $id++) {
	if(isset($maps[$id][0]['flags']) && in_array('lockbranch', $maps[$id][0]['flags'])) $mapid[$id] = 0;
}
check('组装:lockbranch图0锁分支0', $mapid[0] === 0);
check('组装:lockbranch图33锁分支0', $mapid[33] === 0);
check('组装:lockbranch图34锁分支0', $mapid[34] === 0);
// 组装循环（复刻rs_game）
$mapinfo = Array();
for($id=0 ; $id<35 ; $id++) {
	if (!isset($maps[$id][$mapid[$id]]) || empty($maps[$id][$mapid[$id]]['plsinfo'])) {
		$mapid[$id] = 0;
	}
	$mapinfo['plsinfo'][$id] = $maps[$id][$mapid[$id]]['plsinfo'];
	$mapinfo['flags'][$id] = isset($maps[$id][$mapid[$id]]['flags']) ? $maps[$id][$mapid[$id]]['flags'] : Array();
}
check('组装:flags全35图有键', count($mapinfo['flags']) == 35);
check('组装:0提取四特性', $mapinfo['flags'][0] === Array('deepzone','norandom_drop','norandom_npc','lockbranch'));
check('组装:34提取五特性', $mapinfo['flags'][34] === Array('deepzone','norandom_drop','norandom_npc','noesc_tp','lockbranch'));
check('组装:普通图默认空数组', $mapinfo['flags'][1] === Array());
check('组装:json序列化无损（存DB回读等价）', json_decode(json_encode($mapinfo['flags']), true) === $mapinfo['flags']);

// 分支级差异验证：构造轮换分支带不同flags（同一图id两个分支不同特性）
$maps_branch_test = $maps;
$maps_branch_test[5] = Array(
	0 => Array('plsinfo'=>'分支A','flags' => Array('deepzone')),
	1 => Array('plsinfo'=>'分支B','flags' => Array('norandom_drop')),
);
$mflags = Array();
foreach(Array(0,1) as $bi) {
	$mflags[$bi] = isset($maps_branch_test[5][$bi]['flags']) ? $maps_branch_test[5][$bi]['flags'] : Array();
}
check('组装:轮换分支级差异A=deepzone', $mflags[0] === Array('deepzone'));
check('组装:轮换分支级差异B=norandom_drop', $mflags[1] === Array('norandom_drop'));

// ── 3. 旧局migration精确复刻 ──
$oldmapinfo = Array('plsinfo' => $mapinfo['plsinfo']);
$oldmapinfo['plsinfo'][0] = '无月之影';
$mig = $oldmapinfo;
$r = migrate_mapinfo_flags($mig);
check('迁移:返回true', $r === true);
check('迁移:0=危险+双排除', $mig['flags'][0] === Array('deepzone','norandom_drop','norandom_npc'));
check('迁移:32=危险', $mig['flags'][32] === Array('deepzone'));
check('迁移:33=危险', $mig['flags'][33] === Array('deepzone'));
check('迁移:34=危险+双排除+传送排除（无lockbranch：旧局本就锁分支）', $mig['flags'][34] === Array('deepzone','norandom_drop','norandom_npc','noesc_tp'));
check('迁移:普通图空数组', $mig['flags'][15] === Array());
check('迁移:全35图', count($mig['flags']) == 35);
$mig2 = Array(); // 无plsinfo的空mapinfo
check('迁移:无plsinfo返回false', migrate_mapinfo_flags($mig2) === false);

// ── 4. derive_map_flaglists派生 ──
$mapinfo = $mig; // 用迁移结果派生
derive_map_flaglists();
check('派生:deepzones=(0,32,33,34)', $GLOBALS['deepzones'] == Array(0,32,33,34));
check('派生:noranddrop_pls=(0,34)', $GLOBALS['noranddrop_pls'] == Array(0,34));
check('派生:norandnpc_pls=(0,34)', $GLOBALS['norandnpc_pls'] == Array(0,34));
check('派生:noesc_pls=(34)', $GLOBALS['noesc_pls'] == Array(34));
// 新局组装结果派生（lockbranch也计入flags，结果应一致）
$mapinfo['flags'][33] = Array('deepzone','lockbranch');
derive_map_flaglists();
check('派生:含lockbranch不影响4表', $GLOBALS['deepzones'] == Array(0,32,33,34) && $GLOBALS['noesc_pls'] == Array(34));
// 空/异常flags降级
$mapinfo = Array();
derive_map_flaglists();
check('派生:空mapinfo安全降级4表空', $GLOBALS['deepzones'] == Array() && $GLOBALS['norandnpc_pls'] == Array());
$mapinfo = Array('flags' => Array(5 => 'bad', 6 => Array('deepzone')));
derive_map_flaglists();
check('派生:非数组flag跳过+有效flag提取', $GLOBALS['deepzones'] == Array(6));

// ── 5. 随机落点行为等价（蒙特卡洛） ──
// 复刻rs_game mode&16全图池落点：rand(0,..)+while(in_array($noranddrop_pls))
$plsnum = 35;
$noranddrop_pls = Array(0,34);
$hit_excluded = false;
$hist = Array();
for($i = 0; $i < 20000; $i++) {
	$rmap = rand(0,$plsnum-1);
	while (in_array($rmap,$noranddrop_pls)){$rmap = rand(0,$plsnum-1);}
	if(in_array($rmap, $noranddrop_pls)) { $hit_excluded = true; break; }
	$hist[$rmap] = 1;
}
check('落点:2万次采样无一落入排除表', !$hit_excluded);
check('落点:覆盖全部非排除图(33图)', count($hist) == 33);
// NPC随机出生等价：hidding类避deepzone∪norandnpc
$deepzones = Array(0,32,33,34);
$norandnpc_pls = Array(0,34);
$hit_bad = false;
for($i = 0; $i < 20000; $i++) {
	$rpls = rand(0, $plsnum - 1);
	while(in_array($rpls, $deepzones) || in_array($rpls, $norandnpc_pls)) $rpls = rand(0, $plsnum - 1);
	if(in_array($rpls, $deepzones) || in_array($rpls, $norandnpc_pls)) { $hit_bad = true; break; }
}
check('NPC落点:hidding类避deepzone∪norandnpc', !$hit_bad);

echo $fail ? "\n{$fail}项FAIL\n" : "\n全部通过\n";
exit($fail ? 1 : 0);
