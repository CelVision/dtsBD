<?php

if(!defined('IN_GAME')) {
	exit('Access Denied');
}

function roommng_verify_db_game_structure()
{
	global $db,$gtablepre;

	$result = $db->query("DESCRIBE {$gtablepre}users roomid");
	if(!$db->num_rows($result))
	{
		$db->query("ALTER TABLE {$gtablepre}users ADD roomid tinyint(3) unsigned NOT NULL DEFAULT '0' AFTER groupid");
		echo "向users表中添加了字段roomid<br>";
	}

	$result = $db->query("DESCRIBE {$gtablepre}game groomid");
	if(!$db->num_rows($result))
	{
		$db->query("ALTER TABLE {$gtablepre}game ADD groomid tinyint(3) unsigned NOT NULL DEFAULT '0' AFTER gamestate");
		echo "向game表中添加了字段groomid<br>";
	}
	$result = $db->query("DESCRIBE {$gtablepre}game groomnums");
	if(!$db->num_rows($result))
	{
		$db->query("ALTER TABLE {$gtablepre}game ADD groomnums tinyint(3) unsigned NOT NULL DEFAULT '0' AFTER groomid");
		echo "向game表中添加了字段groomnums<br>";
	}
	$result = $db->query("DESCRIBE {$gtablepre}game groomownid");
	if(!$db->num_rows($result))
	{
		$db->query("ALTER TABLE {$gtablepre}game ADD groomownid char(15) NOT NULL default '' AFTER groomnums");
		echo "向game表中添加了字段groomownid<br>";
	}

	$result = $db->query("DESCRIBE {$gtablepre}game gamecfg");
	if(!$db->num_rows($result))
	{
		$db->query("ALTER TABLE {$gtablepre}game ADD gamecfg tinyint(3) unsigned NOT NULL DEFAULT '1' AFTER groomownid");
		echo "向game表中添加了字段gamecfg<br>";
	}

	$result = $db->query("DESCRIBE {$gtablepre}users u_templateid");
	if(!$db->num_rows($result))
	{
		$db->query("ALTER TABLE {$gtablepre}users ADD u_templateid tinyint(3) unsigned NOT NULL DEFAULT '0' AFTER lastword");
		echo "向users表中添加了字段u_templateid<br>";
	}

	$result = $db->query("DESCRIBE {$gtablepre}users nicksrev");
	if(!$db->num_rows($result))
	{
		$db->query("ALTER TABLE {$gtablepre}users ADD nicksrev text NOT NULL default '' AFTER nicks");
		echo "向users表中添加了字段nicksrev<br>";
	}

	$result = $db->query("SHOW INDEX FROM {$gtablepre}game");
	$gr = $db->fetch_array($result);
	if($gr['Column_name'] != 'groomid')
	{
		if(!empty($gr['Key_name']))
		{
			$db->query("ALTER TABLE`{$gtablepre}game` DROP PRIMARY KEY");
			echo "取消了game表的主键{$gr['Key_name']}<br>";
		}
		$db->query("ALTER TABLE`{$gtablepre}game` ADD PRIMARY KEY (`groomid`)");
		echo "将game表的主键变更为groomid<br>";
	}
	return;
}

# 探测当前用户所在房间绑定的游戏配置号（game表gamecfg列）
# 返回0=未登录/未进房/房间不存在（调用方保持默认1号配置）；返回N=房间模式号
# 供common.inc.php在require config(...)系列加载之前调用，实现"进快速模式房间→全套配置按2号加载"
# 副作用：没进房时置 $room_resolved_id=0，进房置房间号（供common.inc探测房间私有resource副本）
function roommng_resolve_gamecfg($cuser)
{
	global $db,$gtablepre,$roommodes;
	$GLOBALS['room_resolved_id'] = 0;

	if(empty($cuser)) return 0;

	$result = $db->query("SELECT roomid FROM {$gtablepre}users WHERE username='$cuser'");
	if(!$db->num_rows($result)) return 0;
	$roomid = $db->fetch_array($result)['roomid'];
	if(empty($roomid)) return 0;

	$result = $db->query("SELECT gamecfg FROM {$gtablepre}game WHERE groomid='$roomid'");
	if(!$db->num_rows($result)) return 0;
	$GLOBALS['room_resolved_id'] = (int)$roomid;
	$roomcfg = (int)$db->fetch_array($result)['gamecfg'];

	//未注册的配置号回退常规模式(1)
	return isset($roommodes[$roomcfg]) ? $roomcfg : 1;
}

# 创建一个新房间（$roommode：房间游戏模式号，即游戏配置号，见system.php的$roommodes）
function roommng_create_new_room(&$udata, $roommode = 1)
{
	global $db,$gtablepre,$now;
	global $startmin,$max_rooms,$ip_max_rooms,$rerror,$roommodes;

	if(!empty($udata['roomid']))
	{
		$rerror = 'alreay_in_room';
		return;
	}

	# 校验模式号：未注册的模式一律回退常规模式(1)
	$roommode = (int)$roommode;
	if(!isset($roommodes[$roommode])) $roommode = 1;

	# 根据IP判断是否可新建房间
	$ipresult = $db->query("SELECT roomid FROM {$gtablepre}users WHERE roomid>0 AND ip='{$udata['ip']}'");
	if($db->num_rows($ipresult) >= $ip_max_rooms)
	{
		$rerror = 'room_ip_limit';
		return;
	}

	# 统计当前已新建房间数量
	$result = $db->query("SELECT groomid FROM {$gtablepre}game WHERE groomid>0 ");
	$now_room_nums = $db->num_rows($result);
	if($now_room_nums >= $max_rooms)
	{
		$rerror = 'room_num_limit';
		return;
	}
	
	if($now_room_nums)
	{
		$room_ids = range(1,$max_rooms);
		while($now_room_ids[] = $db->fetch_array($result)['groomid']){};
		$new_room_id = array_shift(array_diff($room_ids,$now_room_ids));
	}
	else 
	{
		$new_room_id = 1;
	}

	# 获取当前游戏回数
	$result = $db->query("SELECT max(gamenum) AS max_value FROM {$gtablepre}game WHERE groomid>=0 ");
	$new_gamenum = $db->fetch_array($result)['max_value'];

	# 新建并初始化房间状态（gamecfg：本房间绑定的游戏配置号/模式）
	$starttime = $now + $startmin*5;
	$db->query("INSERT INTO {$gtablepre}game (gamenum,groomid,groomownid,gamestate,starttime,gamecfg) VALUES ('$new_gamenum','$new_room_id','{$udata['username']}','0','$starttime','$roommode')");

	# 派生房间私有resource副本（gameresource_room_{id}=模式主文件完整copy）：房主经resourcemng编辑副本，
	# 不影响模式主文件/其他房间；关闭房间自动删除
	roommng_spawn_room_resource($new_room_id, $roommode);

	# 加入房间
	roommng_join_room($new_room_id,$udata);

	return;
}

# 加入一个房间
function roommng_join_room($rkey,&$udata)
{
	global $db,$gtablepre,$rerror;

	if(!empty($udata['roomid']))
	{
		$rerror = 'alreay_in_room';
		return;
	}

	$result = $db->query("SELECT * FROM {$gtablepre}game WHERE groomid='$rkey'");
	if($db->num_rows($result))
	{
		$gdata = $db->fetch_array($result);
		$gdata['groomnums']++;
		# 更新房间内玩家数量
		$db->query("UPDATE {$gtablepre}game SET groomnums='{$gdata['groomnums']}' WHERE groomid='{$rkey}'");
		# 加入房间
		$db->query("UPDATE {$gtablepre}users SET roomid='{$rkey}' WHERE username='{$udata['username']}'");
	}
	else 
	{
		# 要加入的房间号不存在时，尝试新建一个
		roommng_create_new_room($udata);
	}
	return;
}

# 离开当前房间
function roommng_exit_room(&$udata)
{
	global $db,$gtablepre,$rerror;

	if(empty($udata['roomid']))
	{
		$rerror = 'not_in_room';
		return;
	}

	echo "已退出房间{$udata['roomid']}<br>";

	# 退出房间时更新房间状态
	$result = $db->query("SELECT * FROM {$gtablepre}game WHERE groomid='{$udata['roomid']}'");
	if($db->num_rows($result))
	{
		$gdata = $db->fetch_array($result);
		$gdata['groomnums']--;
		# 检查解散房间还是更新房间状态
		if($gdata['groomnums'] > 0)
		{
			# 房主退出房间时，将房主权限移交给房间内其他人
			if(!empty($gdata['groomownid']) && $gdata['groomownid'] == $udata['username'])
			{
				$result2 = $db->query("SELECT * FROM {$gtablepre}users WHERE roomid='{$udata['roomid']}' AND username!='{$udata['username']}'");
				if($db->num_rows($result2))
				{
					$udata2 = $db->fetch_array($result2);
					$new_ownid = $udata2['username'];
					echo "将房主权限移交给了{$udata2['username']}<br>";
				}
			}
			if(isset($new_ownid))
			{
				$db->query("UPDATE {$gtablepre}game SET groomnums='{$gdata['groomnums']}',groomownid='{$new_ownid}' WHERE groomid='{$udata['roomid']}'");
			}
			else 
			{
				$db->query("UPDATE {$gtablepre}game SET groomnums='{$gdata['groomnums']}' WHERE groomid='{$udata['roomid']}'");
			}
		}
		else 
		{
			roommng_close_room($udata['roomid']);
		}
	}
	# 更新用户状态
	$db->query("UPDATE {$gtablepre}users SET roomid = 0 WHERE username='{$udata['username']}'");
	return;
}

# 房主解散自己所在的房间
function roommng_close_own_room(&$udata)
{
	global $db,$gtablepre,$rerror;

	if(empty($udata['roomid']))
	{
		$rerror = 'not_in_room';
		return;
	}

	$result = $db->query("SELECT * FROM {$gtablepre}game WHERE groomid='{$udata['roomid']}'");
	if($db->num_rows($result))
	{
		$gdata = $db->fetch_array($result);
		# 不能解散没有房主的房间
		if(empty($gdata['groomownid']) || (!empty($gdata['groomownid']) && $gdata['groomownid'] != $udata['username']))
		{
			$rerror = 'room_close_limit';
			return;
		}
		# 不能解散正在游戏中的房间
		if($gdata['gamestate'] > 10 && $gdata['alivenum'])
		{
			$rerror = 'room_close_limit2';
			return;
		}
		# 解散房间
		roommng_close_room($udata['roomid']);
	}
	# 更新用户状态
	$db->query("UPDATE {$gtablepre}users SET roomid = 0 WHERE username='{$udata['username']}'");
	return;
}

# 派生/重置房间私有resource副本（幂等：先删旧副本，防同号旧局异常残留污染新房间）
# 源=模式主文件（直接拼路径不走config()，避免common.inc自动重建房间时被副本替换逻辑命中残留副本）
function roommng_spawn_room_resource($roomid, $mode)
{
	$dst = GAME_ROOT."./gamedata/cache/gameresource_room_{$roomid}.php";
	$src = GAME_ROOT."./gamedata/cache/gameresource_{$mode}.php";
	if(!file_exists($src)) $src = GAME_ROOT."./gamedata/cache/gameresource_1.php";
	$tag = '// ⚛ 房间私有resource副本 room='.$roomid.' mode='.$mode.' generated='.$GLOBALS['now'].'（编辑不影响模式主文件/其他房间；关闭房间自动删除；本行勿删）'."\n";
	if(file_exists($dst)) @unlink($dst);
	$content = readover($src);
	writeover($dst, $tag.$content, 'w');
	//writeover无返回值，以文件落盘+非空判定成功
	return file_exists($dst) && filesize($dst) > 100;
}

# 强制解散指定房间
function roommng_close_room($rkey,$adminlog = 0,$check_in_game = 0)
{
	global $db,$gtablepre,$rerror,$cmd_info;

	if(!$rkey)
	{
		$cmd_info .=  "不能关闭大房间！<br>";
		return;
	}

	$result = $db->query("SELECT * FROM {$gtablepre}game WHERE groomid='$rkey'");
	if($db->num_rows($result))
	{
		$gdata = $db->fetch_array($result);
		# 检查是否为闲置房间
		if($check_in_game)
		{
			# 不能解散正在游戏中的房间
			if($gdata['gamestate'] > 10 && $gdata['alivenum'])
			{
				$cmd_info .= "房间 {$rkey} 内仍有存活玩家，无法关闭。<br>";
				return;
			}
		}
		# 清空房间内玩家
		if($gdata['groomnums']) $db->query("UPDATE {$gtablepre}users SET roomid=0 WHERE roomid='{$rkey}'");
		# 删除房间私有resource副本
		@unlink(GAME_ROOT."./gamedata/cache/gameresource_room_{$rkey}.php");
		# 关闭房间
		$db->query("DELETE FROM {$gtablepre}game WHERE groomid='{$rkey}'");
		$cmd_info .= "已关闭房间 {$rkey} 号<br>";
		if($adminlog) adminlog('closeroom',$rkey);
	}
	else 
	{
		$cmd_info .= "房间 {$rkey} 未开启，或房间不存在！<br>";
	}
	return;
}



?>
