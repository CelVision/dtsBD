<?php
if(!defined('IN_GAME')) {
	exit('Access Denied');
}

// gameresource / npcdict 数据文件重建（管理界面 resourcemng / npcdictmng 使用）
// 三个文件均为纯数据文件（变量定义加结束标记），可用 var_export 整体重建

function regenerate_gameresource_file($file, $maps, $npc_spawn_config, $npc_sub_pls) {
	$out = '<?php'."\n\n";
	$out .= '// gameresource：地图与资源总配置（管理界面 resourcemng 重建）'."\n";
	$out .= '// \'npc\' 字段：该地图分支初始固定刷新的NPC类别（typeId，指向npcdict辞典模板）'."\n";
	$out .= '// map 99 的 \'npc\'：全图随机刷新池的NPC类别'."\n";
	$out .= '// 完整刷新参数（num/pls/sub级位置）见文末 $npc_spawn_config / $npc_sub_pls'."\n";
	$out .= '$maps = ' . var_export($maps, true) . ";\n\n";
	$out .= '// ── NPC刷新配置（从 include/game/npcdict.func.php 迁入）──'."\n";
	$out .= '// init = 开局刷新(rs_game mode&8)，add = 动态召唤(addnpc)'."\n";
	$out .= '// pls: 0=无月之影, 34=英灵殿, 99=随机, 具体数字=固定地点, array=多地择一, null=按sub配置'."\n";
	$out .= '$npc_spawn_config = ' . var_export($npc_spawn_config, true) . ";\n\n";
	$out .= '// ── sub级别pls覆盖（typeId 92篝火：每个sub有固定刷新位置）──'."\n";
	$out .= '$npc_sub_pls = ' . var_export($npc_sub_pls, true) . ";\n?>\n";
	file_put_contents($file, $out);
	return $out;
}

function regenerate_npcdict_file($file, $npcdict, $npc_evolve) {
	$out = '<?php'."\n";
	$out .= 'if(!defined(\'IN_GAME\')) exit(\'Access Denied\');'."\n\n";
	$out .= '// NPC辞典 v1 — 自动生成（管理界面 npcdictmng 重建）'."\n";
	$out .= '// 数据来源：npc_1.php + addnpc_1.php + evonpc_1.php'."\n";
	$out .= '// 冲突处理：typeId=1 两版红暮 | typeId=15 取add版 | typeId=19 取add版 | typeId=90 迷之搬运工移至99 | typeId=92 取npc版'."\n\n";
	$out .= '$npcdict = ' . var_export($npcdict, true) . ";\n\n";
	$out .= '$npc_evolve = ' . var_export($npc_evolve, true) . ";\n?>\n";
	file_put_contents($file, $out);
	return $out;
}

// 解码 gstrfilter 的输入：POST 进入 admin 模块时已被 htmlspecialchars 转义
function admin_cfg_decode($str) {
	return htmlspecialchars_decode($str, ENT_COMPAT);
}

// 按“保持原字段类型”的规则写入新值（原字符串存字符串，原整数存整数）
function admin_cfg_typed($old, $new) {
	return is_int($old) ? intval($new) : (string)$new;
}

// 生成 itme0~itme6 等序号字段名列表
function admin_cfg_num_fields($prefix) {
	$a = Array();
	for($i = 0; $i <= 6; $i++) $a[] = $prefix.$i;
	return $a;
}

function admin_cfg_in($name, $value, $size = 10, $maxlength = 250) {
	return '<input size="'.$size.'" type="text" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" maxlength="'.$maxlength.'">';
}

// 地图物品行HTML（resourcemng 展开区/全图随机池共用）
// $base 为全局行号起点（分页用），返回 Array(行HTML, 本页行数)
function admin_map_itemrows($items, $mapitem_cols, $base = 0) {
	$rows = '';
	$cnt = 0;
	$sizes = Array('area'=>6,'num'=>6,'name'=>16,'kind'=>6,'eff'=>6,'sta'=>6,'sk'=>6);
	foreach($items as $it) {
		$gi = $base + $cnt;
		$rows .= '<tr>';
		foreach($mapitem_cols as $ci => $c) {
			$v = htmlspecialchars((string)(isset($it[$ci]) ? $it[$ci] : ''));
			$rows .= '<td><input size="'.$sizes[$c].'" type="text" name="item_'.$gi.'_'.$c.'" value="'.$v.'"></td>';
		}
		$rows .= '<td style="text-align:center"><input type="checkbox" name="itemdel_'.$gi.'" value="1"></td></tr>';
		$cnt++;
	}
	return Array($rows, $cnt);
}

// 物品表分页导航（大列表如全图随机池分页提交，避免超过 max_input_vars）
function admin_cfg_pagebtns($mid, $bi, $page, $total, $pagelen) {
	$pages = $total > 0 ? ceil($total / $pagelen) : 1;
	$b = '<span class="yellow">第'.($page + 1).'/'.$pages.'页</span> ';
	if($page > 0) {
		$b .= '<input type="submit" value="上一页" onclick="$(\'command\').value=\'expand_'.$mid.'_'.$bi.'_'.($page - 1).'\'"> ';
	}
	if(($page + 1) * $pagelen < $total) {
		$b .= '<input type="submit" value="下一页" onclick="$(\'command\').value=\'expand_'.$mid.'_'.$bi.'_'.($page + 1).'\'">';
	}
	return $b;
}

// NPC辞典条目编辑表（npcdictmng 展开区，参考 admin_npcmng 的属性表布局）
function admin_npcdict_edit_table($d) {
	$t = '<tr><th width="80">属性名</th><th width="110">属性值</th><th width="80">属性名</th><th width="110">属性值</th><th width="80">属性名</th><th width="110">属性值</th></tr>';
	$basic = Array(
		Array('性别','gd',8), Array('头像','icon',8), Array('等级','lvl',8),
		Array('社团','club',8), Array('姿态','pose',8), Array('策略','tactic',8),
		Array('生命上限','mhp',8), Array('体力上限','msp',8), Array('金钱','money',8),
		Array('攻击','att',8), Array('防御','def',8), Array('怒气','rage',8),
		Array('技能组','skills',8), Array('技能','skill',8), Array('RP','rp',8),
		Array('杀人数','killnum',8), Array('状态','state',8), Array('帮助计数','help_count',8),
		Array('受伤','inf',8), Array('队伍名','teamID',10), Array('队伍密码','teamPass',10),
	);
	$n = count($basic);
	for($i = 0; $i < $n; $i += 3) {
		$t .= '<tr>';
		for($j = 0; $j < 3; $j++) {
			$k = $i + $j;
			if($k < $n) {
				$t .= '<th>'.$basic[$k][0].'</th><td>'.admin_cfg_in($basic[$k][1], isset($d[$basic[$k][1]]) ? $d[$basic[$k][1]] : '', $basic[$k][2]).'</td>';
			} else {
				$t .= '<th></th><td></td>';
			}
		}
		$t .= '</tr>';
	}
	$prof = Array(Array('殴熟','wp'),Array('斩熟','wk'),Array('枪熟','wg'),Array('投熟','wc'),Array('爆熟','wd'),Array('灵熟','wf'));
	$t .= '<tr>';
	for($i = 0; $i < 6; $i++) $t .= '<th>'.$prof[$i][0].'</th><td>'.admin_cfg_in($prof[$i][1], isset($d[$prof[$i][1]]) ? $d[$prof[$i][1]] : '', 8).'</td>';
	$t .= '</tr>';
	$eqs = Array(Array('武器','wep'),Array('防具(体)','arb'),Array('防具(头)','arh'),Array('防具(腕)','ara'),Array('防具(足)','arf'),Array('饰品','art'));
	$t .= '<tr><th colspan="6" class="tdtitle">装备</th></tr><tr><th>部位</th><th>名字</th><th>类别</th><th>效果</th><th>耐久</th><th>属性</th></tr>';
	foreach($eqs as $eq) {
		$p = $eq[1];
		$k = substr($p, 0, 3);
		$t .= '<tr><th>'.$eq[0].'</th><td>'.admin_cfg_in($p, isset($d[$p]) ? $d[$p] : '', 18)
			.'</td><td>'.admin_cfg_in($k.'k', isset($d[$k.'k']) ? $d[$k.'k'] : '', 6, 20)
			.'</td><td>'.admin_cfg_in($k.'e', isset($d[$k.'e']) ? $d[$k.'e'] : '', 8)
			.'</td><td>'.admin_cfg_in($k.'s', isset($d[$k.'s']) ? $d[$k.'s'] : '', 8)
			.'</td><td>'.admin_cfg_in($k.'sk', isset($d[$k.'sk']) ? $d[$k.'sk'] : '', 8, 40).'</td></tr>';
	}
	$t .= '<tr><th colspan="6" class="tdtitle">包裹</th></tr><tr><th>格位</th><th>名字</th><th>类别</th><th>效果</th><th>耐久</th><th>属性</th></tr>';
	for($i = 0; $i <= 6; $i++) {
		$t .= '<tr><th>包裹'.($i + 1).'</th><td>'.admin_cfg_in('itm'.$i, isset($d['itm'.$i]) ? $d['itm'.$i] : '', 18)
			.'</td><td>'.admin_cfg_in('itmk'.$i, isset($d['itmk'.$i]) ? $d['itmk'.$i] : '', 6, 20)
			.'</td><td>'.admin_cfg_in('itme'.$i, isset($d['itme'.$i]) ? $d['itme'.$i] : '', 8)
			.'</td><td>'.admin_cfg_in('itms'.$i, isset($d['itms'.$i]) ? $d['itms'.$i] : '', 8)
			.'</td><td>'.admin_cfg_in('itmsk'.$i, isset($d['itmsk'.$i]) ? $d['itmsk'.$i] : '', 8, 40).'</td></tr>';
	}
	$t .= '<tr><th>描述</th><td colspan="5"><textarea name="description" cols="90" rows="3">'.htmlspecialchars(isset($d['description']) ? $d['description'] : '').'</textarea></td></tr>';
	$csp = isset($d['clubskillpara']) ? json_encode($d['clubskillpara'], JSON_UNESCAPED_UNICODE) : '';
	$t .= '<tr><th>社团技能参数</th><td colspan="5"><textarea name="str_clubskillpara" cols="90" rows="2">'.htmlspecialchars($csp).'</textarea>（JSON格式，留空表示不修改）</td></tr>';
	return $t;
}
