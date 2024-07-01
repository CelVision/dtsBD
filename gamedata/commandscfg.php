<?php

	if(!defined('IN_GAME'))exit('Access Denied');
//控制台配置文件//

	/*空白指令生成//
	$commands_blank = 'blank123';
	//红暮指令生成//
	$commands_crimson = "crimson";
	//蓝凝指令生成//
	$commands_azure = "azure".rand(100000,999999);
	//篝指令生成//
	$commands_kagari = "kagari".rand(1000000,9999999);
	//崩坏指令生成//
	$commands_breakdown = "breakdown".rand(1000,9999)."activate".rand(100,999);*/

    $commands = Array(
		//空白
		0=> Array(0=>'blank',1=>'r100-999', 2=>'1' ),
		//红暮
		1=> Array(0=>'crimson',1=>'r1000-9999',2=>'1'),
		//蓝凝
		2=> Array(0=>'azure',1=>'r10000-99999',2=>'1'),
		//篝
		3=> Array(0=>'kagari',1=>'r100000-999999',2=>'1'),

	);
?>
