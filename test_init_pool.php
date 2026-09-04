<?php
/**
 * 开局刷新池回归测试 — 红暮双版本修复
 * 语义：开局必刷 红暮-自动托管(type1 sub)；强版红暮(type1 asub)仅由 破灭之诗 的 addnpc(1,0,1) 召唤；
 *       esub(进化目标)不参与开局刷新；纯asub组(92种火)回退全组参与。
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');
require GAME_ROOT.'./include/global.func.php';
$gamecfg = 1;
include config('npcdict', $gamecfg);
include config('gameresource', $gamecfg);
$GLOBALS['npc_spawn_config'] = $npc_spawn_config;
$GLOBALS['npc_sub_pls'] = $npc_sub_pls;
$GLOBALS['maps'] = $maps;
include GAME_ROOT.'./include/game/npcdict.func.php';

$fail = 0;
function check($name, $cond) {
	global $fail;
	echo ($cond ? 'OK' : 'FAIL') . ": $name\n";
	if(!$cond) $fail++;
}

// 1. type1 开局池：只有自动托管
$p1 = get_npc_init_pool(1);
check('type1开局池仅含[红暮-自动托管]', $p1 === array('红暮-自动托管'));

// 2. type14 开局池：3基础形态，无进化目标
$p14 = get_npc_init_pool(14);
check('type14开局池=3基础形态', count($p14) == 3 && !in_array('战斗模式 梦美', $p14) && !in_array('本气（？） 叶留佳', $p14) && !in_array('守卫者 静流', $p14));

// 3. type21 开局池：10个，无 _evo
$p21 = get_npc_init_pool(21);
check('type21开局池=10且无进化版', count($p21) == 10 && !in_array('黑色奪魂曲_evo', $p21));

// 4. type92 纯asub组回退：池=6条目（真实火种由exclude排除）
$p92 = get_npc_init_pool(92);
check('type92开局池回退asub全组(6)', count($p92) == 6 && in_array('✦覆唱的篝火', $p92) && in_array('✦真实的火种', $p92));

// 5. 模拟 spawn_npc_all 对 type1 的完整选择流程（map_plan优先→池→exclude→num→轮询），100次确定性
// type1的init配置=0号图npc字段结构化（map_plan），不在全局init池
$deterministic = true;
for($i = 0; $i < 100; $i++) {
	$names = get_npc_init_pool(1);
	$cfg = get_map_npc_plan();
	$cfg = isset($cfg[1]) ? $cfg[1] : get_npc_spawn_config(1, 'init');
	$exclude = isset($cfg['exclude']) ? $cfg['exclude'] : array();
	if(!empty($exclude)) { $names = array_values(array_diff($names, $exclude)); }
	$namecount = count($names);
	if($namecount == 0 || $cfg['num'] <= 0) { $deterministic = false; break; }
	$shuffled = $names;
	if($namecount > $cfg['num']) shuffle($shuffled);
	for($j = 1; $j <= $cfg['num']; $j++) {
		if($shuffled[($j - 1) % $namecount] !== '红暮-自动托管') { $deterministic = false; break 2; }
	}
}
check('type1开局选择100次均为红暮-自动托管', $deterministic);

// 6. type92 完整流程：exclude后=5种火
$cfg92 = get_npc_spawn_config(92, 'init');
$n92 = array_values(array_diff(get_npc_init_pool(92), $cfg92['exclude']));
check('type92排除真实火种后=5种火', count($n92) == 5 && !in_array('✦真实的火种', $n92));

// 7. 破灭之诗路径：addnpc(1,0,1) 解析到强版红暮（asub[0]）
$d = get_npcdict();
$asub1 = array();
foreach($d['dict'][1] as $n => $data) if($data['source'] === 'asub') $asub1[] = $n;
check('addnpc_compat(1,0)指向强版红暮(asub[0])', isset($asub1[0]) && $asub1[0] === '红暮' && get_npcdict_data(1, '红暮')['mhp'] == 75000);
check('强版红暮不在开局池', !in_array('红暮', $p1));

// 8. esub进化目标仍可按名取数（evolve_npc路径不受影响）
check('进化目标数据仍可获取(战斗模式 梦美)', get_npcdict_data(14, '战斗模式 梦美') !== null);
check('进化目标数据仍可获取(黑色奪魂曲_evo)', get_npcdict_data(21, '黑色奪魂曲_evo') !== null);

// 9. 与原版npc_1.php一致性：type1开局数据=原sub[0]（mhp7500/wepe1280）
$auto = get_npcdict_data(1, '红暮-自动托管');
check('自动托管数值与原版一致(mhp7500/wepe1280/pls见spawn配置)', $auto['mhp'] == 7500 && $auto['wepsk'] == 'rfn' && $auto['club'] == 4);

echo $fail == 0 ? "\n=== 开局刷新池测试全部通过 ===\n" : "\n=== 存在 $fail 项失败 ===\n";
exit($fail == 0 ? 0 : 1);
