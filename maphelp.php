<?php

define('CURSCRIPT', 'help');

require './include/common.inc.php';
require './include/game.func.php';

include config('mapresource',$gamecfg);
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

// ── 从刷新配置推导各地图固定刷新NPC（mapresource的npc字段暂空，后续数据迁入后可替换）──
$mapnpcword = array();
$randnpcword = array();
if(isset($npc_spawn_config['init'])) {
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
			// pls=null 且有 sub 级配置的类型（如92篝火）由 sub 循环处理
			if(!isset($npc_sub_pls[$type])) $randnpcword[] = $entryword;
		} else {
			$mapnpcword[$pls][] = $entryword;
		}
	}
}
// sub级固定位置（type 92篝火：每个sub有固定刷新地图）
if(isset($npc_sub_pls)) {
	foreach($npc_sub_pls as $type => $subs) {
		foreach($subs as $subname => $plss) {
			foreach($plss as $p) {
				if($p != 99) $mapnpcword[$p][] = $subname;
			}
		}
	}
}

// ── 构建地图展示数据 ──
$mapdisplay = array();
foreach($maps as $mid => $branches) {
	if($mid == 99) continue; // 全图随机池单独处理
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

// 全图随机掉落池（map_id 99）
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

</STYLE>
EOT;

include template('maphelp');

?>
