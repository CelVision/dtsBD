<?php
/**
 * 地图分类tag统计工具（快速模式挑图辅助）
 * 遍历 gameresource_1 的 $maps（忽略99池），按图聚合各分支tag：
 * - 各tag（growth/equip/shop/seed）的候选图清单
 * - 未分类图清单
 * - 同图分支tag不一致警告（约定：同一图各分支标同一类）
 * 用法：php scan_maptags.php
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');
include GAME_ROOT.'./gamedata/cache/gameresource_1.php';

$tagdefs = Array(
	'growth' => '一类·发育',
	'equip'  => '二类·装备',
	'shop'   => '商店',
	'seed'   => '种火',
);

$bytag = Array();       // tag => Array(mid,...)
$unlabeled = Array();   // Array(mid,...)
$conflict = Array();    // mid => Array(bi => tag,...)
$mapname = Array();     // mid => 主名（首个非空plsinfo）

foreach($maps as $mid => $branches) {
	if($mid == 99) continue;

	$tags = Array();
	$name = '';
	foreach($branches as $bi => $b) {
		$tags[$bi] = isset($b['tag']) ? $b['tag'] : '';
		if($name === '' && !empty($b['plsinfo'])) $name = $b['plsinfo'];
	}
	$mapname[$mid] = $name !== '' ? $name : '（待补充）';

	if(count(array_unique($tags)) > 1) {
		$conflict[$mid] = $tags;
	}

	$main = '';
	foreach($tags as $t) { if($t !== '') { $main = $t; break; } }
	if($main === '' || !isset($tagdefs[$main])) {
		$unlabeled[] = $mid;
	} else {
		$bytag[$main][] = $mid;
	}
}

echo "══ 地图分类tag统计（共 " . count($mapname) . " 张图，不含99池）══\n\n";

foreach($tagdefs as $t => $label) {
	$list = isset($bytag[$t]) ? $bytag[$t] : Array();
	echo "【{$label} {$t}】 " . count($list) . " 张\n";
	if($list) {
		foreach($list as $mid) echo "  map {$mid}  {$mapname[$mid]}\n";
	}
	echo "\n";
}

echo "【未分类】 " . count($unlabeled) . " 张\n";
foreach($unlabeled as $mid) echo "  map {$mid}  {$mapname[$mid]}\n";
echo "\n";

if($conflict) {
	echo "⚠ 同图分支tag不一致（约定轮换分支同类，请修正）：\n";
	foreach($conflict as $mid => $tags) {
		echo "  map {$mid} {$mapname[$mid]}: ";
		foreach($tags as $bi => $t) echo "分支{$bi}=" . ($t === '' ? '未标' : $t) . "  ";
		echo "\n";
	}
	echo "\n";
} else {
	echo "✓ 无同图分支tag冲突\n\n";
}

echo "══ 快速模式挑图备忘 ══\n";
echo "· 无月之影(map 0)锁定保留：入口图+弱版红暮+最终防线\n";
echo "· 英灵殿(map 34)：弱版红暮流程若不需要可不入选\n";
echo "· 入选10张写入 gameresource_2.php 顶层 \$game_maps_mode['active']\n";
echo "· 建议结构（可自行调整）：0无月之影 + 3发育 + 3装备 + 2商店 + 1种火\n";
?>
