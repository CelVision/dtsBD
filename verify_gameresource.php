<?php
/** 验证迁移后的 gameresource_1.php 数据结构 */
define('IN_GAME', TRUE);
include 'gamedata/cache/gameresource_1.php';
echo "=== 实际地图数（0-34）===\n";
$realm = array(); foreach($maps as $k => $v) if($k != 99) $realm[] = $k;
echo "地图ID: " . min($realm) . "~" . max($realm) . "，共" . count($realm) . "个\n";
echo "\n=== 99段（全图随机池）===\n";
echo "maps[99] item数: " . count($maps[99][0]['item']) . "\n";
echo "maps[99] npc: " . (empty($maps[99][0]['npc']) ? '空' : implode(',', $maps[99][0]['npc'])) . "\n";
echo "\n=== 各真实地图的npc字段（初始固定刷新typeId）===\n";
include 'gamedata/cache/npcdict_1.php';
foreach($realm as $mid) {
	foreach($maps[$mid] as $bid => $b) {
		if(!empty($b['npc'])) {
			$names = array();
			foreach($b['npc'] as $t) {
				$names[] = $t . "(" . count($npcdict[$t]) . "个NPC)";
			}
			echo "map $mid 分支$bid [{$b['plsinfo']}]: " . implode(', ', $names) . "\n";
		}
	}
}
echo "\n=== 文末刷新配置 ===\n";
echo "npc_spawn_config init: " . count($npc_spawn_config['init']) . " 类, add: " . count($npc_spawn_config['add']) . " 类\n";
echo "npc_sub_pls 92 subs: " . implode(' | ', array_map(function($k,$v){return $k.'=>map'.implode('/',$v);}, array_keys($npc_sub_pls[92]), $npc_sub_pls[92])) . "\n";
