<?php
/**
 * 快速模式抽图与非连续图id收口测试
 * 1. rs_game_active_maps：全量回退/配方抽取/池不足/池空/exclude
 * 2. rs_game_build_mapinfo：mapinfo键集=activelist、lockbranch锁分支、空名分支兜底、flags提取
 * 3. arealist派生与99池池差集随机（复刻mode&2尾段与mode&16语义）
 * 4. rand_npc_pls（npcdict）：池差集/全排除兜底
 * 5. check_can_move（search）：非连续高id图合法
 * 6. 真实数据演练：gameresource_1的tag + gameresource_2配方 → 恰好10张
 * 7. 全量模式等价：真实maps全量构建 plsinfo键集0-34全量且全非空
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);   // 抑制search.func.php:8历史性include_once警告（battle.func.php不存在，与本次改动无关）
require GAME_ROOT.'./include/global.func.php';
include GAME_ROOT.'./include/system.func.php';
include GAME_ROOT.'./include/game/npcdict.func.php';
include GAME_ROOT.'./include/game/search.func.php';

$fail = 0;
function check($name, $cond) {
	global $fail;
	echo ($cond ? 'OK' : 'FAIL') . ": $name\n";
	if(!$cond) $fail++;
}
function mini_branch($name, $tag = '', $flags = NULL) {
	$b = Array('plsinfo'=>$name,'xyinfo'=>'X-1','areainfo'=>'','bg'=>'1','events'=>Array(),'isindoor'=>'0','item'=>Array(),'npc'=>Array());
	if($tag !== '') $b['tag'] = $tag;
	if($flags !== NULL) $b['flags'] = $flags;
	return $b;
}

// ── mini数据：非连续id，含lockbranch/空名分支/exclude目标/无tag图 ──
$mini_maps = Array(
	0  => Array(0 => mini_branch('无月之影', '', Array('deepzone','norandom_drop','norandom_npc','lockbranch'))),
	1  => Array(0 => mini_branch('端点', 'growth')),
	3  => Array(0 => mini_branch('雪之镇', 'growth'), 1 => mini_branch('', 'growth')),   // 分支1空名→兜底分支0
	5  => Array(0 => mini_branch('指挥中心')),                                            // 无tag→不入池
	7  => Array(0 => mini_branch('清水池', 'equip')),
	9  => Array(0 => mini_branch('墓地', 'equip')),
	11 => Array(0 => mini_branch('作战本部', 'equip')),
	12 => Array(0 => mini_branch('夏之镇', 'seed')),
	14 => Array(0 => mini_branch('光坂高校', 'shop')),
	18 => Array(0 => mini_branch('秋之镇', 'seed')),
	27 => Array(0 => mini_branch('花菱商厦', 'shop')),
	33 => Array(0 => mini_branch('雏菊之丘', 'equip', Array('deepzone','lockbranch'))),
	34 => Array(0 => mini_branch('英灵殿', 'seed', Array('deepzone','lockbranch'))),     // exclude目标
	99 => Array(0 => Array('item'=>Array(), 'npc'=>Array(14,90,91))),
);
$mini_cfg = Array('entry'=>0, 'pick'=>Array('growth'=>1,'equip'=>2,'seed'=>1,'shop'=>1), 'exclude'=>Array(34));

// ── 1. rs_game_active_maps ──
$all = rs_game_active_maps($mini_maps, null);
sort($all);
check('全量:无配置返回全部图id(除99)', $all == Array(0,1,3,5,7,9,11,12,14,18,27,33,34));

$active = rs_game_active_maps($mini_maps, $mini_cfg);
$tagof = Array();
foreach($active as $mid) {
	if($mid == 0) { $tagof['entry'] = true; continue; }
	foreach($mini_maps[$mid] as $b) { if(!empty($b['tag'])) { $tagof[$b['tag']][] = $mid; break; } }
}
check('抽取:共6张(1+2+1+1+entry)', count($active) == 6);
check('抽取:entry=0在列', in_array(0, $active));
check('抽取:exclude的34不在', !in_array(34, $active));
check('抽取:growth恰1张(1或3)', count($tagof['growth']) == 1 && in_array($tagof['growth'][0], Array(1,3), true));
check('抽取:equip恰2张(7/9/11/33)', count($tagof['equip']) == 2);
check('抽取:seed恰1张(12/18)', count($tagof['seed']) == 1 && in_array($tagof['seed'][0], Array(12,18), true));
check('抽取:shop恰1张(14/27)', count($tagof['shop']) == 1 && in_array($tagof['shop'][0], Array(14,27), true));
check('抽取:无tag的5不在', !in_array(5, $active));

// ── 池不足/池空 ──
$cfg2 = Array('entry'=>0, 'pick'=>Array('growth'=>5,'xxx'=>2), 'exclude'=>Array());
$active2 = rs_game_active_maps($mini_maps, $cfg2);
$gcount = 0;
foreach($active2 as $mid) { if($mid==1||$mid==3) $gcount++; }
check('抽取:池不足取全部(growth池2张)', $gcount == 2 && count($active2) == 3);   // 0+2growth, xxx池空跳过

// ── 2. rs_game_build_mapinfo（mini配方；内部独立抽取，断言自洽）──
list($mapid, $mapinfo) = rs_game_build_mapinfo($mini_maps, $mini_cfg);
$keys = array_keys($mapinfo['plsinfo']);
check('组装:plsinfo键集=6张', count($keys) == 6);
check('组装:含entry 0', in_array(0, $keys));
check('组装:不含exclude 34', !in_array(34, $keys, true));
$tagcnt = Array('growth'=>0,'equip'=>0,'seed'=>0,'shop'=>0);
foreach($keys as $mid) {
	if($mid == 0) continue;
	foreach($mini_maps[$mid] as $b) { if(!empty($b['tag'])) { if(isset($tagcnt[$b['tag']])) $tagcnt[$b['tag']]++; break; } }
}
check('组装:growth恰1', $tagcnt['growth'] == 1);
check('组装:equip恰2', $tagcnt['equip'] == 2);
check('组装:seed恰1', $tagcnt['seed'] == 1);
check('组装:shop恰1', $tagcnt['shop'] == 1);
check('组装:全部plsinfo非空', count(array_filter($mapinfo['plsinfo'])) == count($mapinfo['plsinfo']));
check('组装:entry锁分支0', $mapid[0] === 0);
// 全量模式：lockbranch(0/33/34)锁0、空名分支兜底、flags提取
list($fmapid, $fmapinfo) = rs_game_build_mapinfo($mini_maps, null);
check('组装:lockbranch图33锁分支0', $fmapid[33] === 0);
check('组装:lockbranch图34锁分支0', $fmapid[34] === 0);
check('组装:全量键集含全部图', count($fmapinfo['plsinfo']) == 13);
check('组装:空名分支兜底(3号plsinfo=雪之镇)', ($fmapid[3] === 0 || $fmapinfo['plsinfo'][3] === '雪之镇'));
check('组装:flags提取(0号deepzone)', in_array('deepzone', $fmapinfo['flags'][0]));
check('组装:无flags图空数组', $fmapinfo['flags'][1] === Array());

// ── 3. arealist派生（复刻mode&2尾段语义）与99池池差集 ──
$arealist_t = array_keys($mapinfo['plsinfo']);
$arealist_t = array_values(array_diff($arealist_t, Array(0)));
shuffle($arealist_t);
array_unshift($arealist_t, 0);
check('禁区:队首为入口0', $arealist_t[0] === 0);
check('禁区:其余=mapinfo键集-0乱序', count($arealist_t) == count($keys) && count(array_diff($arealist_t, $keys)) == 0 && count(array_unique($arealist_t)) == count($arealist_t));

// ── 3.5 全量模拟池（"刷到完整大地图再摘出"）──
// mini: 0号带deepzone+noranddrop+norandnpc+lockbranch，33号带deepzone+lockbranch
$pools = rs_game_sim_pools($mini_maps);
check('模拟池:drop排除0号(12图)', count($pools['drop']) == 12 && !in_array(0, $pools['drop']));
check('模拟池:npc排除0号(12图)', count($pools['npc']) == 12 && !in_array(0, $pools['npc']));
check('模拟池:npcdeep再排33号(10图)', count($pools['npcdeep']) == 10 && !in_array(0, $pools['npcdeep']) && !in_array(33, $pools['npcdeep']));
check('模拟池:99池不参与', !in_array(99, $pools['drop']) && !in_array(99, $pools['npc']));

// sim_npc_pls摘出采样：本局6图∩npc池=本局非0图；废图丢弃=NULL
// 注意：顶层$mapinfo即全局，改GLOBALS会污染顶层变量 → 全程用独立拷贝$mi_local
$gv_bak = $GLOBALS['gamevars'] ?? NULL;
$mi_local = $mapinfo;   // 本局6图快照（值拷贝，不受后续GLOBALS污染）
$GLOBALS['gamevars'] = Array('sim_full_pls' => $pools);
$GLOBALS['mapinfo'] = $mi_local;
$sims = Array(); $nulls = 0;
for($i = 0; $i < 500; $i++) {
	$p = sim_npc_pls(false);
	if($p === NULL) { $nulls++; continue; }
	$sims[$p] = 1;
}
$local_keys = array_keys($mi_local['plsinfo']);
$expect_local = array_values(array_intersect($local_keys, $pools['npc']));
check('模拟NPC:值域=本局∩npc池', count(array_diff(array_keys($sims), $expect_local)) == 0);
check('模拟NPC:废图丢弃发生(NULL>0)', $nulls > 0);
check('模拟NPC:覆盖本局全部合法图', count($sims) == count($expect_local));
// 躲避类：npcdeep池
$sims2 = Array();
for($i = 0; $i < 500; $i++) { $p = sim_npc_pls(true); if($p !== NULL) $sims2[$p] = 1; }
$expect_deep = array_values(array_intersect($local_keys, $pools['npcdeep']));
check('模拟NPC:躲避类值域=本局∩npcdeep池', count(array_diff(array_keys($sims2), $expect_deep)) == 0);
// 全量模式等价：sim池=全集=本局 → 永不丢弃
$GLOBALS['mapinfo'] = $fmapinfo;   // 全量13图
$nulls2 = 0;
for($i = 0; $i < 300; $i++) { if(sim_npc_pls(false) === NULL) $nulls2++; }
check('模拟NPC:全量模式永不丢弃', $nulls2 == 0);
$nulls3 = 0;
for($i = 0; $i < 300; $i++) { if(sim_npc_pls(true) === NULL) $nulls3++; }
check('模拟NPC:全量模式躲避类永不丢弃', $nulls3 == 0);

// 99池物品摘出复刻（mode&16语义）：sim池随机+非本局丢弃
$GLOBALS['mapinfo'] = $mi_local;
$hits = Array(); $dropped = 0;
for($i = 0; $i < 2000; $i++) {
	$rmap = $pools['drop'][array_rand($pools['drop'])];
	if(!isset($mi_local['plsinfo'][$rmap])) { $dropped++; continue; }
	$hits[$rmap] = 1;
}
$expect_drop = array_values(array_intersect($local_keys, $pools['drop']));
check('模拟落点:值域=本局∩drop池', count(array_diff(array_keys($hits), $expect_drop)) == 0);
check('模拟落点:覆盖本局全部合法图', count($hits) == count($expect_drop));
check('模拟落点:丢弃实例存在', $dropped > 0);
if($gv_bak !== NULL) $GLOBALS['gamevars'] = $gv_bak; else unset($GLOBALS['gamevars']);

// ── 4. rand_npc_pls ──
$mapinfo_bak = $GLOBALS['mapinfo'] ?? NULL;
$GLOBALS['mapinfo'] = Array('plsinfo' => Array(0=>'a', 3=>'b', 7=>'c', 12=>'d'));
$GLOBALS['norandnpc_pls'] = Array(0, 3);
$GLOBALS['deepzones'] = Array(0);
$hits = Array();
for($i = 0; $i < 500; $i++) { $hits[rand_npc_pls(false)] = 1; }
check('NPC落点:不落norandnpc(0/3)', !isset($hits[0]) && !isset($hits[3]) && count($hits) == 2);
$hits2 = Array();
for($i = 0; $i < 500; $i++) { $hits2[rand_npc_pls(true)] = 1; }
check('NPC落点:躲避类另避deepzone', !isset($hits2[0]) && count($hits2) == 2);   // 7,12
$GLOBALS['norandnpc_pls'] = Array(0,3,7,12);
check('NPC落点:全排除时兜底全键', rand_npc_pls(false) !== NULL);

// ── 5. check_can_move：非连续高id ──
$GLOBALS['mapinfo'] = Array('plsinfo' => Array(0=>'无月之影', 7=>'清水池', 33=>'雏菊之丘'));
$GLOBALS['log'] = '';
$GLOBALS['hplsinfo'] = Array();
$GLOBALS['arealist'] = Array(0, 7, 33);
$GLOBALS['areanum'] = 1;   // 0与7为禁区
$GLOBALS['hack'] = 0;
$GLOBALS['exp'] = 0;
check('移动:非连续高id33合法(旧>=plsnum会误拒)', check_can_move(0, 0, 33) === 1);
$GLOBALS['mapinfo']['plsinfo'][33] = '雏菊之丘';
$GLOBALS['arealist'] = Array(0, 33, 7);   // 33在禁区段(前areanum+1张)
check('移动:禁区拒绝仍生效', check_can_move(0, 0, 33) === 0);
check('移动:不存在的图id拒绝', check_can_move(0, 0, 5) === 0);
if($mapinfo_bak !== NULL) $GLOBALS['mapinfo'] = $mapinfo_bak;

// ── 6. 真实数据演练：只用2号（运行时真实状态：$maps与$game_maps_mode同源）──
unset($maps, $game_maps_mode);
include GAME_ROOT.'./gamedata/cache/gameresource_2.php';
check('真实:2号配方已定义', isset($game_maps_mode) && is_array($game_maps_mode));
check('真实:2号maps已带tag(派生同步)', strpos(file_get_contents(GAME_ROOT.'./gamedata/cache/gameresource_2.php'), "'tag' =>") !== false);
$real_active = rs_game_active_maps($maps, $game_maps_mode);
$real_tags = Array('growth'=>0,'equip'=>0,'seed'=>0,'shop'=>0);
foreach($real_active as $mid) {
	if($mid === 0 || $mid == 0) continue;
	$t = '';
	foreach($maps[$mid] as $b) { if(!empty($b['tag'])) { $t = $b['tag']; break; } }
	if($t !== '' && isset($real_tags[$t])) $real_tags[$t]++;
}
check('真实:恰好10张(0+3+3+2+1)', count($real_active) == 10);
check('真实:含entry无月之影0', in_array(0, $real_active));
check('真实:不含exclude英灵殿34', !in_array(34, $real_active, true));
check('真实:growth=3', $real_tags['growth'] == 3);
check('真实:equip=3', $real_tags['equip'] == 3);
check('真实:seed=2', $real_tags['seed'] == 2);
check('真实:shop=1', $real_tags['shop'] == 1);

// ── 7. 真实数据全量等价：plsinfo键集0-34全量且全非空 ──
list($rmapid, $rmapinfo) = rs_game_build_mapinfo($maps, null);
$expect_ids = range(0, 34);
check('真实:全量键集0-34', array_keys($rmapinfo['plsinfo']) == $expect_ids);
check('真实:全量plsinfo全非空', count(array_filter($rmapinfo['plsinfo'])) == 35);
check('真实:全量lockbranch(0/33/34)锁0', $rmapid[0] === 0 && $rmapid[33] === 0 && $rmapid[34] === 0);

// ── 8. 真实数据模拟池 ──
$rpools = rs_game_sim_pools($maps);
check('真实:drop池⊆0-34且排0号', count(array_diff($rpools['drop'], range(0,34))) == 0 && !in_array(0, $rpools['drop']));
check('真实:npc池⊆0-34且排0号', count(array_diff($rpools['npc'], range(0,34))) == 0 && !in_array(0, $rpools['npc']));
check('真实:npcdeep⊂npc且排deepzone图', count(array_diff($rpools['npcdeep'], $rpools['npc'])) == 0 && count($rpools['npcdeep']) < count($rpools['npc']));

// ── 结果 ──
echo $fail ? "\n*** {$fail} FAILURES ***\n" : "\nALL PASSED\n";
exit($fail ? 1 : 0);
?>
