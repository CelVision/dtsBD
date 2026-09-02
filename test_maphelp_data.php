<?php
/**
 * maphelp.php 数据构建逻辑冒烟测试（无DB）
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');

// 加载数据源
include GAME_ROOT.'gamedata/cache/mapresource_1.php';
include GAME_ROOT.'gamedata/cache/npcdict_1.php';
include GAME_ROOT.'include/game/npcdict.func.php';

// 模拟 resources 配置（iteminfo/itemspkinfo）
$iteminfo = array('TO'=>'陷阱','TN'=>'可拾取陷阱','HH'=>'治疗','HS'=>'恢复体力','HB'=>'恢复体力',
	'WP'=>'殴系武器','WK'=>'斩系武器','WG'=>'射系武器','WC'=>'投系武器','WD'=>'爆系武器','WF'=>'灵系武器',
	'A'=>'饰品','ER'=>'道具','EW'=>'环境道具','X'=>'增幅','ZA'=>'特殊','VP'=>'书籍','ss'=>'歌谱',
	'PB'=>'毒药','PB2'=>'猛毒药','HM'=>'音乐','HT'=>'乐谱','MA'=>'食品','MD'=>'食品','ME'=>'食品',
	'MH'=>'食品','MS'=>'食品','MV'=>'食品','p'=>'箱子','Z'=>' misc');
$itemspkinfo = array('1'=>'属性1','d'=>'带毒','z'=>'酸蚀','w'=>'锐利','u'=>'即死','m'=>'魅惑',
	'rd'=>'密扣','b'=>'反击','am'=>'反甲','AM'=>'全反甲','🍎'=>'苹果系','j'=>'即死','97'=>'天气','10'=>'火种');

// ── 复制 maphelp.php 的构建逻辑 ──
function maphelp_kindword($ikind) {
	global $iteminfo;
	$r = '';
	if(substr($ikind,0,2)=="GB") {
		if ($ikind=="GBr") $r.="机枪弹药";
		if ($ikind=="GBi") $r.="气体弹药";
		if ($ikind=="GBh") $r.="重型弹药";
		if ($ikind=="GBe") $r.="能源弹药";
		if ($ikind=="GB") $r.="手枪弹药";
	} else {
		for ($k=1; $k<=strlen($ikind); $k++) {
			if (isset($iteminfo[substr($ikind,0,$k)])) { $r.=$iteminfo[substr($ikind,0,$k)]; break; }
		}
		if (substr($ikind,0,2)=="TO") $r.="（已埋设）";
		else if (substr($ikind,0,2)=="TN") $r.="（可拾取）";
		else if (isset($ikind[0]) && $ikind[0]=="P") {
			if ($ikind[strlen($ikind)-1]=="2") $r.="（猛毒）"; else $r.="（有毒）";
		}
	}
	return $r;
}
function maphelp_skword($iskind) {
	global $itemspkinfo;
	$r = '';
	for ($k=0; $k<strlen($iskind); $k++) {
		if (!isset($itemspkinfo[$iskind[$k]])) break;
		if ($k) $r.="+";
		$r.=$itemspkinfo[$iskind[$k]];
	}
	return $r;
}

$mapnpcword = array();
$randnpcword = array();
foreach($npc_spawn_config['init'] as $type => $cfg) {
	if(empty($cfg['num'])) continue;
	$pls = isset($cfg['pls']) ? $cfg['pls'] : null;
	$names = array();
	if(isset($npcdict[$type])) $names = array_keys($npcdict[$type]);
	if(empty($names)) continue;
	if(count($names) > 4) {
		$nameword = implode('、', array_slice($names, 0, 3)) . ' 等' . count($names) . '种';
	} else {
		$nameword = implode('、', $names);
	}
	$entryword = $nameword . ' ×' . $cfg['num'];
	if(is_array($pls)) {
		foreach($pls as $p) { if($p != 99) $mapnpcword[$p][] = $entryword; else $randnpcword[] = $entryword; }
	} elseif($pls === 99) {
		$randnpcword[] = $entryword;
	} elseif($pls === null) {
		if(!isset($npc_sub_pls[$type])) $randnpcword[] = $entryword;
	} else {
		$mapnpcword[$pls][] = $entryword;
	}
}
foreach($npc_sub_pls as $type => $subs) {
	foreach($subs as $subname => $plss) {
		foreach($plss as $p) {
			if($p != 99) $mapnpcword[$p][] = $subname;
		}
	}
}

$mapdisplay = array();
foreach($maps as $mid => $branches) {
	if($mid == 99) continue;
	foreach($branches as $bid => $branch) {
		if(empty($branch['plsinfo'])) continue;
		$items_disp = array();
		if(!empty($branch['item'])) {
			foreach($branch['item'] as $item) {
				list($iarea,$inum,$iname,$ikind,$ieff,$ista,$iskind) = $item;
				$items_disp[] = array(
					'area' => ($iarea==99 ? '每禁' : "{$iarea}禁"),
					'name' => $iname,
					'kind' => maphelp_kindword($ikind),
					'effsta' => $ieff.'/'.$ista,
					'sk' => maphelp_skword($iskind),
					'num' => $inum,
				);
			}
		}
		$mapdisplay[] = array(
			'title' => $branch['plsinfo'],
			'xy' => $branch['xyinfo'],
			'indoor' => empty($branch['isindoor']) ? '室内' : '室外',
			'area' => $branch['areainfo'],
			'items' => $items_disp,
			'npcword' => isset($mapnpcword[$mid]) ? implode('；', $mapnpcword[$mid]) : '',
		);
	}
}

$poolitems = array();
if(isset($maps[99][0]['item'])) {
	foreach($maps[99][0]['item'] as $item) {
		list($iarea,$inum,$iname,$ikind,$ieff,$ista,$iskind) = $item;
		$poolitems[] = array(
			'area' => ($iarea==99 ? '每禁' : "{$iarea}禁"),
			'name' => $iname,
			'kind' => maphelp_kindword($ikind),
			'effsta' => $ieff.'/'.$ista,
			'sk' => maphelp_skword($iskind),
			'num' => $inum,
		);
	}
}

// ── 输出统计 ──
echo "地图条目（含分支形态）: " . count($mapdisplay) . "\n";
$with_items = 0; $with_npc = 0; $no_items = 0;
foreach($mapdisplay as $md) {
	if(!empty($md['items'])) $with_items++; else $no_items++;
	if(!empty($md['npcword'])) $with_npc++;
}
echo "含固定掉落物: $with_items / 无掉落物: $no_items / 含固定NPC: $with_npc\n";
echo "全图随机池物品: " . count($poolitems) . "\n";
echo "全图随机NPC条目: " . count($randnpcword) . "\n";
echo "固定NPC地图分布: " . count($mapnpcword) . " 个地图\n\n";

// 抽样显示
echo "=== 样例：无月之影 ===\n";
foreach($mapdisplay as $md) {
	if($md['title'] == '无月之影') {
		echo "标题: {$md['title']}（{$md['xy']}·{$md['indoor']}）\n";
		echo "NPC: {$md['npcword']}\n";
		echo "前3件掉落物:\n";
		foreach(array_slice($md['items'], 0, 3) as $it) {
			echo "  [{$it['area']}] {$it['name']} | {$it['kind']} | {$it['effsta']} | {$it['sk']} | ×{$it['num']}\n";
		}
		break;
	}
}
echo "\n=== 样例：含NPC的地图 ===\n";
$shown = 0;
foreach($mapdisplay as $md) {
	if(!empty($md['npcword']) && $shown < 4) {
		echo "{$md['title']}: {$md['npcword']}\n";
		$shown++;
	}
}
echo "\n=== 样例：随机NPC ===\n";
foreach(array_slice($randnpcword, 0, 3) as $rw) echo "$rw\n";
echo "\n=== 样例：随机池前3件 ===\n";
foreach(array_slice($poolitems, 0, 3) as $pt) {
	echo "[{$pt['area']}] {$pt['name']} | {$pt['kind']} | ×{$pt['num']}\n";
}
