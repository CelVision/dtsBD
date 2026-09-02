<?php

define('CURSCRIPT', 'help');

require './include/common.inc.php';
require './include/game.func.php';

include config('gameresource',$gamecfg);
include config('npcdict',$gamecfg);
include_once GAME_ROOT.'./include/game/npcdict.func.php';

// 物品类型中文（与 itemhelp.php 显示逻辑一致）
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

// 物品属性中文
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

// ── NPC名称解析：地图分支 'npc' 字段为 typeId 引用，名称查 npcdict；数量查刷新配置 ──
function maphelp_npcword($mid, $types) {
	$parts = array();
	foreach($types as $type) {
		$cfg = get_npc_spawn_config($type, 'init');
		// type 92种火：sub级固定位置，只显示固定在本地图的sub名（数量为全类型总数，不逐图显示）
		if(!empty($GLOBALS['npc_sub_pls'][$type])) {
			$subs = array();
			foreach($GLOBALS['npc_sub_pls'][$type] as $subname => $plss) {
				if(in_array($mid, $plss)) $subs[] = $subname;
			}
			if(!empty($subs)) $parts[] = implode('、', $subs);
			continue;
		}
		$names = get_npcdict_names($type);
		if(empty($names)) continue;
		if(count($names) > 4) {
			$nameword = implode('、', array_slice($names, 0, 3)) . ' 等' . count($names) . '种';
		} else {
			$nameword = implode('、', $names);
		}
		$parts[] = $nameword . ' ×' . $cfg['num'];
	}
	return implode('；', $parts);
}

// ── 特殊事件中文名（include/game/event.func.php 的 event_* 函数）──
function maphelp_eventword($events) {
	static $evnames = array(
		'mask_stranger' => '面具怪人',
		'crash_girl' => '撞人的少女',
		'slip_pool' => '脚滑落水',
		'hammer' => '大锤袭击',
		'crows' => '乌鸦群袭',
		'youkai' => '妖怪袭击',
		'pikachu' => '野生皮卡丘',
		'angel_barrage' => '天使部队演习',
		'kagari_graveyard' => '篝火之瞳',
		'kagari_hill' => '篝火少女',
		'valhalla_gate' => '英灵殿之门',
	);
	$words = array();
	foreach($events as $ev) {
		$words[] = isset($evnames[$ev]) ? $evnames[$ev] : $ev;
	}
	return implode('、', $words);
}

// ── 地图级属性：特性标签（resources）+ 基准遇敌率（combatcfg）──
function maphelp_mapfeatures($mid) {
	global $shops, $depots, $hospitals;
	$tags = array();
	if(in_array($mid, $shops)) $tags[] = '商店';
	if(in_array($mid, $depots)) $tags[] = '安全箱';
	if(in_array($mid, $hospitals)) $tags[] = '医院（可静养）';
	return $tags;
}
function maphelp_findrate($mid) {
	global $pls_find_modifier;
	$mod = isset($pls_find_modifier[$mid]) ? $pls_find_modifier[$mid] : 0;
	return array('base' => 40 + $mod, 'mod' => $mod);
}

// ── 构建地图展示数据（含未填充分支，对应位置留空）──
$mapdisplay = array();
foreach($maps as $mid => $branches) {
	if($mid == 99) continue; // 全图随机池单独处理
	$featureword = implode('；', maphelp_mapfeatures($mid));
	$findrate = maphelp_findrate($mid);
	$findword = $findrate['base'] . '%';
	if($findrate['mod']) $findword .= '<span class="grey">（地图修正' . ($findrate['mod'] > 0 ? '+' : '') . $findrate['mod'] . '）</span>';
	$branchnum = count($branches);
	foreach($branches as $bid => $branch) {
		$unfilled = empty($branch['plsinfo']);
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
		$bgimg = isset($branch['bg']) && file_exists('img/location/'.$branch['bg'].'.jpg')
			? 'img/location/'.$branch['bg'].'.jpg' : 'img/location/-1.png';
		$mapdisplay[] = array(
			'title' => $unfilled ? '' : $branch['plsinfo'],
			'unfilled' => $unfilled,
			'branchlabel' => $branchnum > 1 ? '分支形态'.($bid+1) : '固定形态',
			'xy' => $unfilled ? '' : $branch['xyinfo'],
			'indoor' => $unfilled ? '' : (empty($branch['isindoor']) ? '室内' : '室外'),
			'features' => $featureword,
			'findword' => $findword,
			'events' => (!$unfilled && !empty($branch['events'])) ? maphelp_eventword($branch['events']) : '',
			'area' => $unfilled ? '' : $branch['areainfo'],
			'bgimg' => $bgimg,
			'items' => $items_disp,
			'npcword' => !empty($branch['npc']) ? maphelp_npcword($mid, $branch['npc']) : '',
		);
	}
}

// 全图随机掉落池（map_id 99，'item'/'npc' 与普通地图同结构）
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
// 全图随机刷新NPC（99段 'npc' 字段的 typeId 引用）
$randnpcword = array();
if(!empty($maps[99][0]['npc'])) {
	foreach($maps[99][0]['npc'] as $type) {
		$cfg = get_npc_spawn_config($type, 'init');
		$names = get_npcdict_names($type);
		if(empty($names)) continue;
		if(count($names) > 4) {
			$nameword = implode('、', array_slice($names, 0, 3)) . ' 等' . count($names) . '种';
		} else {
			$nameword = implode('、', $names);
		}
		$randnpcword[] = $nameword . ' ×' . $cfg['num'];
	}
}

$extrahead = <<<EOT
<STYLE type=text/css>
BODY {
	FONT-SIZE: 10pt;MARGIN: 0; color:#eee; FONT-FAMILY: "Trebuchet MS","Gill Sans","Microsoft Sans Serif",sans-serif;
}
A {
	COLOR: #eee
}
A:visited {
	COLOR: #eee
}
A:active {
	color: #98fb98;text-decoration:underline
}
P{ line-height:16px
}

DIV.help {
	PADDING-LEFT: 1em;PADDING-right: 1em
}

.subtitle2 {
	font-family: "微软雅黑"; color: #98fb98; width: 100%;font-size: 16px;font-weight:900;
}

/* 地图卡片：背景图当头像 */
TD.mapbgcell {
	background-size: cover;
	background-position: center;
	background-repeat: no-repeat;
	padding: 0;
}
DIV.mapbgfill {
	min-height: 260px;
	width: 100%;
	height: 100%;
}

</STYLE>
EOT;

include template('maphelp');

?>
