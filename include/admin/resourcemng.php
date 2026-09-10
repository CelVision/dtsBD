<?php
if(!defined('IN_ADMIN')) {
	exit('Access Denied');
}
include_once GAME_ROOT.'./include/admin/cfgfile.func.php';

$cmd_info = '';
// 编辑目标选择：默认跟随当前生效配置（房间成员命中自己的私有副本）；
// rescfg显式指定——mode_N=模式主文件（force绕过房间副本替换）/room_N=房间私有副本（不存在则提示）
$rescfg = isset($_GET['rescfg']) ? $_GET['rescfg'] : '';
$resource_file = '';
if(preg_match('/^room_(\d+)$/', $rescfg, $rm)) {
	$resource_file = GAME_ROOT.'./gamedata/cache/gameresource_room_'.(int)$rm[1].'.php';
	if(!file_exists($resource_file)) {
		$cmd_info = '房间 '.(int)$rm[1].' 的私有resource副本不存在（房间未建或已关闭），已回退模式1主文件。';
		$resource_file = '';
	}
} elseif(preg_match('/^mode_(\d+)$/', $rescfg, $mm)) {
	$resource_file = config('gameresource', (int)$mm[1], true);
}
if(empty($resource_file)) $resource_file = config('gameresource', $gamecfg);
include $resource_file;
$resource_short = basename($resource_file);

// picker选项：模式主文件+现存房间副本（glob）
$room_res_opts = Array();
foreach(glob(GAME_ROOT.'./gamedata/cache/gameresource_room_*.php') as $rf) {
	if(preg_match('/gameresource_room_(\d+)\.php$/', $rf, $rm)) {
		$rid = (int)$rm[1];
		$room_res_opts[] = Array('val' => 'room_'.$rid, 'label' => '房间 '.$rid.' 私有副本', 'sel' => ($rescfg == 'room_'.$rid ? ' selected' : ''));
	}
}

$expanded_mid = -1;
$expanded_bi = -1;
$expanded_page = 0;
$pagelen = 100;
$mapitem_cols = Array('area','num','name','kind','eff','sta','sk');

if(strpos($command,'expand_') === 0) {
	$cparts = explode('_', $command);
	$tmid = isset($cparts[1]) ? intval($cparts[1]) : -1;
	$tbi = isset($cparts[2]) ? intval($cparts[2]) : -1;
	$tpage = isset($cparts[3]) ? intval($cparts[3]) : 0;
	if($tpage < 0) $tpage = 0;
	if(isset($maps[$tmid][$tbi])) {
		$expanded_mid = $tmid;
		$expanded_bi = $tbi;
		$expanded_page = $tpage;
	} else {
		$cmd_info = '地图分支不存在。';
	}
} elseif($command == 'submit') {
	$smid = isset($_POST['mid']) ? intval($_POST['mid']) : -1;
	$sbi = isset($_POST['bi']) ? intval($_POST['bi']) : -1;
	if(!isset($maps[$smid][$sbi])) {
		$cmd_info = '提交的地图分支不存在，未修改。';
	} else {
		$chg = 0;
		$b = &$maps[$smid][$sbi];
		foreach(Array('plsinfo','xyinfo','bg','areainfo') as $k) {
			if(isset($_POST[$k]) && array_key_exists($k,$b)) {
				$new = trim(admin_cfg_decode($_POST[$k]));
				if($new != $b[$k]) {
					$b[$k] = admin_cfg_typed($b[$k], $new);
					$chg++;
				}
			}
		}
		if(isset($_POST['isindoor']) && array_key_exists('isindoor',$b)) {
			$new = $_POST['isindoor'] == '1' ? '1' : '0';
			if($new != $b['isindoor']) {
				$b['isindoor'] = admin_cfg_typed($b['isindoor'], $new);
				$chg++;
			}
		}
		if(isset($_POST['events']) && array_key_exists('events',$b)) {
			$evs = Array();
			foreach(explode(',', admin_cfg_decode($_POST['events'])) as $ev) {
				$ev = trim($ev);
				if($ev !== '') $evs[] = $ev;
			}
			if($evs != $b['events']) {
				$nev = Array();
				$i = 0;
				foreach($evs as $ev) {
					$o = isset($b['events'][$i]) ? $b['events'][$i] : '';
					$nev[] = is_int($o) ? intval($ev) : (string)$ev;
					$i++;
				}
				$b['events'] = $nev;
				$chg++;
			}
		}
		if(isset($_POST['npcword']) && array_key_exists('npc',$b)) {
			// 语法：纯typeId=扁平引用列表（现状兼容）；typeId:数量=图级固定刷新（结构化typeId=>num）；两种不可混合
			$ns = Array(); $pairs = Array(); $haspair = false; $hasplain = false;
			$badnpc = false;
			foreach(explode(',', admin_cfg_decode($_POST['npcword'])) as $nv) {
				$nv = trim($nv);
				if($nv === '') continue;
				if(preg_match('/^(\d+):(\d+)$/', $nv, $pm)) {
					$haspair = true;
					$pairs[intval($pm[1])] = intval($pm[2]);
				} elseif(preg_match('/^\d+$/', $nv)) {
					$hasplain = true;
					$ns[] = $nv;
				} else {
					$badnpc = true;
					break;
				}
			}
			if($badnpc || ($haspair && $hasplain)) {
				// 格式非法（非数字项或扁平/结构化混输）：拒绝修改本字段，保留原值
				$npcwarn = true;
			} elseif($haspair) {
				if($pairs != $b['npc']) {
					$b['npc'] = $pairs;
					$chg++;
				}
			} elseif($ns != $b['npc']) {
				$nn = Array();
				$i = 0;
				foreach($ns as $nv) {
					$o = isset($b['npc'][$i]) ? $b['npc'][$i] : 0;
					$nn[] = is_int($o) ? intval($nv) : (string)$nv;
					$i++;
				}
				$b['npc'] = $nn;
				$chg++;
			}
		}
		// 地图特性flags（逗号分隔；可新增也可清空，不要求原键存在）
		if(isset($_POST['flagsword'])) {
			$fls = Array();
			foreach(explode(',', admin_cfg_decode($_POST['flagsword'])) as $fl) {
				$fl = trim($fl);
				if($fl !== '') $fls[] = (string)$fl;
			}
			$ofl = isset($b['flags']) ? $b['flags'] : Array();
			if($fls != $ofl) {
				$b['flags'] = $fls;
				$chg++;
			}
		}
		// 地图分类tag（单值下拉：growth=一类发育/equip=二类装备/shop=商店/seed=种火；空=未分类；非法值拒收）
		// 同一图各分支应标同一类（轮换不改变地图用途）；用于快速模式按类抽图
		if(isset($_POST['tagword'])) {
			$tv = trim(admin_cfg_decode($_POST['tagword']));
			if(!in_array($tv, Array('growth','equip','shop','seed'), true)) $tv = '';
			$otv = isset($b['tag']) ? $b['tag'] : '';
			if($tv !== $otv) {
				if($tv === '') unset($b['tag']);
				else $b['tag'] = $tv;
				$chg++;
			}
		}
		// 物品行合并语义：只有POST中出现的行号才参与修改/删除，未提交的行（其他分页）保持原样
		$origitems = $b['item'];
		$origcnt = count($origitems);
		$itemcount = isset($_POST['itemcount']) ? intval($_POST['itemcount']) : $origcnt;
		if($itemcount < $origcnt) $itemcount = $origcnt;
		if($itemcount > $origcnt + 500) $itemcount = $origcnt + 500;
		$newitems = Array();
		for($i = 0; $i < $itemcount; $i++) {
			$delrow = !empty($_POST["itemdel_$i"]);
			$has = false;
			$row = Array();
			foreach($mapitem_cols as $c) {
				if(isset($_POST["item_{$i}_{$c}"])) {
					$has = true;
					$row[$c] = trim(admin_cfg_decode($_POST["item_{$i}_{$c}"]));
				} else {
					$row[$c] = '';
				}
			}
			if($i >= $origcnt && !$has && !$delrow) continue;
			if($delrow) {
				if($i < $origcnt) $chg++;
				continue;
			}
			if(!$has) {
				if($i < $origcnt) $newitems[] = $origitems[$i];
				continue;
			}
			if($row['name'] === '' && $row['kind'] === '') {
				if($i < $origcnt) $chg++;
				continue;
			}
			if($i < $origcnt) {
				$o = $origitems[$i];
				$same = true;
				foreach($mapitem_cols as $ci => $c) {
					if(!isset($o[$ci]) || $row[$c] != $o[$ci]) { $same = false; break; }
				}
				if($same) { $newitems[] = $o; continue; }
				$nr = Array();
				foreach($mapitem_cols as $ci => $c) {
					$nr[] = isset($o[$ci]) ? admin_cfg_typed($o[$ci], $row[$c]) : (string)$row[$c];
				}
				$newitems[] = $nr;
				$chg++;
			} else {
				$newitems[] = Array($row['area'],$row['num'],$row['name'],$row['kind'],$row['eff'],$row['sta'],$row['sk']);
				$chg++;
			}
		}
		$b['item'] = $newitems;
		unset($b);
		if($chg) {
			regenerate_gameresource_file($resource_file, $maps, $npc_spawn_config, $npc_sub_pls);
			adminlog('resourcemng', $smid.'-'.$sbi, $resource_short);
			$cmd_info = "编号 {$smid}-{$sbi} 修改 {$chg} 处并已写入配置文件。";
		} else {
			$cmd_info = "未检测到编号 {$smid}-{$sbi} 的有效修改。";
		}
		if(!empty($npcwarn)) $cmd_info .= ' 但NPC类别格式错误（需全部为typeId或全部为typeId:数量，不可混合），该项未保存。';
		$expanded_mid = $smid;
		$expanded_bi = $sbi;
		$expanded_page = 0;
	}
} elseif($command == 'savetags') {
	// 批量打标（图级）：POST tagmap[mid]一次性写入该图全部分支，同图同类约定强制满足
	$chg = 0;
	if(isset($_POST['tagmap']) && is_array($_POST['tagmap'])) {
		foreach($_POST['tagmap'] as $tmid => $tv) {
			$tmid = intval($tmid);
			if($tmid == 99 || !isset($maps[$tmid])) continue;
			$tv = trim(admin_cfg_decode($tv));
			if(!in_array($tv, Array('growth','equip','shop','seed'), true)) $tv = '';
			foreach($maps[$tmid] as $bi => $b) {
				$otv = isset($b['tag']) ? $b['tag'] : '';
				if($tv !== $otv) {
					if($tv === '') unset($maps[$tmid][$bi]['tag']);
					else $maps[$tmid][$bi]['tag'] = $tv;
					$chg++;
				}
			}
		}
	}
	if($chg) {
		regenerate_gameresource_file($resource_file, $maps, $npc_spawn_config, $npc_sub_pls);
		adminlog('resourcemng', 'savetags', $resource_short);
		$cmd_info = "批量打标完成：共修改 {$chg} 处分支tag，已写入配置文件。";
	} else {
		$cmd_info = '批量打标：未检测到任何修改。';
	}
} elseif($command == 'restore') {
	$bakfile = $resource_file.'.bak';
	if(file_exists($bakfile)) {
		copy($bakfile, $resource_file);
		adminlog('resourcemng', 'restore', $resource_short);
		$cmd_info = '已从备份恢复配置文件（撤销最近一次保存）。';
		include $resource_file;
	} else {
		$cmd_info = '备份文件不存在（保存过一次后才会生成备份）。';
	}
}

// 全图随机池（99不是地图，单独处理）
$randpool = Array(
	'has' => false, 'mid' => 99, 'bi' => 0,
	'itemcnt' => 0, 'npclist' => '',
	'btnword' => '展开', 'btncmd' => 'expand_99_0', 'expanded' => false,
);

$mapdisplay = Array();
foreach($maps as $mid => $branches) {
	if($mid == 99) {
		$bi = key($branches);
		$branch = reset($branches);
		$randpool['has'] = true;
		$randpool['bi'] = $bi;
		$randpool['itemcnt'] = count($branch['item']);
		$randpool['npclist'] = implode(',', isset($branch['npc']) ? $branch['npc'] : Array());
		$randpool['btncmd'] = 'expand_99_'.$bi;
		if($expanded_mid == 99 && $expanded_bi == $bi) {
			$randpool['expanded'] = true;
			$randpool['btnword'] = '收起';
			$randpool['btncmd'] = 'list';
			$randpool['f_npcword'] = htmlspecialchars(implode(',', $branch['npc']));
			$randpool['itemcount'] = $randpool['itemcnt'];
			$randpool['pagebtns'] = admin_cfg_pagebtns(99, $bi, $expanded_page, $randpool['itemcnt'], $pagelen);
			$slice = array_slice($branch['item'], $expanded_page * $pagelen, $pagelen);
			list($randpool['itemrows'], $shown) = admin_map_itemrows($slice, $mapitem_cols, $expanded_page * $pagelen);
		}
		continue;
	}
	foreach($branches as $bi => $branch) {
		$r = Array();
		$r['mid'] = $mid;
		$r['bi'] = $bi;
		$r['id'] = $mid.'-'.$bi;
		$r['name'] = (isset($branch['plsinfo']) && $branch['plsinfo'] !== '') ? $branch['plsinfo'] : '（待补充）';
		$r['xy'] = htmlspecialchars(isset($branch['xyinfo']) ? $branch['xyinfo'] : '');
		$r['indoor'] = (isset($branch['isindoor']) && $branch['isindoor'] == '1') ? '室外' : '室内';
		$r['itemcnt'] = count($branch['item']);
		$r['npclist'] = implode(',', $branch['npc']);
		if($mid == $expanded_mid && $bi == $expanded_bi) {
			$r['expanded'] = true;
			$r['btnword'] = '收起';
			$r['btncmd'] = 'list';
			$r['f_plsinfo'] = htmlspecialchars(isset($branch['plsinfo']) ? $branch['plsinfo'] : '');
			$r['f_xyinfo'] = htmlspecialchars(isset($branch['xyinfo']) ? $branch['xyinfo'] : '');
			$r['f_bg'] = htmlspecialchars(isset($branch['bg']) ? $branch['bg'] : '');
			$r['f_areainfo'] = htmlspecialchars(isset($branch['areainfo']) ? $branch['areainfo'] : '');
			$r['f_eventsword'] = htmlspecialchars(implode(',', isset($branch['events']) ? $branch['events'] : Array()));
			$r['f_flagsword'] = htmlspecialchars(implode(',', isset($branch['flags']) ? $branch['flags'] : Array()));
			$r['f_tagword'] = isset($branch['tag']) ? $branch['tag'] : '';
			$r['tagsel_growth'] = $r['f_tagword'] === 'growth' ? ' selected' : '';
			$r['tagsel_equip'] = $r['f_tagword'] === 'equip' ? ' selected' : '';
			$r['tagsel_shop'] = $r['f_tagword'] === 'shop' ? ' selected' : '';
			$r['tagsel_seed'] = $r['f_tagword'] === 'seed' ? ' selected' : '';
			// npc字段双形态：结构化 typeId=>num 渲染为 "typeId:num"；扁平 typeId 渲染为 "typeId"
			$narr = isset($branch['npc']) ? $branch['npc'] : Array();
			$nkeys = array_keys($narr);
			if(!empty($nkeys) && $nkeys !== range(0, count($nkeys) - 1)) {
				$nw = Array();
				foreach($narr as $nt => $nn) $nw[] = $nt . ':' . $nn;
				$r['f_npcword'] = htmlspecialchars(implode(',', $nw));
			} else {
				$r['f_npcword'] = htmlspecialchars(implode(',', $narr));
			}
			$r['sel0'] = (isset($branch['isindoor']) && $branch['isindoor'] == '0') ? ' selected' : '';
			$r['sel1'] = (isset($branch['isindoor']) && $branch['isindoor'] == '1') ? ' selected' : '';
			$r['itemcount'] = $r['itemcnt'];
			$r['pagebtns'] = admin_cfg_pagebtns($mid, $bi, $expanded_page, $r['itemcnt'], $pagelen);
			$slice = array_slice($branch['item'], $expanded_page * $pagelen, $pagelen);
			list($r['itemrows'], $shown) = admin_map_itemrows($slice, $mapitem_cols, $expanded_page * $pagelen);
		} else {
			$r['expanded'] = false;
			$r['btnword'] = '展开';
			$r['btncmd'] = 'expand_'.$mid.'_'.$bi;
		}
		$mapdisplay[] = $r;
	}
}
// 批量打标区数据（图级：一行一图，保存时写入该图全部分支；按两列预分组渲染）
$tagquick = Array();
foreach($maps as $mid => $branches) {
	if($mid == 99) continue;
	$tqname = '';
	$tqtag = '';
	foreach($branches as $b) {
		if($tqname === '' && !empty($b['plsinfo'])) $tqname = $b['plsinfo'];
		if($tqtag === '' && isset($b['tag'])) $tqtag = $b['tag'];
	}
	$tagquick[] = Array(
		'mid' => $mid,
		'name' => $tqname !== '' ? $tqname : '（待补充）',
		'sel_growth' => $tqtag === 'growth' ? ' selected' : '',
		'sel_equip' => $tqtag === 'equip' ? ' selected' : '',
		'sel_shop' => $tqtag === 'shop' ? ' selected' : '',
		'sel_seed' => $tqtag === 'seed' ? ' selected' : '',
	);
}
$tagq_l = array_slice($tagquick, 0, ceil(count($tagquick) / 2));
$tagq_r = array_slice($tagquick, ceil(count($tagquick) / 2));

include template('admin_resourcemng');
