<?php
/**
 * 地图分支npc字段结构化（typeId=>num 图级固定刷新）试点测试：88 SCP→图32
 * 1. 数据断言（双形态：结构化32图 / 扁平其他图）
 * 2. get_map_npc_plan 展开计划（含轮换分支、扁平不入计划、同type多图覆盖语义）
 * 3. maphelp_npcword 展示同源（结构化直接×num；扁平回退全局config；种火sub级不变）
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

// ── 1. 数据断言 ──
check('数据:32-0 npc结构化(88=>4)', $maps[32][0]['npc'] === Array(88 => 4));
check('数据:32-1占位分支同结构化', $maps[32][1]['npc'] === Array(88 => 4));
check('数据:全局init已无88条目', !isset($GLOBALS['npc_spawn_config']['init'][88]));
check('数据:99池npc扁平(14,90,91)', $maps[99][0]['npc'] === Array(14, 90, 91));
check('数据:图0 npc扁平(红暮)', $maps[0][0]['npc'] === Array(1));
check('数据:图34 npc扁平(英灵殿系)', $maps[34][0]['npc'] === Array(20, 21, 22, 24, 26));

// ── 2. get_map_npc_plan 展开计划 ──
$plan = get_map_npc_plan();
check('计划:88→图32×4(等价旧init条目)', $plan === Array(88 => Array('num' => 4, 'pls' => 32, 'exclude' => Array())));
check('计划:扁平引用图不入计划(仅88一项)', count($plan) === 1);

// 轮换分支：32参与轮换，两分支同配 → 任一分支选中结果一致
$mapid = Array(32 => 1); // 模拟轮换到分支1
$plan_b1 = get_map_npc_plan();
check('计划:轮换到分支1结果不变(两分支同配)', $plan_b1 === $plan);
unset($mapid); // $mapid未设时兜底分支0
$plan_b0 = get_map_npc_plan();
check('计划:$mapid未设兜底分支0', $plan_b0 === $plan);

// 同type配多图：后者覆盖（当前语义：固定刷新type↔图一一对应）
$savemaps = $GLOBALS['maps'];
$GLOBALS['maps'][33][0]['npc'] = Array(88 => 2); // 88同时配图32与图33
$plan_ov = get_map_npc_plan();
$GLOBALS['maps'] = $savemaps;
check('计划:同type多图后者覆盖(33>32)', $plan_ov === Array(88 => Array('num' => 2, 'pls' => 33, 'exclude' => Array())));

// ── 3. maphelp_npcword 展示同源 ──
$w = maphelp_npcword(32, $maps[32][0]['npc']);
check('展示:32图结构化直接×4(不查全局config)', strpos($w, '×4') !== false);
check('展示:32图名称非空', trim(str_replace('×4', '', $w)) !== '');
check('展示:32图不含×0(旧扁平+无全局条目的错误形态)', strpos($w, '×0') === false);
$w34 = maphelp_npcword(34, $maps[34][0]['npc']);
check('展示:34图扁平仍查全局config(×10)', strpos($w34, '×10') !== false);
$w2 = maphelp_npcword(2, $maps[2][0]['npc']);
check('展示:种火sub级显示不变', strpos($w2, '✦覆唱的篝火') !== false);
$w0 = maphelp_npcword(0, $maps[0][0]['npc']);
check('展示:图0扁平红暮×1', strpos($w0, '×1') !== false);

echo $fail ? "\n{$fail}项FAIL\n" : "\n全部通过\n";
exit($fail ? 1 : 0);
