<?php
/**
 * 刷新配置迁移等价性测试：
 * 原npcdict.func.php内嵌的 $npc_spawn_config/$npc_sub_pls（迁移前快照，见下方$expected_*）
 * vs 新加载器从 gameresource 读取的结果
 * 两者必须 === 完全相等 ⇒ 开局/动态召唤行为一致
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');
require GAME_ROOT.'./include/global.func.php';
$gamecfg = 1;
include GAME_ROOT.'./include/game/npcdict.func.php';

// ── 迁移前快照（原npcdict.func.php内嵌数据；88已下沉图32分支npc字段，见$expected_map_plan）──
$expected_init = array(
	1  => array('num' => 1,   'pls' => 0),
	14 => array('num' => 3,   'pls' => 99),
	15 => array('num' => 0,   'pls' => 99),
	19 => array('num' => 0,   'pls' => 0),
	20 => array('num' => 10,  'pls' => 34),
	21 => array('num' => 5,   'pls' => 34),
	22 => array('num' => 2,   'pls' => 34),
	24 => array('num' => 3,   'pls' => 34),
	26 => array('num' => 1,   'pls' => 34),
	// 88 已下沉图32分支npc字段（结构化typeId=>num）
	90 => array('num' => 280, 'pls' => 99),
	91 => array('num' => 1,   'pls' => 99),
	92 => array('num' => 100, 'pls' => null, 'exclude' => array('✦真实的火种')),
);
// 图级固定刷新计划（地图分支结构化npc：typeId=>num；取代旧init的88条目）
$expected_map_plan = array(
	88 => array('num' => 4, 'pls' => 32, 'exclude' => array()),
);
$expected_add = array(
	1  => array('num' => 1,   'pls' => 0),
	2  => array('num' => 16,  'pls' => 99),
	4  => array('num' => 1,   'pls' => 33),
	5  => array('num' => 2,   'pls' => 99),
	6  => array('num' => 1,   'pls' => 99),
	7  => array('num' => 3,   'pls' => 99),
	9  => array('num' => 1,   'pls' => 0),
	11 => array('num' => 6,   'pls' => 99),
	12 => array('num' => 1,   'pls' => 99),
	13 => array('num' => 3,   'pls' => 99),
	15 => array('num' => 1,   'pls' => 99),
	19 => array('num' => 1,   'pls' => 0),
	25 => array('num' => 0,   'pls' => 99),
	89 => array('num' => 1,   'pls' => 99),
	90 => array('num' => 1,   'pls' => 99),
	99 => array('num' => 1,   'pls' => 99),
	92 => array('num' => 100, 'pls' => 99),
);
$expected_sub = array(
	92 => array(
		'✦覆唱的篝火' => array(2, 15),
		'✦爱恋的埋火' => array(3, 22),
		'✦怜悯的永火' => array(18, 23),
		'✦执念的残火' => array(20, 24),
		'✦希望的焰火' => array(12, 29),
	),
);

// ── 新加载器读取 ──
load_npc_spawn_data();
$fail = 0;

// init 全量对比
if($GLOBALS['npc_spawn_config']['init'] !== $expected_init) {
	echo "FAIL: init 配置不一致\n";
	$fail++;
	// 差异定位
	foreach($expected_init as $t => $c) {
		if(get_npc_spawn_config($t, 'init') !== $c) echo "  - type $t: 期望 " . var_export($c, true) . " 实际 " . var_export(get_npc_spawn_config($t, 'init'), true) . "\n";
	}
} else echo "OK: init 配置（12类）完全一致\n";

// 图级固定刷新计划对比（地图分支结构化npc）
$mplan = get_map_npc_plan();
if($mplan !== $expected_map_plan) {
	echo "FAIL: 地图固定刷新计划不一致\n";
	var_export($mplan); echo "\n";
	$fail++;
} else echo "OK: 地图固定刷新计划（88 SCP→图32×4）与旧init条目等价\n";

// 全量展开等价：旧init(含88) vs 新(init无88 + map_plan含88) 生成的刷新计划应逐type全等
$old_full = $expected_init + array(88 => array('num' => 4, 'pls' => 32, 'exclude' => array()));
ksort($old_full);
$new_full = $GLOBALS['npc_spawn_config']['init'] + $mplan;
ksort($new_full);
if($old_full !== $new_full) {
	echo "FAIL: 全量刷新计划不等价（旧88下沉前后合并对比）\n";
	$fail++;
} else echo "OK: 全量刷新计划等价（下沉前后每type的num/pls/exclude全等）\n";

// add 全量对比
if($GLOBALS['npc_spawn_config']['add'] !== $expected_add) {
	echo "FAIL: add 配置不一致\n";
	$fail++;
} else echo "OK: add 配置（17类）完全一致\n";

// sub_pls 全量对比
if($GLOBALS['npc_sub_pls'] !== $expected_sub) {
	echo "FAIL: sub_pls 配置不一致\n";
	$fail++;
} else echo "OK: sub_pls 配置（92类5个sub）完全一致\n";

// 边界：未知type默认值、未知sub名
$def = get_npc_spawn_config(999, 'init');
if($def !== array('num' => 0, 'pls' => 99, 'exclude' => array())) { echo "FAIL: 未知type默认值改变\n"; $fail++; }
else echo "OK: 未知type默认值一致\n";
if(get_npc_sub_pls(92, '不存在的名字') !== null) { echo "FAIL: 未知sub返回值改变\n"; $fail++; }
else echo "OK: 未知sub返回null一致\n";

// 边界：懒加载幂等（重复调用不重复加载）
load_npc_spawn_data();
if($GLOBALS['npc_spawn_config']['init'] === $expected_init) echo "OK: 重复懒加载幂等\n";
else { echo "FAIL: 重复懒加载破坏数据\n"; $fail++; }

// 模拟 rs_game mode&8 的读取路径（函数作用域内调用）
function sim_rs_game_npc() {
	// rs_game 在函数内 include npcdict.func.php 并直接调 spawn_npc_all（其内部经getter读GLOBALS）
	return get_npc_spawn_config(92, 'init') === array('num' => 100, 'pls' => null, 'exclude' => array('✦真实的火种'));
}
if(sim_rs_game_npc()) echo "OK: 函数作用域内读取正常（模拟rs_game mode&8路径）\n";
else { echo "FAIL: 函数作用域内读取异常\n"; $fail++; }

echo $fail === 0 ? "\n=== 等价性验证全部通过：切换gameresource与原配置效果一致 ===\n" : "\n=== 存在 $fail 项失败 ===\n";
exit($fail === 0 ? 0 : 1);
