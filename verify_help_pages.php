<?php
/**
 * 页面验证：检查4个新页面关键内容与错误
 */
$pages = array(
	'helpintro.php' => array('玩法介绍', '常见问题', 'helpmix.php', 'helpnpc.php', 'maphelp.php'),
	'helpmix.php' => array('合成表一览', '道具合成', '同调合成'),
	'maphelp.php' => array('地图百科', '固定掉落物', '全图随机掉落', '无月之影', 'itemhelp.php'),
	'helpnpc.php' => array('NPC图鉴', 'BOSS NPC简介'),
);
$all_ok = true;
foreach($pages as $p => $markers) {
	$c = file_get_contents('http://localhost:8080/' . $p);
	if($c === false) { echo "$p: 请求失败\n"; $all_ok = false; continue; }
	$errs = array();
	if(strpos($c, 'Fatal error') !== false) $errs[] = 'Fatal error';
	if(strpos($c, 'Parse error') !== false) $errs[] = 'Parse error';
	if(strpos($c, 'Warning</b>') !== false) $errs[] = 'PHP Warning';
	if(strpos($c, 'Notice</b>') !== false) $errs[] = 'PHP Notice';
	$missing = array();
	foreach($markers as $m) if(strpos($c, $m) === false) $missing[] = $m;
	$status = (empty($errs) && empty($missing)) ? 'OK' : 'PROBLEM';
	if($status != 'OK') $all_ok = false;
	echo "$p: $status (len=" . strlen($c) . ")" . (empty($errs) ? '' : ' 错误:' . implode(',', $errs)) . (empty($missing) ? '' : ' 缺失:' . implode(',', $missing)) . "\n";
}
// 检查header下拉菜单
$c = file_get_contents('http://localhost:8080/helpintro.php');
$dd = strpos($c, 'helpintro.php') !== false && strpos($c, 'helpmix.php') !== false && strpos($c, 'maphelp.php') !== false && strpos($c, 'helpnpc.php') !== false && strpos($c, 'dropdown-menu') !== false;
echo "header下拉菜单(4项+css): " . ($dd ? 'OK' : 'PROBLEM') . "\n";
echo $all_ok && $dd ? "\n=== 全部页面验证通过 ===\n" : "\n=== 存在问题 ===\n";
