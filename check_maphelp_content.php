<?php
/** maphelp 渲染内容检查：新表格关键字段 */
$c = file_get_contents('http://localhost:8080/maphelp.php');
if($c === false) { echo "请求失败\n"; exit(1); }
$checks = array(
	'背景图头像' => 'img/location/0.jpg',
	'背景图CSS' => 'TD.mapbgcell',
	'未填充分支' => '（待补充）',
	'地图特性-商店' => '商店',
	'地图特性-安全箱' => '安全箱',
	'地图特性-医院' => '医院（可静养）',
	'基准遇敌率' => '基准遇敌率',
	'遇敌率数值40' => '40%',
	'遇敌率修正+20' => '地图修正+20',
	'特殊事件-撞人的少女' => '撞人的少女',
	'特殊事件-英灵殿之门' => '英灵殿之门',
	'分支形态' => '分支形态',
	'固定形态' => '固定形态',
	'简介字段' => '>简介<',
	'固定刷新NPC' => '固定刷新NPC',
	'随机池NPC' => '全图随机刷新NPC',
);
$ok = 0;
foreach($checks as $name => $needle) {
	$found = strpos($c, $needle) !== false;
	echo ($found ? 'OK' : 'MISS') . ": $name ($needle)\n";
	if($found) $ok++;
}
// 统计地图卡片数（每个分支一张卡）
$cards = substr_count($c, 'mapbgcell');
$unfilled = substr_count($c, '（待补充）');
$imgs = preg_match_all('/img\/location\/(\d+)\.jpg/', $c, $m) ? count(array_unique($m[1])) : 0;
echo "\n地图卡片数: {$cards}（其中待补充: {$unfilled}），不同背景图: {$imgs}\n";
echo $ok === count($checks) ? "=== 内容检查全部通过 ===\n" : "=== 有缺失 ===\n";
