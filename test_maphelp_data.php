<?php
/**
 * maphelp 数据构建测试（无DB）——新版：NPC来源=地图分支npc字段（typeId引用）+npcdict
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');
require GAME_ROOT.'./include/global.func.php';
$gamecfg = 1;
include config('gameresource', $gamecfg);
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

$mapdisplay = array();
foreach($maps as $mid => $branches) {
	if($mid == 99) continue;
	foreach($branches as $bid => $branch) {
		if(empty($branch['plsinfo'])) continue;
		$mapdisplay[] = array(
			'title' => $branch['plsinfo'],
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
echo "地图条目（含分支形态）: " . count($mapdisplay) . "\n";
$with_npc = 0; foreach($mapdisplay as $md) if(!empty($md['npcword'])) $with_npc++;
echo "含固定刷新NPC条目: $with_npc\n";
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
echo "\n=== 随机池NPC ===\n";
foreach($randnpcword as $rw) echo "$rw\n";
