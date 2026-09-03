<?php
if(!defined('IN_GAME')) exit('Access Denied');

// NPC辞典化统一spawn系统
// 本文件实现新函数，与旧系统并行运行，完成测试后替换旧系统
//
// 索引方式：spawn_npc($typeId, $npcName, $num, ...)
// 数据来源：$npcdict (npcdict_1.php) + $npc_evolve (进化关系映射)
// 刷新配置：$npc_spawn_config / $npc_sub_pls 迁至 gameresource（gamedata/cache/gameresource_1.php 文末）
// 地图分支的 'npc' 字段为该地初始固定刷新的 typeId 引用（指向本辞典）；99段为全图随机池

// 懒加载刷新配置（数据在 gameresource；任意调用上下文可用）
function load_npc_spawn_data() {
	if(isset($GLOBALS['npc_spawn_config']) && isset($GLOBALS['npc_sub_pls'])) return;
	global $gamecfg;
	// gameresource 同时定义 $maps / $npc_spawn_config / $npc_sub_pls
	include config('gameresource', $gamecfg);
	$GLOBALS['npc_spawn_config'] = $npc_spawn_config;
	$GLOBALS['npc_sub_pls'] = $npc_sub_pls;
	if(!isset($GLOBALS['maps'])) $GLOBALS['maps'] = $maps;
}

// 获取typeId的spawn配置
function get_npc_spawn_config($type, $mode = 'init') {
	load_npc_spawn_data();
	if(isset($GLOBALS['npc_spawn_config'][$mode][$type])) {
		return $GLOBALS['npc_spawn_config'][$mode][$type];
	}
	return array('num' => 0, 'pls' => 99, 'exclude' => array());
}

// 获取sub级别pls覆盖
function get_npc_sub_pls($type, $name) {
	load_npc_spawn_data();
	if(isset($GLOBALS['npc_sub_pls'][$type][$name])) {
		return $GLOBALS['npc_sub_pls'][$type][$name];
	}
	return null;
}

// 加载NPC辞典（单例缓存）
function get_npcdict() {
	global $gamecfg;
	static $dict = null;
	if($dict === null) {
		include config('npcdict', $gamecfg);
		$dict = array(
			'dict' => $npcdict,
			'evolve' => $npc_evolve,
		);
	}
	return $dict;
}

// 获取某个typeId下所有NPC名称列表
function get_npcdict_names($type) {
	$d = get_npcdict();
	if(!isset($d['dict'][$type])) return array();
	return array_keys($d['dict'][$type]);
}

// 获取单个NPC数据（已展平，无需array_merge）
function get_npcdict_data($type, $name) {
	$d = get_npcdict();
	if(!isset($d['dict'][$type][$name])) return null;
	return $d['dict'][$type][$name];
}

// 开局刷新池 — 还原原版 npc_1.php 语义：
// 同组内优先取 sub 来源（原 npc_1.php 条目）；纯 asub 组（如92种火）回退用全部 asub 条目；
// esub（进化目标）与同组内的 asub（addnpc专属召唤，如type1的强版红暮）不参与开局刷新
function get_npc_init_pool($type) {
	$d = get_npcdict();
	if(!isset($d['dict'][$type])) return array();
	$sub_names = $asub_names = array();
	foreach($d['dict'][$type] as $name => $data) {
		$src = isset($data['source']) ? $data['source'] : 'sub';
		if($src == 'sub') $sub_names[] = $name;
		elseif($src == 'asub') $asub_names[] = $name;
	}
	return !empty($sub_names) ? $sub_names : $asub_names;
}

// 统一spawn函数 — 替代 addnpc()
// 参数与 addnpc() 对齐，$sub 数字下标改为 $name 字符串
function spawn_npc($type, $name, $num = 1, $time = 0, $anpcdata = NULL, $pls_override = NULL) {
	global $now,$db,$gtablepre,$tablepre,$log,$mapinfo,$typeinfo,$arealist,$areanum,$gamecfg;
	global $hidding_typelist,$deepzones;
	include_once GAME_ROOT."./include/game/clubslct.func.php";

	$time = $time == 0 ? $now : $time;
	$plsnum = sizeof($mapinfo['plsinfo']);

	$npcdata = get_npcdict_data($type, $name);
	if(!$npcdata) {
		return;
	}

	$summon_ids = array();
	$namelist = array();

	for($i = 0; $i < $num; $i++) {
		$npc = $npcdata;
		$npc['type'] = $type;
		$npc['endtime'] = $time;
		$npc['exp'] = round(($npc['lvl'] * 2 + 1) * $GLOBALS['baseexp']);
		$npc['sNo'] = $i;
		$npc['hp'] = $npc['mhp'];
		$npc['sp'] = $npc['msp'];
		if(!isset($npc['state'])) $npc['state'] = 0;

		// 六系熟练度：未单独设置的用skill填充
		foreach(array('p','k','g','c','d','f') as $val) {
			if(!isset($npc['w'.$val]) || !$npc['w'.$val]) $npc['w'.$val] = isset($npc['skill']) ? $npc['skill'] : 0;
		}

		// 性别：r=随机
		if($npc['gd'] == 'r') $npc['gd'] = rand(0,1) ? 'm' : 'f';

		// 位置分配：优先级 pls_override > sub_pls配置 > spawn_config('add') > 默认99
		if($pls_override !== NULL) {
			$npc['pls'] = $pls_override;
		} else {
			$sub_pls = get_npc_sub_pls($type, $name);
			if($sub_pls !== null) {
				$npc['pls'] = $sub_pls[array_rand($sub_pls)];
			} else {
				$cfg = get_npc_spawn_config($type, 'add');
				$cfg_pls = $cfg['pls'];
				if(is_array($cfg_pls)) {
					$npc['pls'] = $cfg_pls[array_rand($cfg_pls)];
				} elseif($cfg_pls === 99 || $cfg_pls === null) {
					$areaarr = array_slice($arealist, $areanum + 1);
					if(empty($areaarr)) {
						$npc['pls'] = 0;
					} else {
						shuffle($areaarr);
						$npc['pls'] = $areaarr[0];
						if(in_array($npc['type'], $hidding_typelist)) {
							while(in_array($npc['pls'], $deepzones)) {
								shuffle($areaarr);
								$npc['pls'] = $areaarr[0];
							}
						}
					}
				} else {
					$npc['pls'] = $cfg_pls;
				}
			}
		}

		// 称号技能初始化
		if(!empty($npc['club'])) changeclub($npc['club'], $npc);
		// 自定义技能初始化
		if(!empty($npc['clubskill']) || !empty($npc['clubskillpara'])) customtclubskill($npc);

		// 自定义数据覆盖（与addnpc逻辑一致）
		if(!empty($anpcdata)) {
			foreach($anpcdata as $adkey => $advalue) {
				if(is_array($advalue)) continue;
				$npc[$adkey] = $advalue;
			}
			if(isset($anpcdata['clbstatus'])) {
				foreach(array('a','b','c','d','e') as $cbs) {
					if(isset($anpcdata['clbstatus'][$cbs])) $npc['clbstatus'.$cbs] = $anpcdata['clbstatus'][$cbs];
				}
			}
			if(isset($anpcdata['clbpara'])) {
				$npc['clbpara'] = is_array($npc['clbpara']) ? array_merge($npc['clbpara'], $anpcdata['clbpara']) : $anpcdata['clbpara'];
			}
		}

		// 格式化并写入DB
		$npc = player_format_with_db_structure($npc);
		$db->array_insert("{$tablepre}players", $npc);
		$summon_ids[] = $db->insert_id();

		$newsname = $typeinfo[$type] . ' ' . $npc['name'];
		if($num > 1) {
			$namelist[$newsname] += 1;
		} else {
			addnews($now, 'addnpc', $newsname);
		}
		unset($npc);
	}

	if($num > 1) {
		foreach($namelist as $aname => $anum) {
			addnews($now, 'addnpcs', $aname, $anum);
		}
	} else {
		return $summon_ids;
	}
	return;
}

// 随机spawn — 从某个typeId下随机选一个NPC生成
// 用于电掣召唤仪等随机召唤场景
function spawn_npc_random($type, $num = 1, $time = 0, $anpcdata = NULL, $pls_override = NULL) {
	$names = get_npcdict_names($type);
	if(empty($names)) return;
	$name = $names[array_rand($names)];
	return spawn_npc($type, $name, $num, $time, $anpcdata, $pls_override);
}

// 开局批量刷新 — 替代 rs_game() mode&8 的NPC初始化逻辑
// num/pls 从 gameresource 的 $npc_spawn_config['init'] 读取，sub级pls从 $npc_sub_pls 读取
function spawn_npc_all($time = 0) {
	global $now,$db,$gtablepre,$tablepre,$log,$mapinfo,$typeinfo,$arealist,$areanum,$gamecfg;
	global $hidding_typelist,$deepzones,$norandnpc_pls;
	include_once GAME_ROOT."./include/game/clubslct.func.php";

	$time = $time == 0 ? $now : $time;
	$plsnum = sizeof($mapinfo['plsinfo']);
	$d = get_npcdict();

	// 清空旧NPC
	$db->query("DELETE FROM {$tablepre}players WHERE type>0");


	foreach($d['dict'] as $type => $npcs) {
		// 开局刷新池（sub优先/纯asub组回退/排除esub进化目标，见 get_npc_init_pool）
		$names = get_npc_init_pool($type);
		// 从 spawn 配置读取 typeId 级别的 num 和 pls
		$cfg = get_npc_spawn_config($type, 'init');
		// 排除不参与开局刷新的NPC
		$exclude = isset($cfg['exclude']) ? $cfg['exclude'] : array();
		if(!empty($exclude)) {
			$names = array_diff($names, $exclude);
			$names = array_values($names);
		}
		$namecount = count($names);
		if($namecount == 0) continue;

		$num = $cfg['num'];
		$cfg_pls = $cfg['pls'];
		if($num <= 0) continue;

		// sub数量大于num时打乱顺序（与原逻辑一致）
		$shuffled = $names;
		if($namecount > $num) shuffle($shuffled);

		for($j = 1; $j <= $num; $j++) {
			// 轮询选择NPC
			$name = $shuffled[($j - 1) % $namecount];
			$npcdata = $npcs[$name];

			$npc = $npcdata;
			$npc['type'] = $type;
			$npc['endtime'] = $time;
			$npc['sNo'] = $j;
			$npc['hp'] = $npc['mhp'];
			$npc['sp'] = $npc['msp'];
			$npc['exp'] = round(2 * $npc['lvl'] * $GLOBALS['baseexp']);
			if(!isset($npc['state'])) $npc['state'] = 0;

			// 六系熟练度
			foreach(array('p','k','g','c','d','f') as $val) {
				if(!isset($npc['w'.$val]) || !$npc['w'.$val]) $npc['w'.$val] = isset($npc['skill']) ? $npc['skill'] : 0;
			}

			// 性别
			if($npc['gd'] == 'r') $npc['gd'] = rand(0,1) ? 'm' : 'f';

			// 称号技能
			if(!empty($npc['club'])) changeclub($npc['club'], $npc);
			if(!empty($npc['clubskill']) || !empty($npc['clubskillpara'])) customtclubskill($npc);

			// 位置：优先 sub_pls配置 > spawn_config('init') > 随机
			$sub_pls = get_npc_sub_pls($type, $name);
			if($sub_pls !== null) {
				$npc['pls'] = $sub_pls[array_rand($sub_pls)];
			} elseif(is_array($cfg_pls)) {
				$npc['pls'] = $cfg_pls[array_rand($cfg_pls)];
			} elseif($cfg_pls === 99 || $cfg_pls === null) {
				// 随机区域：排除norandom_npc地图（原：rand(1,..)隐性排0+写死排34）；躲避类NPC另避deepzone
				if(in_array($npc['type'], $hidding_typelist)) {
					do {
						$rpls = rand(0, $plsnum - 1);
					} while(in_array($rpls, $deepzones) || in_array($rpls, $norandnpc_pls));
				} else {
					do {
						$rpls = rand(0, $plsnum - 1);
					} while(in_array($rpls, $norandnpc_pls));
				}
				$npc['pls'] = $rpls;
			} else {
				$npc['pls'] = $cfg_pls;
			}

			$npc['state'] = 0;
			$npc = player_format_with_db_structure($npc);
			$db->array_insert("{$tablepre}players", $npc);
			unset($npc);
		}
	}
}

// NPC进化 — 替代 evonpc()
// 用 $npc_evolve 映射查进化目标，从辞典取进化后属性，UPDATE到DB
function evolve_npc($type, $name) {
	global $now,$db,$gtablepre,$tablepre,$log,$mapinfo,$typeinfo,$gamecfg;
	global $club_skillslist,$cskills;

	if(!$type || !$name) return false;

	$d = get_npcdict();
	$evolve_map = $d['evolve'];

	// 查进化目标
	if(!isset($evolve_map[$type][$name])) return false;
	$evo_name = $evolve_map[$type][$name];

	// 同名进化时用_evo后缀取进化后数据
	$lookup_name = ($evo_name == $name) ? $name . '_evo' : $evo_name;
	$evo_data = get_npcdict_data($type, $lookup_name);
	if(!$evo_data) {
		// 尝试不带后缀
		$evo_data = get_npcdict_data($type, $evo_name);
		if(!$evo_data) return false;
	}

	// 查DB中现有NPC
	$result = $db->query("SELECT * FROM {$tablepre}players WHERE type = '$type' AND name = '$name'");
	$num = $db->num_rows($result);
	if(!$num) return false;

	// 用进化后数据覆盖
	$npc = $evo_data;
	$npc['hp'] = $npc['mhp'];
	$npc['sp'] = $npc['msp'];
	$npc['exp'] = round(($npc['lvl'] * 2 + 1) * $GLOBALS['baseexp']);
	if(!isset($npc['state'])) $npc['state'] = 0;

	// 进化后熟练度：全部设为skill（与原evonpc一致）
	$npc['wp'] = $npc['wk'] = $npc['wg'] = $npc['wc'] = $npc['wd'] = $npc['wf'] = $npc['skill'];
	unset($npc['skill']);

	// 进化后技能初始化（与原evonpc一致）
	if(empty($npc['clbpara'])) $npc['clbpara']['skill'] = array();
	if(isset($club_skillslist[$npc['club']])) {
		$npc_csk = $club_skillslist[$npc['club']];
		foreach($npc_csk as $sk) getclubskill($sk, $npc['clbpara']);
	}
	if(!empty($npc['clubskill'])) {
		foreach($npc['clubskill'] as $sk) getclubskill($sk, $npc['clbpara']);
	}
	if(!empty($npc['clubskillpara'])) {
		foreach($npc['clubskillpara'] as $sk => $skarr) {
			foreach($skarr as $skpara => $skvalue) set_skillpara($sk, $skpara, $skvalue, $npc['clbpara']);
		}
	}
	unset($npc['clubskill']);
	unset($npc['clubskillpara']);

	$npc['clbpara'] = json_encode($npc['clbpara'], JSON_UNESCAPED_UNICODE);

	// 用DB结构过滤字段，避免非DB列（source/description/help_count等）
	$npc = player_format_with_db_structure($npc);

	// 构建UPDATE语句（排除pid等不应UPDATE的字段）
	$skip_keys = array_flip(array('pid','type','pass','ip','sNo','endtime','validtime','deathtime','cmdnum','cdsec','cdmsec','cdtime','action','validtime'));
	$qry = '';
	foreach($npc as $key => $val) {
		if(isset($skip_keys[$key])) continue;
		$qry .= "$key = '{$val}',";
	}
	if(!empty($qry)) {
		$qry = substr($qry, 0, -1);
		$db->query("UPDATE {$tablepre}players SET $qry WHERE type = '$type' AND name = '$name'");
	}

	return $npc;
}

// 旧函数兼容层 — 用旧参数(type,sub数字)调用新系统
// 便于渐进迁移：调用方暂不改参数，内部转换后走spawn_npc
function addnpc_compat($type, $sub, $num, $time = 0, $anpcdata = NULL, $pls_override = NULL) {
	$d = get_npcdict();
	if(!isset($d['dict'][$type])) return;
	// 仅取 source='asub' 的条目，与原 addnpc_1.php 的 sub 数组对齐
	$asub_names = array();
	foreach($d['dict'][$type] as $name => $data) {
		if(isset($data['source']) && $data['source'] === 'asub') $asub_names[] = $name;
	}
	if(!isset($asub_names[$sub])) return;
	return spawn_npc($type, $asub_names[$sub], $num, $time, $anpcdata, $pls_override);
}
