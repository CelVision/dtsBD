<?php
/**
 * 地图分支npc字段结构化（typeId=>num 图级固定刷新）测试：
 * 下沉类：红暮(type1)→图0、英灵殿系(20/21/22/24/26)→图34、SCP(88)→图32
 * 含：双形态数据断言 / get_map_npc_plan（轮换分支差异、扁平不入计划、多图覆盖）/
 *     地图名寻靶 resolve_npc_target_pls（破灭之诗召唤篝免写死坐标）/ maphelp展示同源
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');
require GAME_ROOT.'./include/global.func.php';
$gamecfg = 1;
include config('gameresource', $gamecfg);
include GAME_ROOT.'./include/game/npcdict.func.php';
include GAME_ROOT.'./include/game/maphelp.func.php';

$fail = 0;
function check($name, $cond) {
	global $fail;
	echo ($cond ? 'OK' : 'FAIL') . ": $name\n";
	if(!$cond) $fail++;
}

// ── 1. 数据断言（双形态）──
check('数据:图0 npc结构化(1=>1)', $maps[0][0]['npc'] === Array(1 => 1));
check('数据:图34 npc结构化(20=>10,21=>5,22=>2,24=>3,26=>1)', $maps[34][0]['npc'] === Array(20 => 10, 21 => 5, 22 => 2, 24 => 3, 26 => 1));
check('数据:图32-0 npc结构化(88=>4)', $maps[32][0]['npc'] === Array(88 => 4));
check('数据:图32-1(Level 0分支)npc空', $maps[32][1]['npc'] === Array());
check('数据:全局init已无1/20/21/22/24/26/88', !isset($GLOBALS['npc_spawn_config']['init'][1]) && !isset($GLOBALS['npc_spawn_config']['init'][20]) && !isset($GLOBALS['npc_spawn_config']['init'][21]) && !isset($GLOBALS['npc_spawn_config']['init'][22]) && !isset($GLOBALS['npc_spawn_config']['init'][24]) && !isset($GLOBALS['npc_spawn_config']['init'][26]) && !isset($GLOBALS['npc_spawn_config']['init'][88]));
check('数据:99池npc扁平(14,90,91)', $maps[99][0]['npc'] === Array(14, 90, 91));

// ── 2. get_map_npc_plan 展开计划 ──
$plan = get_map_npc_plan();
$exp = Array(
	1  => Array('num' => 1,  'pls' => 0,  'exclude' => Array()),
	20 => Array('num' => 10, 'pls' => 34, 'exclude' => Array()),
	21 => Array('num' => 5,  'pls' => 34, 'exclude' => Array()),
	22 => Array('num' => 2,  'pls' => 34, 'exclude' => Array()),
	24 => Array('num' => 3,  'pls' => 34, 'exclude' => Array()),
	26 => Array('num' => 1,  'pls' => 34, 'exclude' => Array()),
	88 => Array('num' => 4,  'pls' => 32, 'exclude' => Array()),
);
check('计划:7类图级固定刷新(等价旧init条目)', (function() use ($plan, $exp) { $a = $plan; $b = $exp; ksort($a); ksort($b); return $a === $b; })());
check('计划:扁平引用图不入计划(种火图/99池)', count($plan) === 7);

// 轮换分支差异：32轮换到分支1(Level 0, npc空) → SCP不刷，其余不变
$mapid = Array(32 => 1);
$plan_b1 = get_map_npc_plan();
unset($exp[88]);
check('计划:32轮换到Level 0分支→SCP不刷(分支级差异)', $plan_b1 === $exp);
unset($mapid); // $mapid未设兜底分支0 → 7类
$plan_b0 = get_map_npc_plan();
$exp0 = $exp + Array(88 => Array('num' => 4, 'pls' => 32, 'exclude' => Array()));
check('计划:$mapid未设兜底分支0(7类)', (function() use ($plan_b0, $exp0) { $a = $plan_b0; $b = $exp0; ksort($a); ksort($b); return $a === $b; })());

// 同type配多图：后者覆盖
$savemaps = $GLOBALS['maps'];
$GLOBALS['maps'][33][0]['npc'] = Array(88 => 2);
$plan_ov = get_map_npc_plan();
$GLOBALS['maps'] = $savemaps;
check('计划:同type多图后者覆盖(33>32)', $plan_ov[88] === Array('num' => 2, 'pls' => 33, 'exclude' => Array()));

// ── 3. 地图名寻靶（破灭之诗召唤篝：add[4] pls='name:雏菊之丘'）──
check('配置:add[4]为名字寻靶语法', get_npc_spawn_config(4, 'add') === Array('num' => 1, 'pls' => 'name:雏菊之丘'));
check('寻靶:雏菊之丘→图33', resolve_npc_target_pls('name:雏菊之丘') === 33);
check('寻靶:英灵殿→图34', resolve_npc_target_pls('name:英灵殿') === 34);
check('寻靶:无月之影→图0', resolve_npc_target_pls('name:无月之影') === 0);
check('寻靶:非字符串int原样返回', resolve_npc_target_pls(32) === 32);
$arealist = range(0, 40); $areanum = 34; // 回退池=35-40
$fallback = resolve_npc_target_pls('name:不存在的地图');
check('寻靶:未匹配回退随机(35-40池内)', $fallback >= 35 && $fallback <= 40);
unset($arealist, $areanum);

// ── 4. maphelp_npcword 展示同源 ──
$w0 = maphelp_npcword(0, $maps[0][0]['npc']);
check('展示:图0结构化红暮×1', strpos($w0, '×1') !== false);
$w34 = maphelp_npcword(34, $maps[34][0]['npc']);
check('展示:图34结构化英雄×10', strpos($w34, '×10') !== false);
check('展示:图34含5类数量', substr_count($w34, '×') === 5);
$w = maphelp_npcword(32, $maps[32][0]['npc']);
check('展示:32-0结构化SCP×4', strpos($w, '×4') !== false);
$w2 = maphelp_npcword(2, $maps[2][0]['npc']);
check('展示:种火sub级显示不变', strpos($w2, '✦覆唱的篝火') !== false);

echo $fail ? "\n{$fail}项FAIL\n" : "\n全部通过\n";
exit($fail ? 1 : 0);
