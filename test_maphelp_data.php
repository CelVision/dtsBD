<?php
/**
 * maphelp 数据构建测试（无DB）——新版：NPC来源=地图分支npc字段（typeId引用）+npcdict
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');
require GAME_ROOT.'./include/global.func.php';
$gamecfg = 1;
include config('gameresource', $gamecfg);
include config('combatcfg', $gamecfg);   // $pls_find_modifier 遇敌率修正
include config('resources', $gamecfg);   // $shops/$depots/$hospitals 地图特性
include GAME_ROOT.'./include/game/npcdict.func.php';

// 复制 maphelp.php 的 maphelp_npcword 逻辑
function maphelp_npcword($mid, $types) {
	$parts = array();
	foreach($types as $type) {
		$cfg = get_npc_spawn_config($type, 'init');
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

// 复制 maphelp.php 的地图级属性函数
function maphelp_eventword($events) {
	static $evnames = array(
		'mask_stranger' => '面具怪人', 'crash_girl' => '撞人的少女', 'slip_pool' => '脚滑落水',
		'hammer' => '大锤袭击', 'crows' => '乌鸦群袭', 'youkai' => '妖怪袭击',
		'pikachu' => '野生皮卡丘', 'angel_barrage' => '天使部队演习',
		'kagari_graveyard' => '篝火之瞳', 'kagari_hill' => '篝火少女', 'valhalla_gate' => '英灵殿之门',
	);
	$words = array();
	foreach($events as $ev) $words[] = isset($evnames[$ev]) ? $evnames[$ev] : $ev;
	return implode('、', $words);
}
function maphelp_mapfeatures($mid) {
	$tags = array();
	if(in_array($mid, $GLOBALS['shops'])) $tags[] = '商店';
	if(in_array($mid, $GLOBALS['depots'])) $tags[] = '安全箱';
	if(in_array($mid, $GLOBALS['hospitals'])) $tags[] = '医院（可静养）';
	return $tags;
}

$mapdisplay = array();
foreach($maps as $mid => $branches) {
	if($mid == 99) continue;
	$features = implode('；', maphelp_mapfeatures($mid));
	$mod = isset($pls_find_modifier[$mid]) ? $pls_find_modifier[$mid] : 0;
	$findword = (40 + $mod) . '%' . ($mod ? '（修正' . ($mod > 0 ? '+' : '') . $mod . '）' : '');
	$branchnum = count($branches);
	foreach($branches as $bid => $branch) {
		$unfilled = empty($branch['plsinfo']);
		$mapdisplay[] = array(
			'title' => $unfilled ? '（待补充）' : $branch['plsinfo'],
			'unfilled' => $unfilled,
			'branchlabel' => $branchnum > 1 ? '分支形态'.($bid+1) : '固定形态',
			'features' => $features,
			'findword' => $findword,
			'events' => (!$unfilled && !empty($branch['events'])) ? maphelp_eventword($branch['events']) : '',
			'bgimg' => isset($branch['bg']) ? 'img/location/'.$branch['bg'].'.jpg' : '',
			'npcword' => !empty($branch['npc']) ? maphelp_npcword($mid, $branch['npc']) : '',
			'items_cnt' => count($branch['item']),
		);
	}
}
$poolitems = count($maps[99][0]['item']);
$randnpcword = array();
if(!empty($maps[99][0]['npc'])) {
	foreach($maps[99][0]['npc'] as $type) {
		$cfg = get_npc_spawn_config($type, 'init');
		$names = get_npcdict_names($type);
		if(count($names) > 4) $nameword = implode('、', array_slice($names, 0, 3)) . ' 等' . count($names) . '种';
		else $nameword = implode('、', $names);
		$randnpcword[] = $nameword . ' ×' . $cfg['num'];
	}
}

// ── 输出统计 ──
echo "地图卡片（含未填充分支）: " . count($mapdisplay) . "\n";
$unfilled_cnt = 0; $with_npc = 0;
foreach($mapdisplay as $md) { if($md['unfilled']) $unfilled_cnt++; if(!empty($md['npcword'])) $with_npc++; }
echo "其中待补充分支: $unfilled_cnt，含固定刷新NPC: $with_npc\n";
echo "全图随机池物品: $poolitems\n";
echo "全图随机NPC类别数: " . count($randnpcword) . "\n\n";

echo "=== 样例：含NPC的地图条目 ===\n";
$shown = 0;
foreach($mapdisplay as $md) {
	if(!empty($md['npcword']) && $shown < 8) {
		echo "[{$md['title']}] {$md['npcword']}\n";
		$shown++;
	}
}
echo "\n=== 样例：特性/遇敌率/事件 ===\n";
foreach($mapdisplay as $md) {
	if(!empty($md['features']) || strpos($md['findword'], '修正') !== false || !empty($md['events'])) {
		echo "[{$md['title']}] 特性:{$md['features']} 遇敌率:{$md['findword']}" . (empty($md['events']) ? '' : " 事件:{$md['events']}") . "\n";
	}
}
echo "\n=== 随机池NPC ===\n";
foreach($randnpcword as $rw) echo "$rw\n";
