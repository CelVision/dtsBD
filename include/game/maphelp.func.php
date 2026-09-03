<?php
if(!defined('IN_GAME')) exit('Access Denied');

// 地图百科NPC/事件/特性展示函数（maphelp.php 与 test_maphelp_data.php / test_map_npc_spawn.php 共用）
// 数据依赖：npcdict.func.php（get_npc_init_pool/get_npc_spawn_config/$npc_sub_pls）+ resources（$shops等）

// ── NPC名称列表→展示词（>4种取前3+「等N种」）──
function maphelp_npcname_word($names) {
	if(count($names) > 4) {
		return implode('、', array_slice($names, 0, 3)) . ' 等' . count($names) . '种';
	}
	return implode('、', $names);
}

// ── NPC名称解析：地图分支 'npc' 字段为 typeId 引用，名称查 npcdict；数量查刷新配置 ──
// 结构化 Array(typeId=>num)：数量直接来自地图分支（图级固定刷新，与spawn同源）；扁平 Array(typeId,...)：数量查全局config
function maphelp_npcword($mid, $types) {
	$parts = array();
	// 结构化分支：图级固定刷新，数量直接来自字段
	$keys = array_keys($types);
	if(!empty($keys) && $keys !== range(0, count($keys) - 1)) {
		foreach($types as $type => $num) {
			$names = get_npc_init_pool($type);
			if(empty($names)) continue;
			$parts[] = maphelp_npcname_word($names) . ' ×' . $num;
		}
		return implode('；', $parts);
	}
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
		$names = get_npc_init_pool($type);
		if(empty($names)) continue;
		$parts[] = maphelp_npcname_word($names) . ' ×' . $cfg['num'];
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

// ── 地图级属性：特性标签（resources）──
function maphelp_mapfeatures($mid) {
	global $shops, $depots, $hospitals;
	$tags = array();
	if(in_array($mid, $shops)) $tags[] = '商店';
	if(in_array($mid, $depots)) $tags[] = '安全箱';
	if(in_array($mid, $hospitals)) $tags[] = '医院（可静养）';
	return $tags;
}
