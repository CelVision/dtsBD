<?php
if(!defined('IN_ADMIN')) {
	exit('Access Denied');
}
include_once GAME_ROOT.'./include/admin/cfgfile.func.php';

$cmd_info = '';
$dict_file = config('npcdict',$gamecfg);
include $dict_file;

$expanded_type = -1;
$expanded_name = '';

$npc_int_fields = array_merge(
	Array('icon','club','lvl','mhp','msp','att','def','money','rage','pose','tactic','skills','skill','rp','state','mode','help_count','killnum','wp','wk','wg','wc','wd','wf',
		'wepe','weps','arbe','arbs','arhe','arhs','arae','aras','arfe','arfs','arte','arts'),
	admin_cfg_num_fields('itme'),
	admin_cfg_num_fields('itms')
);
$npc_str_fields = array_merge(
	Array('gd','inf','teamID','teamPass','wep','wepk','wepsk','arb','arbk','arbsk','arh','arhk','arhsk','ara','arak','arask','arf','arfk','arfsk','art','artk','artsk'),
	admin_cfg_num_fields('itm'),
	admin_cfg_num_fields('itmk'),
	admin_cfg_num_fields('itmsk')
);

if(strpos($command,'expand_') === 0) {
	$cparts = explode('_', $command);
	$rn = isset($cparts[1]) ? intval($cparts[1]) : -1;
	$rtype = isset($_POST["rtype_$rn"]) ? intval($_POST["rtype_$rn"]) : -1;
	$rname = isset($_POST["rname_$rn"]) ? trim($_POST["rname_$rn"]) : '';
	$rname = admin_cfg_decode($rname);
	if($rname !== '' && isset($npcdict[$rtype][$rname])) {
		$expanded_type = $rtype;
		$expanded_name = $rname;
	} else {
		$cmd_info = 'NPC条目不存在。';
	}
} elseif($command == 'submit') {
	$stype = isset($_POST['typeId']) ? intval($_POST['typeId']) : -1;
	$sname = isset($_POST['npcname']) ? admin_cfg_decode(trim($_POST['npcname'])) : '';
	if($sname === '' || !isset($npcdict[$stype][$sname])) {
		$cmd_info = '提交的NPC条目不存在，未修改。';
	} else {
		$chg = 0;
		$d = &$npcdict[$stype][$sname];
		foreach($npc_int_fields as $k) {
			if(isset($_POST[$k]) && array_key_exists($k,$d)) {
				$new = intval(trim($_POST[$k]));
				if($new != $d[$k]) {
					$d[$k] = admin_cfg_typed($d[$k], $new);
					$chg++;
				}
			}
		}
		foreach($npc_str_fields as $k) {
			if(isset($_POST[$k]) && array_key_exists($k,$d)) {
				$new = trim(admin_cfg_decode($_POST[$k]));
				if($new !== (string)$d[$k]) {
					$d[$k] = admin_cfg_typed($d[$k], $new);
					$chg++;
				}
			}
		}
		if(isset($_POST['description']) && array_key_exists('description',$d)) {
			$new = admin_cfg_decode($_POST['description']);
			if($new !== $d['description']) {
				$d['description'] = $new;
				$chg++;
			}
		}
		if(isset($_POST['str_clubskillpara']) && trim($_POST['str_clubskillpara']) !== '') {
			$json = admin_cfg_decode($_POST['str_clubskillpara']);
			$arr = json_decode($json, true);
			if(is_array($arr)) {
				if($arr != $d['clubskillpara']) {
					$d['clubskillpara'] = $arr;
					$chg++;
				}
			} else {
				$cmd_info .= 'clubskillpara JSON解析失败，该字段未修改。<br>';
			}
		}
		unset($d);
		if($chg) {
			regenerate_npcdict_file($dict_file, $npcdict, $npc_evolve);
			adminlog('npcdictmng', $stype.'/'.$sname, $gamecfg);
			$cmd_info .= "NPC [{$stype}] {$sname} 修改 {$chg} 处并已写入配置文件。";
		} else {
			$cmd_info .= "未检测到NPC [{$stype}] {$sname} 的有效修改。";
		}
		$expanded_type = $stype;
		$expanded_name = $sname;
	}
}

$npcdisplay = Array();
$rowidx = 0;
foreach($npcdict as $typeId => $group) {
	foreach($group as $nname => $ndata) {
		$r = Array();
		$r['type'] = $typeId;
		$r['name'] = $nname;
		$r['name_h'] = htmlspecialchars($nname);
		$r['source'] = isset($ndata['source']) ? $ndata['source'] : '';
		$r['lvl'] = isset($ndata['lvl']) ? $ndata['lvl'] : '';
		$r['mhp'] = isset($ndata['mhp']) ? $ndata['mhp'] : '';
		$r['ad'] = (isset($ndata['att']) ? $ndata['att'] : '-').'/'.(isset($ndata['def']) ? $ndata['def'] : '-');
		$r['wep'] = isset($ndata['wep']) ? htmlspecialchars($ndata['wep']) : '';
		if($typeId == $expanded_type && $nname === $expanded_name) {
			$r['expanded'] = true;
			$r['btnword'] = '收起';
			$r['btncmd'] = 'list';
			$r['edittable'] = admin_npcdict_edit_table($ndata);
		} else {
			$r['expanded'] = false;
			$r['btnword'] = '展开';
			$r['btncmd'] = 'expand_'.$rowidx;
		}
		$npcdisplay[] = $r;
		$rowidx++;
	}
}
include template('admin_npcdictmng');
