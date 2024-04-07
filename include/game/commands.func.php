<?php

	if(!defined('IN_GAME')) {
		exit('Access Denied');
	}

	
	include_once GAME_ROOT.'./gamedata/commandscfg.php';
	
	//识别输入的指令
	function command_input($in_commands)
	{
		global $log, $mode;
		global $commands_blank, $commands_crimson, $commands_azure, $commands_kagari, $commands_breakdown;
		;
		if($in_commands == $commands_blank)
		{
		    $log .= "身份确认为软件工程师，指令输入成功<br>
			        <i>听好了，如果你是第一天来这里上班，请谨记以下三条！<br>
					一，上班不要迟到；二，下班了不要在公司逗留；三，不要谈论老总！<br>
				                                ———————  一位前辈的注释</i>";
		    $mode = 'command';
		    return;
		}
		elseif($in_commands == $commands_crimson)
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    $mode = 'command';
		    return;
		}
	    elseif($in_commands == $commands_azure)
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    $mode = 'command';
		    return;
		}
		elseif($in_commands == $commands_kagari)
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    $mode = 'command';
		    return;
		}
		elseif($in_commands == $commands_breakdown)
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    $mode = 'command';
		    return;
		}else{
			$log .= "指令执行失败<br>
			         <span class='red'>上班不要摸鱼，我在看着你</span><br>
					 <i>**玩意，天天压榨员工那么狠也没见工资高到哪儿去,**公司迟早要凉</i><br>
					 <br>
					 <span class='red'>写上面那段话的人已经被开除了，下次出现这样的情况我会惩处整个部门</span><br>";
			return;
		}
	}

	