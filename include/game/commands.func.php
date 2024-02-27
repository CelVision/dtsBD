<?php

	if(!defined('IN_GAME')) {
		exit('Access Denied');
	}

	//识别输入的指令
	function command_input($in_command)
	{
		global $log, $mode;
		global $commands_blank, $commands_crimson, $commands_azure, $commands_kagari, $commands_breakdown;
		include_once GAME_ROOT.'./gamedata/commandscfg.php';
		
		if($in_command == $commands_blank)
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    $mode = 'command';
		    return;
		}
		elseif($in_command == $commands_crimson)
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    $mode = 'command';
		    return;
		}
	    elseif($in_command == $commands_azure)
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    $mode = 'command';
		    return;
		}
		elseif($in_command == $commands_kagari)
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    $mode = 'command';
		    return;
		}
		elseif($in_command == $commands_breakdown)
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    $mode = 'command';
		    return;
		}else{
			$log .= '指令执行失败';
			return;
		}
	}

	