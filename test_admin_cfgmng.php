<?php
/**
 * 管理界面配置文件管理（resourcemng/npcdictmng）功能测试
 * 使用 _98 临时配置文件，不触碰真实 _1 数据
 */
define('IN_GAME', TRUE);
define('IN_ADMIN', TRUE);
define('GAME_ROOT', __DIR__ . '/');
define('TPLDIR', 'templates/default');
define('TEMPLATEID', 1);
require GAME_ROOT.'./include/global.func.php';
include GAME_ROOT.'./include/template.func.php';
include GAME_ROOT.'./include/admin/admin.lang.php';
include GAME_ROOT.'./include/admin/cfgfile.func.php';
$tplrefresh = 1;
$gamecfg = 98;
$GLOBALS['magic_quotes_gpc'] = false;

function adminlog($op,$an1='',$an2='',$an3=''){}

$fail = 0;
function check($name, $cond) {
	global $fail;
	echo ($cond ? 'OK' : 'FAIL') . ": $name\n";
	if(!$cond) $fail++;
}

// 临时文件路径必须显式指定_98，严禁用config()（_98不存在时会回退到_1导致误写误删真实数据）
$res98 = GAME_ROOT.'./gamedata/cache/gameresource_98.php';
$dict98 = GAME_ROOT.'./gamedata/cache/npcdict_98.php';
if(substr($res98, -15) !== 'resource_98.php' || substr($dict98, -14) !== 'npcdict_98.php') exit('路径守卫失败');

// ── 1. 重建回环：gameresource ──
include config('gameresource', 1);
$orig_maps = $maps;
$orig_spawn = $npc_spawn_config;
$orig_spls = $npc_sub_pls;
regenerate_gameresource_file($res98, $maps, $npc_spawn_config, $npc_sub_pls);
unset($maps, $npc_spawn_config, $npc_sub_pls);
include $res98;
check('gameresource重建后maps一致', $maps == $orig_maps);
check('gameresource重建后spawn配置一致', $npc_spawn_config == $orig_spawn);
check('gameresource重建后sub_pls一致', $npc_sub_pls == $orig_spls);

// ── 2. 重建回环：npcdict ──
include config('npcdict', 1);
$orig_dict = $npcdict;
$orig_evo = $npc_evolve;
regenerate_npcdict_file($dict98, $npcdict, $npc_evolve);
unset($npcdict, $npc_evolve);
include $dict98;
check('npcdict重建后辞典一致', $npcdict == $orig_dict);
check('npcdict重建后进化表一致', $npc_evolve == $orig_evo);

// ── 3. resourcemng 列表渲染 ──
$command = 'list';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$out = ob_get_clean();
check('resourcemng列表含展开按钮', strpos($out, 'expand_0_0') !== false);
check('resourcemng列表含无月之影', strpos($out, '无月之影') !== false);
check('resourcemng列表含待补充标记', strpos($out, '（待补充）') !== false);

// ── 4. resourcemng 展开渲染 ──
$command = 'expand_0_0';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$out = ob_get_clean();
check('展开后含plsinfo输入框', strpos($out, 'name="plsinfo"') !== false);
check('展开后含物品行输入', strpos($out, 'name="item_0_area"') !== false);
check('展开后含保存按钮', strpos($out, "value='submit'") !== false);
check('展开后含新增行JS', strpos($out, 'add_item_row') !== false);

// ── 5. resourcemng 保存（改名字+改1行+删1行）──
$b0 = $maps[0][0];
$_POST = Array(
	'mid' => '0',
	'bi' => '0',
	'plsinfo' => gstrfilter('无月之影·改'),
	'xyinfo' => gstrfilter($b0['xyinfo']),
	'bg' => gstrfilter($b0['bg']),
	'areainfo' => gstrfilter($b0['areainfo']),
	'isindoor' => '0',
	'events' => gstrfilter(implode(',', $b0['events'])),
	'npcword' => gstrfilter(implode(',', $b0['npc'])),
	'itemcount' => (string)count($b0['item']),
);
$itemcols = Array('area','num','name','kind','eff','sta','sk');
foreach($b0['item'] as $i => $it) {
	foreach($itemcols as $ci => $c) {
		$_POST["item_{$i}_{$c}"] = gstrfilter((string)$it[$ci]);
	}
}
$editidx = 5;
$delidx = 3;
$_POST["item_{$editidx}_num"] = gstrfilter('12');
$_POST["itemdel_{$delidx}"] = '1';
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$saveinfo = $cmd_info;
ob_end_clean();
unset($maps);
include $res98;
$nb = $maps[0][0];
check('resourcemng保存:plsinfo已改', $nb['plsinfo'] === '无月之影·改');
check('resourcemng保存:物品行数减1', count($nb['item']) == count($b0['item']) - 1);
check('resourcemng保存:编辑行num改12且保持字符串', $nb['item'][$editidx - 1][1] === '12');
check('resourcemng保存:未改动行严格不变', $nb['item'][0] === $b0['item'][0]);
check('resourcemng保存:删除行后索引前移', $nb['item'][$delidx] === $b0['item'][$delidx + 1]);
check('resourcemng保存:npc未变', $nb['npc'] === $b0['npc']);
check('resourcemng保存:areainfo仅反斜杠规范化', str_replace('\\','',$nb['areainfo']) === str_replace('\\','',$b0['areainfo']));
check('resourcemng保存:提示信息', strpos($saveinfo, '修改') !== false);
check('resourcemng保存:其他地图未受影响', $maps[1][0]['plsinfo'] === $orig_maps[1][0]['plsinfo']);

// ── 6. resourcemng 空修改 ──
$b0 = $maps[0][0];
$_POST = Array(
	'mid' => '0', 'bi' => '0',
	'plsinfo' => gstrfilter($b0['plsinfo']),
	'xyinfo' => gstrfilter($b0['xyinfo']),
	'bg' => gstrfilter($b0['bg']),
	'areainfo' => gstrfilter($b0['areainfo']),
	'isindoor' => '0',
	'events' => gstrfilter(implode(',', $b0['events'])),
	'npcword' => gstrfilter(implode(',', $b0['npc'])),
	'itemcount' => (string)count($b0['item']),
);
foreach($b0['item'] as $i => $it) {
	foreach($itemcols as $ci => $c) {
		$_POST["item_{$i}_{$c}"] = gstrfilter((string)$it[$ci]);
	}
}
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$saveinfo = $cmd_info;
ob_end_clean();
check('resourcemng空修改:提示无有效修改', strpos($saveinfo, '未检测到') !== false);

// ── 6b. 全图随机池渲染（99不是地图，单独区块）──
$command = 'list';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$out = ob_get_clean();
check('列表不含99地图行', strpos($out, '>99-0<') === false);
check('列表含全图随机池区块', strpos($out, '全图随机') !== false);
check('池区含展开按钮expand_99_0', strpos($out, 'expand_99_0') !== false);

$command = 'expand_99_0';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$out = ob_get_clean();
check('池展开含物品行输入', strpos($out, 'name="item_0_area"') !== false);
check('池展开含NPC类别输入', strpos($out, 'name="npcword"') !== false);
check('池展开无地图名字段', strpos($out, 'name="plsinfo"') === false);
check('池展开含分页导航', strpos($out, '下一页') !== false);
check('池展开仅渲染第一页100行', substr_count($out, 'name="item_99_area"') == 1 && strpos($out, 'name="item_100_area"') === false);

// ── 6c. 池保存：仅提交第1页（0~99行），改NPC+删1行，其余页保持原样 ──
$p0 = $maps[99][0];
$p0cnt = count($p0['item']);
$_POST = Array(
	'mid' => '99',
	'bi' => '0',
	'npcword' => gstrfilter('14,90'),
	'itemcount' => (string)$p0cnt,
);
for($i = 0; $i < 100 && $i < $p0cnt; $i++) {
	foreach($itemcols as $ci => $c) {
		$_POST["item_{$i}_{$c}"] = gstrfilter((string)$p0['item'][$i][$ci]);
	}
}
$_POST['itemdel_5'] = '1';
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$saveinfo = $cmd_info;
ob_end_clean();
unset($maps);
include $res98;
$np = $maps[99][0];
check('池保存:npc改为14,90', $np['npc'] === Array(14,90));
check('池保存:仅删除页内1行', count($np['item']) == $p0cnt - 1);
check('池保存:页外行保持原样', $np['item'][100] === $p0['item'][101]);
check('池保存:页内未删行原样', $np['item'][0] === $p0['item'][0]);

// ── 6d. 从备份恢复（撤销6c的保存）──
check('恢复前备份文件已生成', file_exists($res98.'.bak'));
$command = 'restore';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$saveinfo = $cmd_info;
ob_end_clean();
check('恢复:提示成功', strpos($saveinfo, '已从备份恢复') !== false);
check('恢复:回到6c保存前状态(npc=14,90,91)', $maps[99][0]['npc'] === Array(14,90,91));
check('恢复:池物品数回到原值', count($maps[99][0]['item']) == $p0cnt);
check('恢复:此前保存的地图改动仍在', $maps[0][0]['plsinfo'] === '无月之影·改');

// ── 6e. flags编辑：修改34-0特性，验证保存生效 ──
$_POST = Array(
	'mid' => '34', 'bi' => '0',
	'flagsword' => gstrfilter('deepzone,noesc_tp'),
	'npcword' => gstrfilter(implode(',', $maps[34][0]['npc'])),
	'itemcount' => '0',
);
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$saveinfo = $cmd_info;
ob_end_clean();
unset($maps);
include $res98;
check('flags保存:34-0改为deepzone,noesc_tp', $maps[34][0]['flags'] === Array('deepzone','noesc_tp'));
check('flags保存:0-0原flags未受影响', $maps[0][0]['flags'] === Array('deepzone','norandom_drop','norandom_npc','lockbranch'));

// ── 6f. flags清空：POST空flagsword → 空数组 ──
$_POST['flagsword'] = gstrfilter('');
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
ob_end_clean();
unset($maps);
include $res98;
check('flags清空:34-0变空数组', $maps[34][0]['flags'] === Array());

// ── 6g. 未POST flagsword（其他字段保存）→ flags保持原样 ──
$_POST = Array(
	'mid' => '34', 'bi' => '0',
	'npcword' => gstrfilter(implode(',', $maps[34][0]['npc'])),
	'itemcount' => '0',
);
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
ob_end_clean();
unset($maps);
include $res98;
check('flags保留:未POST时保持空数组', $maps[34][0]['flags'] === Array());

// ── 6h. flags新增：给原无flags键的1-0加特性 ──
$_POST = Array(
	'mid' => '1', 'bi' => '0',
	'flagsword' => gstrfilter('deepzone'),
	'npcword' => gstrfilter(implode(',', $maps[1][0]['npc'])),
	'itemcount' => '0',
);
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
ob_end_clean();
unset($maps);
include $res98;
check('flags新增:1-0加上deepzone', $maps[1][0]['flags'] === Array('deepzone'));

// ── 6i. flags渲染：展开区含flags输入框 ──
$command = 'expand_0_0';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$out = ob_get_clean();
check('flags渲染:展开区含flagsword输入框', strpos($out, 'name="flagsword"') !== false);
check('flags渲染:值含deepzone', strpos($out, 'deepzone') !== false);
check('flags渲染:含flags说明行', strpos($out, 'flags说明') !== false);
$command = 'expand_99_0';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$out = ob_get_clean();
check('flags渲染:99池无flagsword输入框', strpos($out, 'name="flagsword"') === false);

// ── 6j. npcword结构化语法：32-0保存88:5 ──
$_POST = Array(
	'mid' => '32', 'bi' => '0',
	'npcword' => gstrfilter('88:5'),
	'itemcount' => '0',
);
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$saveinfo = $cmd_info;
ob_end_clean();
unset($maps);
include $res98;
check('npc语法:32-0保存88:5为结构化', $maps[32][0]['npc'] === Array(88 => 5));

// ── 6k. 混合语法（扁平+结构化）拒绝且保持原值 ──
$_POST['npcword'] = gstrfilter('88, 20:3');
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$saveinfo = $cmd_info;
ob_end_clean();
unset($maps);
include $res98;
check('npc语法:混合输入被拒绝', strpos($saveinfo, 'NPC类别格式错误') !== false);
check('npc语法:混合输入npc未变(仍88=>5)', $maps[32][0]['npc'] === Array(88 => 5));

// ── 6l. 非法项拒绝 ──
$_POST['npcword'] = gstrfilter('abc');
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$saveinfo = $cmd_info;
ob_end_clean();
unset($maps);
include $res98;
check('npc语法:非法项被拒绝', strpos($saveinfo, 'NPC类别格式错误') !== false);
check('npc语法:非法项npc未变', $maps[32][0]['npc'] === Array(88 => 5));

// ── 6m. npcword结构化渲染 ──
$command = 'expand_32_0';
ob_start();
include GAME_ROOT.'./include/admin/resourcemng.php';
$out = ob_get_clean();
check('npc语法:展开区渲染88:5', strpos($out, '88:5') !== false);
check('npc语法:输入框含新语法提示', strpos($out, 'typeId:数量') !== false);

// ── 7. npcdictmng 列表渲染 ──
$command = 'list';
ob_start();
include GAME_ROOT.'./include/admin/npcdictmng.php';
$out = ob_get_clean();
check('npcdictmng列表含红暮-自动托管', strpos($out, '红暮-自动托管') !== false);
check('npcdictmng列表含行标识', strpos($out, 'name="rtype_0"') !== false);
check('npcdictmng列表含展开按钮', strpos($out, 'expand_0') !== false);

// ── 8. npcdictmng 展开渲染 ──
$_POST = Array('rtype_0' => '1', 'rname_0' => gstrfilter('红暮-自动托管'));
$command = 'expand_0';
ob_start();
include GAME_ROOT.'./include/admin/npcdictmng.php';
$out = ob_get_clean();
check('展开后含mhp输入框', strpos($out, 'name="mhp"') !== false && strpos($out, 'value="7500"') !== false);
check('展开后含武器输入', strpos($out, 'name="wep"') !== false);
check('展开后含描述文本域', strpos($out, 'name="description"') !== false);
check('展开后含clubskillpara', strpos($out, 'name="str_clubskillpara"') !== false);

// ── 9. npcdictmng 保存（mhp+clubskillpara）──
$d1 = $npcdict[1]['红暮-自动托管'];
$csp = $d1['clubskillpara'];
$csp['c4_stable']['lvl'] = 6;
$_POST = Array(
	'typeId' => '1',
	'npcname' => gstrfilter('红暮-自动托管'),
	'mhp' => '8000',
	'str_clubskillpara' => gstrfilter(json_encode($csp, JSON_UNESCAPED_UNICODE)),
);
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/npcdictmng.php';
$saveinfo = $cmd_info;
ob_end_clean();
unset($npcdict);
include $dict98;
$nd = $npcdict[1]['红暮-自动托管'];
check('npcdictmng保存:mhp改8000保持整型', $nd['mhp'] === 8000);
check('npcdictmng保存:att未变', $nd['att'] === 750);
check('npcdictmng保存:描述未变', $nd['description'] === $d1['description']);
check('npcdictmng保存:clubskillpara已更新', $nd['clubskillpara']['c4_stable']['lvl'] === 6);
check('npcdictmng保存:武器未变', $nd['wep'] === $d1['wep']);
check('npcdictmng保存:条目总数不变', count($npcdict, COUNT_RECURSIVE) == count($orig_dict, COUNT_RECURSIVE));
check('npcdictmng保存:进化表未变', $npc_evolve == $orig_evo);

// ── 10. npcdictmng 非法JSON拒绝 ──
$d10 = $npcdict[1]['红暮-自动托管'];
$_POST = Array(
	'typeId' => '1',
	'npcname' => gstrfilter('红暮-自动托管'),
	'str_clubskillpara' => 'not-json{{',
);
$command = 'submit';
ob_start();
include GAME_ROOT.'./include/admin/npcdictmng.php';
$saveinfo = $cmd_info;
ob_end_clean();
unset($npcdict);
include $dict98;
check('npcdictmng非法JSON:提示解析失败', strpos($saveinfo, '解析失败') !== false);
check('npcdictmng非法JSON:数据未变', $npcdict[1]['红暮-自动托管']['clubskillpara'] == $d10['clubskillpara']);

// ── 10b. 从备份恢复（撤销场景9的保存）──
check('npcdict备份文件已生成', file_exists($dict98.'.bak'));
$command = 'restore';
ob_start();
include GAME_ROOT.'./include/admin/npcdictmng.php';
$saveinfo = $cmd_info;
ob_end_clean();
unset($npcdict);
include $dict98;
check('npcdict恢复:提示成功', strpos($saveinfo, '已从备份恢复') !== false);
check('npcdict恢复:mhp回到7500', $npcdict[1]['红暮-自动托管']['mhp'] === 7500);
check('npcdict恢复:clubskillpara回到lvl5', $npcdict[1]['红暮-自动托管']['clubskillpara']['c4_stable']['lvl'] === 5);

// ── 清理 ──
if(substr($res98, -15) === 'resource_98.php') {
	unlink($res98);
	if(file_exists($res98.'.bak')) unlink($res98.'.bak');
}
if(substr($dict98, -14) === 'npcdict_98.php') {
	unlink($dict98);
	if(file_exists($dict98.'.bak')) unlink($dict98.'.bak');
}

echo $fail == 0 ? "\n=== 管理配置功能测试全部通过 ===\n" : "\n=== 存在 $fail 项失败 ===\n";
exit($fail == 0 ? 0 : 1);
