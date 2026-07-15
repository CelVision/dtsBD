<?php

if(!defined('IN_GAME')) {
	exit('Access Denied');
}

include_once GAME_ROOT.'./include/state.func.php';
include_once GAME_ROOT.'./include/game/battle.func.php';
include_once GAME_ROOT.'./include/game/itemmain.func.php';
include_once GAME_ROOT.'./include/game/revbattle.func.php';
include_once GAME_ROOT.'./include/game/revbattle.calc.php';
include_once GAME_ROOT.'./include/game/revcombat.func.php';
include_once GAME_ROOT.'./include/game/search.func.php';

# ============================================================
# 队列探索系统 — 替代旧版随机抽取探索 + 探索记忆
# 已启用：替代旧版随机抽取探索 + 探索记忆
# ============================================================

/**
 * 生成玩家个人物品队列（环形）
 * 每次进入地图时调用，从 mapitem 表读取当前地图全部 iid，shuffle 生成随机排列
 * 不从 mapitem 表删除物品，只记录 iid 顺序
 *
 * @param array &$data 玩家数据
 */
function gen_item_queue(&$data=NULL)
{
	global $db,$tablepre,$log;

	if(!isset($data))
	{
		global $pdata;
		$data = &$pdata;
	}
	extract($data,EXTR_REFS);

	# 读取当前地图全部物品 iid
	$result = $db->query("SELECT iid FROM {$tablepre}mapitem WHERE pls = '$pls'");
	$iids = array();
	while($row = $db->fetch_array($result))
	{
		$iids[] = (int)$row['iid'];
	}

	# shuffle 生成随机排列
	shuffle($iids);

	# 存入 clbpara
	$data['clbpara']['itmq'] = array(
		'pls'    => (int)$pls,
		'order'  => $iids,
		'cursor' => 0,
	);

	return;
}

/**
 * 检查并刷新队列（如果地图不匹配或队列为空）
 *
 * @param array &$data 玩家数据
 */
function check_item_queue(&$data=NULL)
{
	if(!isset($data))
	{
		global $pdata;
		$data = &$pdata;
	}
	extract($data,EXTR_REFS);

	if(empty($data['clbpara']['itmq']) || $data['clbpara']['itmq']['pls'] != (int)$pls)
	{
		gen_item_queue($data);
	}
}

/**
 * 向前探索（cursor+1，环形）
 * 与旧版 search() 流程一致：扣SP → 预事件 → 探索事件 → discover
 * 区别：discover 中调用 focus_item_queue 替代 focus_item
 *
 * @param array &$data 玩家数据
 */
function search_forward(&$data=NULL)
{
	global $log,$weather,$arealist,$areanum,$hack,$mapinfo,$hplsinfo,$gamestate;
	global $actlog;

	if(!isset($data))
	{
		global $pdata;
		$data = &$pdata;
	}
	extract($data,EXTR_REFS);

	if(!isset($mapinfo['plsinfo'][$pls]) && isset($hplsinfo[$pgroup]))
	{
		$hpls_flag = true;
	}
	else
	{
		if(array_search($pls,$arealist) <= $areanum && !$hack)
		{
			$log .= $mapinfo['plsinfo'][$pls].'是禁区，还是赶快逃跑吧！<br>';
			return;
		}
		$hpls_flag = false;
	}

	# 计算并扣除探索所需SP/HP
	$flag = calc_move_search_sp_cost($data,'search');
	if(!$flag) return;

	# 预探索阶段事件结算
	$moved = pre_move_search_events($data,'search');
	if($hp <= 0) return;

	$log .= "{$actlog}，你向前搜索着周围的一切。。。<br>";

	# 探索事件结算
	move_search_events($data,'search');
	if($hp <= 0) return;

	$enemyrate = \revbattle\calc_meetman_rate($data);
	discover_queue($enemyrate,$data,'forward');
	return;
}

/**
 * 向后探索（cursor-1，环形）
 * 流程与 search_forward 完全一致，仅方向不同
 * 替代旧版探索记忆功能
 *
 * @param array &$data 玩家数据
 */
function search_backward(&$data=NULL)
{
	global $log,$weather,$arealist,$areanum,$hack,$mapinfo,$hplsinfo,$gamestate;
	global $actlog;

	if(!isset($data))
	{
		global $pdata;
		$data = &$pdata;
	}
	extract($data,EXTR_REFS);

	if(!isset($mapinfo['plsinfo'][$pls]) && isset($hplsinfo[$pgroup]))
	{
		$hpls_flag = true;
	}
	else
	{
		if(array_search($pls,$arealist) <= $areanum && !$hack)
		{
			$log .= $mapinfo['plsinfo'][$pls].'是禁区，还是赶快逃跑吧！<br>';
			return;
		}
		$hpls_flag = false;
	}

	# 计算并扣除探索所需SP/HP
	$flag = calc_move_search_sp_cost($data,'search');
	if(!$flag) return;

	# 预探索阶段事件结算
	$moved = pre_move_search_events($data,'search');
	if($hp <= 0) return;

	$log .= "{$actlog}，你向后搜索着周围的一切。。。<br>";

	# 探索事件结算
	move_search_events($data,'search');
	if($hp <= 0) return;

	$enemyrate = \revbattle\calc_meetman_rate($data);
	discover_queue($enemyrate,$data,'backward');
	return;
}

/**
 * 队列版 discover — 与旧版 discover() 流程一致
 * 唯一区别：物品发现调用 focus_item_queue 而非 focus_item
 * 遇敌、事件、陷阱逻辑完全不变
 *
 * @param int $schmode 敌人发现率
 * @param array &$data 玩家数据
 * @param string $direction 'forward' 或 'backward'
 */
function discover_queue($schmode = 0,&$data=NULL,$direction = 'forward')
{
	global $now,$log,$mode,$command,$cmd;
	global $db,$tablepre,$gamestate,$aidata,$pls_bgm,$weather;
	global $event_obbs,$item_obbs,$enemy_obbs,$trap_min_obbs,$trap_max_obbs,$corpse_obbs,$corpseprotect;

	if(!isset($data))
	{
		global $pdata;
		$data = &$pdata;
	}
	extract($data,EXTR_REFS);

	# 判定BGM变化
	if(array_key_exists($pls,$pls_bgm))
	{
		$clbpara['pls_bgmbook'] = $pls_bgm[$pls];
	}
	else
	{
		if(isset($clbpara['pls_bgmbook']))
			unset($clbpara['pls_bgmbook']);
	}

	# AI事件
	include_once GAME_ROOT.'./include/game/aievent.func.php';
	$aidata = false;
	aievent(20);
	if(is_array($aidata))
	{
		$edata = $aidata;
		goto battle_flag;
	}

	# 地图事件
	$event_dice = rand(0,99);
	if($data['pass'] == 'bot') $event_obbs = -1;
	if(($event_dice < $event_obbs)||(($art!="Untainted Glory")&&($pls==34)&&($gamestate != 50))){
		include_once GAME_ROOT.'./include/game/event.func.php';
		$event_flag = event();
		if($event_flag)
		{
			$mode = 'command';
			return;
		}
	}

	# 陷阱判定
	$trap_dice=diceroll(99);
	if($trap_dice < $trap_max_obbs)
	{
		$trapresult = $db->query("SELECT * FROM {$tablepre}maptrap WHERE pls = '$pls' ORDER BY itmk DESC");
		$trpnum = $db->num_rows($trapresult);
		if($trpnum)
		{
			$fstrp = $db->fetch_array($trapresult);
			$xtrpflag = $fstrp['itmk'] == 'TOc' ? true : false;
			$real_trap_obbs = $xtrpflag ? 100 : calc_real_trap_obbs($data,$trpnum);
			if($trap_dice < $real_trap_obbs)
			{
				if(!$xtrpflag)
				{
					$itemno = rand(0,$trpnum-1);
					$db->data_seek($trapresult,$itemno);
					$fstrp = $db->fetch_array($trapresult);
				}
				$itm0=$fstrp['itm'];
				$itmk0=$fstrp['itmk'];
				$itme0=$fstrp['itme'];
				$itms0=$fstrp['itms'];
				$itmsk0=$fstrp['itmsk'];
				$tid = $fstrp['tid'];
				$db->query("DELETE FROM {$tablepre}maptrap WHERE tid='$tid'");
				itemfind($data);
				return;
			}
		}
	}

	# 敌人发现判定
	$mode_dice = rand(0,99);
	if($mode_dice < $schmode)
	{
		global $fog,$gamestate;

		$result = $db->query("SELECT * FROM {$tablepre}players WHERE pls='$pls' AND pid!='$pid'");
		if(!$db->num_rows($result)){
			$log .= '<span class="yellow">周围一个人都没有。</span><br>';
			if(CURSCRIPT == 'botservice') echo "noenemy=1\n";
			$mode = 'command';
			return;
		}

		$enemynum = $db->num_rows($result);
		$enemyarray = range(0, $enemynum - 1);
		shuffle($enemyarray);

		$meetman_flag = 0;
		foreach($enemyarray as $enum)
		{
			$db->data_seek($result, $enum);
			$edata = $db->fetch_array($result);
			$eid = $edata['pid'];
			# 使用fetch_playerdata_by_pid重新获取敌人数据，以应用各种在载入玩家数据时进行的判定
			$edata = fetch_playerdata_by_pid($eid);

			# 不管是活人还是死人，都只会在处于相同视界的情况下遭遇
			# 死斗模式无视视界限制
			if($horizon == $edata['horizon'] || (!$edata['type'] && $gamestate == 50))
			{
				if($edata['hp'] <= 0)
				{
					//直接略过无效尸体
					if($gamestate>=40) continue;
					$ret = false;
					# 略过无效尸体的条件是……全身装备/道具存在耐久不为0的部分
					# 但是空手和内衣又属于特例……这两个部位就只能判断效果不为0了
					foreach(array('wepe','wep2e','money','arhs','arbe','aras','arfs','arts','itms1','itms2','itms3','itms4','itms5','itms6') as $chkval)
					{
						if($edata[$chkval])
						{
							$ret = true;
							break;
						}
					}
					if(!$ret) continue;
					//计算尸体发现率
					$corpse_dice = rand(0,99);
					//击杀女主后，对女主尸体发现率大幅提升
					if($edata['type'] == 14 && isset($data['clbpara']['achvars']['kill_n14'])) $corpse_dice = 100;
					if($corpse_dice > $corpse_obbs)
					{
						$meetman_flag = 1;
						break;
					}
				}
				else
				{
					# 略过决斗者
					if ((!$edata['type'])&&($artk=='XX')&&(($edata['artk']!='XX')||($edata['art']!=$name))&&($gamestate<50)) continue;
					if (($artk!='XX')&&($edata['artk']=='XX')&&($gamestate<50)) continue;
					# 暂时直接略过盟友单位
					if(!empty($edata['clbpara']['mate']) && in_array($pid,$edata['clbpara']['mate'])) continue;

					# 「量心」技能效果判定（不会遭遇HP为1的敌人）：
					if(!check_skill_unlock('c19_dispel',$data) && !empty(get_skillpara('c19_dispel','active',$clbpara)) && $edata['hp'] == 1) continue;

					# 计算活人发现率
					$hide_r = \revbattle\calc_hide_rate($data,$edata);
					$enemy_dice = diceroll(99);
					# 把find_r杀了，现在技能都是用躲避率去判断的了，躲避率为负就等于发现率增幅了
					$meetman_flag = $enemy_dice < ($enemy_obbs - $hide_r) ? 1 : -1;
					break;
				}
			}
		}
		if($meetman_flag>0)
		{
			if($edata['hp'] > 0)
			{
				if($teamID&&(!$fog)&&($gamestate<40)&&($teamID == $edata['teamID']))
				{
					$bid = $edata['pid'];
					$action = 'team';
					findteam($edata);
					return;
				}
				elseif(isset($edata['clbpara']['post']) && $edata['clbpara']['post'] == $pid)
				{
					$bid = $edata['pid'];
					$action = 'neut';
					\revbattle\findneut($edata,1);
					return;
				}
				else
				{
					battle_flag:
					$active_r = \revbattle\calc_active_rate($data,$edata);
					$bid = $edata['pid'];
					$active_dice = diceroll(99);
					if($active_dice < $active_r)
					{
						$action = 'enemy'; $bid = $edata['pid'];
						if($data['pass'] != 'bot')
						{
							\revbattle\findenemy_rev($edata);
						}
						else
						{
							echo "进入战斗！<br>";
							\revcombat\rev_combat_prepare($data,$edata,1,'',0);
						}
						return;
					}
					else
					{
						if($data['pass'] != 'bot')
						{
							\revcombat\rev_combat_prepare($edata,$data,0);
						}
						else
						{
							\revcombat\rev_combat_prepare($edata,$data,0,'',0);
						}
						return;
					}
				}
			}
			else
			{
				$action = 'corpse'; $bid = $edata['pid'];
				findcorpse($edata);
				return;
			}
		}
		elseif($meetman_flag < 0)
		{
			$log .= '似乎有人隐藏着……<br>';
		}
		else
		{
			if($horizon == 1) $log .= '<span class="yellow">周围没有同处于灵子视界中的对象。</span><br>';
			else $log .= '<span class="yellow">周围一个人都没有。</span><br>';
		}
		$mode = 'command';
		return;
	}
	else
	{
		# 物品发现判定 — 使用队列
		$find_obbs = $item_obbs;
		$item_dice = rand(0,99);
		if($item_dice < $find_obbs)
		{
			$flag = focus_item_queue($data,$direction);
			if(!$flag)
			{
				$log .= '<span class="yellow">周围找不到任何物品。</span><br>';
				$mode = 'command';
				return;
			}
		}
		else
		{
			$log .= "但是什么都没有发现。<br>";
		}
	}
	$mode = 'command';
	return;
}

/**
 * 队列版 focus_item — 按队列顺序取物品
 * 向前：cursor+1（环形，越过末尾回到0）
 * 向后：cursor-1（环形，越过0回到末尾）
 * 跳过已被其他玩家拾取的物品（iid 不存在于 mapitem 表），不消耗探索次数
 * 拾取时才 DELETE，防止其他玩家拾取同一件
 *
 * @param array &$data 玩家数据
 * @param string $direction 'forward' 或 'backward'
 * @return int 1=找到物品, 0=未找到
 */
function focus_item_queue(&$data=NULL,$direction = 'forward')
{
	global $db,$tablepre,$log;

	if(!isset($data))
	{
		global $pdata;
		$data = &$pdata;
	}
	extract($data,EXTR_REFS);

	# 确保队列存在且匹配当前地图
	check_item_queue($data);

	$order = &$data['clbpara']['itmq']['order'];
	$cursor = &$data['clbpara']['itmq']['cursor'];
	$queue_len = count($order);

	if($queue_len <= 0)
	{
		return 0;
	}

	# 沿方向寻找下一个有效物品
	# 向前：cursor+1 → cursor+2 → ...（环形）
	# 向后：cursor-1 → cursor-2 → ...（环形）
	$steps = 1;
	$found_iid = null;
	$found_pos = null;

	while($steps <= $queue_len)
	{
		if($direction == 'forward')
		{
			$pos = ($cursor + $steps) % $queue_len;
		}
		else
		{
			$pos = (($cursor - $steps) % $queue_len + $queue_len) % $queue_len;
		}

		$iid = $order[$pos];

		# 检查该 iid 是否仍存在于 mapitem 表
		$result = $db->query("SELECT * FROM {$tablepre}mapitem WHERE iid = '$iid' AND pls = '$pls'");
		if($db->num_rows($result))
		{
			$found_iid = $iid;
			$found_pos = $pos;
			$mi = $db->fetch_array($result);
			break;
		}

		# 该物品已被其他玩家拾取，从队列中移除并继续
		unset($order[$pos]);
		$steps++;
	}

	# 重新索引数组
	$order = array_values($order);
	$queue_len = count($order);

	if($queue_len <= 0 || $found_iid === null)
	{
		return 0;
	}

	# 重新计算 found_pos（因为 unset 后索引可能变了）
	$found_pos = array_search($found_iid, $order);
	if($found_pos === false)
	{
		return 0;
	}

	# 更新 cursor 和最后搜索方向
	$cursor = $found_pos;
	$data['clbpara']['itmq']['last_dir'] = $direction;

	# 取物品数据
	$itm0=$mi['itm'];
	$itmk0=$mi['itmk'];
	$itme0=$mi['itme'];
	$itms0=$mi['itms'];
	$itmsk0=$mi['itmsk'];

	# 拾取时才删除，防止其他玩家拾取同一件
	$db->query("DELETE FROM {$tablepre}mapitem WHERE iid='$found_iid'");

	if($itms0)
	{
		if($data['pass'] == 'bot')
		{
			itemget($data);
		}
		else
		{
			itemfind();
			return 1;
		}
	}
	else
	{
		$log .= "但是什么都没有发现。可能是因为道具有天然呆属性。<br>";
	}
	return;
}

/**
 * 队列版 move — 移动后重新生成队列
 * 与旧版 move() 流程一致，区别：
 * 1. 移动后调用 gen_item_queue 替代 lost_searchmemory('all')
 * 2. discover 调用 discover_queue
 *
 * @param int $moveto 目标地图
 * @param array &$data 玩家数据
 */
function move_queue($moveto = 99,&$data=NULL)
{
	global $log,$weather,$mapinfo,$hplsinfo,$arealist,$areanum,$hack,$gamestate,$gamecfg;
	global $actlog;

	if(!isset($data))
	{
		global $pdata;
		$data = &$pdata;
	}
	extract($data,EXTR_REFS);

	$plsnum = sizeof($mapinfo['plsinfo']);

	if($pls == $moveto)
	{
		$log .= '相同地点，不需要移动。<br>';
		return;
	}

	if(!isset($mapinfo['plsinfo'][$pls]) && isset($hplsinfo[$pgroup]))
	{
		if(!array_key_exists($moveto,$hplsinfo[$pgroup]))
		{
			$log .= "地图上没有{$hplsinfo[$pgroup][$moveto]}啊？<br>";
			return;
		}
		$hpls_flag = true;
	}
	else
	{
		if((!array_key_exists($moveto,$mapinfo['plsinfo']))||($moveto == 'main')||($moveto < 0 )||($moveto >= $plsnum))
		{
			$log .= '请选择正确的移动地点。<br>';
			return;
		}
		elseif(array_search($moveto,$arealist) <= $areanum && !$hack)
		{
			$log .= $mapinfo['plsinfo'][$moveto].'是禁区，还是离远点吧！<br>';
			return;
		}
		$hpls_flag = false;
	}

	# 计算并扣除移动所需SP/HP
	$flag = calc_move_search_sp_cost($data,'move');
	if(!$flag) return;

	# 预移动、探索阶段事件结算
	$moved = pre_move_search_events($data,'move');
	if($hp <= 0) return;

	if(!$moved)
	{
		if(!$hpls_flag) $pgroup = 0;
		$pls = $moveto;
		$moveto_info = $hpls_flag ? $hplsinfo[$pgroup][$pls] : $mapinfo['plsinfo'][$pls];
		$log .= "{$actlog}，移动到了<span class=\"yellow\">{$moveto_info}</span>。<br>";
	}

	$log .= $mapinfo['areainfo'][$pls].'<br>';

	# 移动后重新生成物品队列（替代旧版 lost_searchmemory('all')）
	gen_item_queue($data);

	# 移动到指定地点，结算移动探索事件
	move_search_events($data,'move');
	if($hp <= 0) return;

	$enemyrate = \revbattle\calc_meetman_rate($data);
	discover_queue($enemyrate,$data,'forward');
	return;
}

/**
 * 丢弃物品时插入到队列中
 * 根据最后搜索方向决定插入位置：
 * - 向前搜索后丢弃：插入 cursor-1（向后一步即可发现）
 * - 向后搜索后丢弃：插入 cursor+1（向前一步即可发现）
 * 在 itemdrop() 中调用此函数替代 check_add_searchmemory
 *
 * @param int $drop_iid 新插入 mapitem 表的 iid
 * @param array &$data 玩家数据
 */
function insert_item_queue($drop_iid,&$data=NULL)
{
	if(!isset($data))
	{
		global $pdata;
		$data = &$pdata;
	}
	extract($data,EXTR_REFS);

	# 确保队列存在且匹配当前地图
	check_item_queue($data);

	$order = &$data['clbpara']['itmq']['order'];
	$cursor = $data['clbpara']['itmq']['cursor'];
	$last_dir = isset($data['clbpara']['itmq']['last_dir']) ? $data['clbpara']['itmq']['last_dir'] : 'forward';
	$queue_len = count($order);

	# 根据最后搜索方向插入到反方向一步的位置
	if($last_dir == 'backward')
	{
		# 向后搜索后丢弃：插入 cursor+1（向前一步即可发现）
		$insert_pos = ($cursor + 1) % ($queue_len + 1);
	}
	else
	{
		# 向前搜索后丢弃：插入 cursor-1（向后一步即可发现）
		$insert_pos = $cursor > 0 ? $cursor - 1 : $queue_len;
	}
	array_splice($order, $insert_pos, 0, array((int)$drop_iid));

	return;
}

/**
 * 获取队列信息（供模板显示用，替代旧版 smeo 探索记忆栏）
 *
 * @param array &$data 玩家数据
 * @return array 前方和后方物品信息
 */
function get_queue_info(&$data=NULL)
{
	global $db,$tablepre;

	if(!isset($data))
	{
		global $pdata;
		$data = &$pdata;
	}
	extract($data,EXTR_REFS);

	if(empty($data['clbpara']['itmq']) || $data['clbpara']['itmq']['pls'] != (int)$pls)
	{
		return array('forward' => null, 'backward' => null);
	}

	$order = $data['clbpara']['itmq']['order'];
	$cursor = $data['clbpara']['itmq']['cursor'];
	$queue_len = count($order);

	$result = array('forward' => null, 'backward' => null);

	if($queue_len <= 0) return $result;

	# 前方下一个物品
	$fwd_pos = ($cursor + 1) % $queue_len;
	$fwd_iid = $order[$fwd_pos];
	$r = $db->query("SELECT itm FROM {$tablepre}mapitem WHERE iid='$fwd_iid' AND pls='$pls'");
	if($db->num_rows($r))
	{
		$result['forward'] = $db->fetch_array($r)['itm'];
	}

	# 后方上一个物品
	$bwd_pos = (($cursor - 1) % $queue_len + $queue_len) % $queue_len;
	$bwd_iid = $order[$bwd_pos];
	$r = $db->query("SELECT itm FROM {$tablepre}mapitem WHERE iid='$bwd_iid' AND pls='$pls'");
	if($db->num_rows($r))
	{
		$result['backward'] = $db->fetch_array($r)['itm'];
	}

	return $result;
}

?>
