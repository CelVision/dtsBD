<?php
if(!defined('IN_ADMIN')) {
	exit('Access Denied');
}
include_once GAME_ROOT.'./include/admin/cfgfile.func.php';

$cmd_info = '';
$resource_file = config('gameresource',$gamecfg);
include $resource_file;

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
			$ns = Array();
			foreach(explode(',', admin_cfg_decode($_POST['npcword'])) as $nv) {
				$nv = trim($nv);
				if($nv !== '') $ns[] = $nv;
			}
			if($ns != $b['npc']) {
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
			adminlog('resourcemng', $smid.'-'.$sbi, $gamecfg);
			$cmd_info = "编号 {$smid}-{$sbi} 修改 {$chg} 处并已写入配置文件。";
		} else {
			$cmd_info = "未检测到编号 {$smid}-{$sbi} 的有效修改。";
		}
		$expanded_mid = $smid;
		$expanded_bi = $sbi;
		$expanded_page = 0;
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
			$r['f_npcword'] = htmlspecialchars(implode(',', isset($branch['npc']) ? $branch['npc'] : Array()));
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
include template('admin_resourcemng');
