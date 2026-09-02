<?php
/**
 * 模板编译冒烟测试：编译新模板并语法检查生成的tpl.php
 */
define('IN_GAME', TRUE);
define('GAME_ROOT', __DIR__ . '/');
$base = GAME_ROOT . 'gamedata/templates/';
$tpldir = 'templates/default';

require GAME_ROOT.'./include/global.func.php';
include GAME_ROOT.'./include/template.func.php';

$templates = array('help_intro', 'help_mix', 'help_npc', 'maphelp', 'header');
foreach($templates as $tpl) {
	$objfile = $base . "1_{$tpl}.tpl.php";
	parse_template($tpl, 1, $tpldir);
	if(file_exists($objfile)) {
		$out = shell_exec('php -l ' . escapeshellarg($objfile) . ' 2>&1');
		echo "$tpl: " . trim($out) . "\n";
	} else {
		echo "$tpl: 编译文件未生成！\n";
	}
}
