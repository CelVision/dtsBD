<?php

	if(!defined('IN_GAME')) {
		exit('Access Denied');
	}

	
	include_once GAME_ROOT.'./gamedata/commandscfg.php';
	
	//生成指令
	function generate_random_command()
	{
		include GAME_ROOT.'./gamedata/commandscfg.php';
        global $gamevars, $commands,$log;
		$gamevars['rand_commands'] = Array();
		foreach($commands as $ckey => $clist)
		{
			$c_temp = $clist[0];
			$c_code = explode('-',substr($clist[1],1));
			$c_temp .= rand($c_code[0],$c_code[1]);
			$gamevars['rand_commands'][$ckey] = Array($c_temp,$clist[2]);
		}
		unset($clist);
		save_gameinfo();
		return $gamevars['rand_commands'];
	}


	
	//识别指令
	function command_input($in_commands)
	{
		global $log, $mode,$db,$tablepre,$now;
		global $gamevars;
		include_once GAME_ROOT . './include/system.func.php';

		if(!isset($gamevars['rand_commands']))
		{
		    $gamevars['rand_commands'] = generate_random_command();
		}
		

		if($in_commands == $gamevars['rand_commands'][0][0])
		{
		    $log .= "身份确认为软件工程师，指令输入成功<br>
			        <i>听好了，如果你是第一天来这里上班，请谨记以下三条！<br>
					一，上班不要迟到；二，下班了不要在公司逗留；三，不要谈论老总！<br>
				                                ———————  一位前辈的注释</i>";

		    return;
		}
		elseif($in_commands == $gamevars['rand_commands'][1][0])
		{
			if($gamevars['rand_commands'][1][1] == '1')
			{
				$gamevars['rand_commands'][1][1] = '0';
				$log .= "似乎是那个人的私人电话...你头脑一热，居然按下了拨打键！<br><span class='red'>这下便样衰了!</span><br>";
			    addnpc(1,0,1);
			    $db->query("INSERT INTO {$tablepre}chat (type,`time`,send,recv,msg) VALUES ('2','$now','【红暮】','','终于被你发现了吗...我在无月之影等你')");
				save_gameinfo();
		        return;
			}else{
				$log.="<span class='red'>您拨打的号码暂时无法接通</span><br>";
				return;
			}

		}
	    elseif($in_commands == $gamevars['rand_commands'][2][0])
		{
		    if($gamevars['rand_commands'][2][1] == '1')
			{
				$gamevars['rand_commands'][2][1] = '0';
				$log .= "你咬了咬牙，按下了控制台的按钮...<br><span class='cyan'>一股寒风从你耳边吹过...</span><br>";
			    addnpc(9,0,1);
			    $db->query("INSERT INTO {$tablepre}chat (type,`time`,send,recv,msg) VALUES ('2','$now','【蓝凝?】','','TARGET IN SIGHT，Deploying IN Admin Backend')");
				save_gameinfo();
		        return;
			}else{
				$log.="<span class='red'>您拨打的号码暂时无法接通</span><br>";
				return;
			}
		}
		elseif($in_commands == $gamevars['rand_commands'][3][0])
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    return;
		/*}
		elseif($in_commands == $commands_breakdown)
		{
		    $log .= '身份确认为软件工程师，指令输入成功';
		    return;*/
		}else{
			$log .= "指令执行失败<br>
			         <span class='red'>上班不要摸鱼，我在看着你</span><br>
					 <i>**玩意，天天压榨员工那么狠也没见工资高到哪儿去,**公司迟早要凉</i><br>
					 <br>
					 <span class='red'>写上面那段话的人已经被开除了，下次出现这样的情况我会惩处整个部门</span><br>";
			return;
		}
	}
    

?>