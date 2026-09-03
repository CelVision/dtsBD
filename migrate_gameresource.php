<?php
/**
 * gameresource 迁移：
 * 1. 每地图分支 npc 字段填入初始固定刷新的 typeId（引用npcdict辞典模板）
 * 2. 文件尾部追加 $npc_spawn_config / $npc_sub_pls（从npcdict.func.php迁入）
 * 3. 头部注释说明 npc 字段语义
 * 4. 特殊地图分支注入 'flags' 特性字段（防重跑丢失）
 */
$file = 'gamedata/cache/gameresource_1.php';
$lines = file($file, FILE_IGNORE_NEW_LINES);
if(!$lines) die("读取失败\n");

// ── 目标地图的 npc typeId（初始刷新；92的sub级位置见文末$npc_sub_pls）──
$mapnpc = array(
	0  => 'Array(1)',                    // 无月之影：红暮
	2  => 'Array(92)', 15 => 'Array(92)',// 覆唱的篝火
	3  => 'Array(92)', 22 => 'Array(92)',// 爱恋的埋火
	18 => 'Array(92)', 23 => 'Array(92)',// 怜悯的永火
	20 => 'Array(92)', 24 => 'Array(92)',// 执念的残火
	12 => 'Array(92)', 29 => 'Array(92)',// 希望的焰火
	32 => 'Array(88 => 4)',             // SCP研究设施：SCP生物图级固定刷新4只（结构化typeId=>num）
	34 => 'Array(20, 21, 22, 24, 26)',   // 英灵殿：天神/武神/巫师等
	99 => 'Array(14, 90, 91)',           // 全图随机池：女主/数据残影/数据残影-？？？
);

// ── 特殊地图分支特性（map 0/32/33/34；分支级差异由此实现）──
$mapflags = array(
	0  => "Array('deepzone', 'norandom_drop', 'norandom_npc', 'lockbranch')",
	32 => "Array('deepzone')",
	33 => "Array('deepzone', 'lockbranch')",
	34 => "Array('deepzone', 'norandom_drop', 'norandom_npc', 'noesc_tp', 'lockbranch')",
);
$flagsfilled = array();

$current = null;
$out = array();
$filled = array();
foreach($lines as $ln) {
	// 地图段落开始
	if(preg_match('/^\s+(\d+)\s*=>\s*array\(/', $ln, $m)) {
		$current = intval($m[1]);
	}
	// 99段注释更新
	if(strpos($ln, '全图随机物品池（原 mapitemresource map_id 99）') !== false) {
		$ln = str_replace('全图随机物品池（原 mapitemresource map_id 99）', '全图随机池（原 mapitemresource map_id 99；NPC随机刷新类别见npc字段）', $ln);
	}
	// npc字段填充
	if($current !== null && isset($mapnpc[$current]) && preg_match("/^(\s*)'npc'\s*=>\s*Array\(\),\s*$/", $ln, $m2)) {
		$ln = $m2[1] . "'npc' => " . $mapnpc[$current] . ",";
		$filled[$current] = isset($filled[$current]) ? $filled[$current] + 1 : 1;
	}
	// flags特性注入：目标地图首个 'item' 行前插入（该图已存在flags则跳过，防重复）
	if($current !== null && isset($mapflags[$current])) {
		if(strpos($ln, "'flags'") !== false) {
			$flagsfilled[$current] = 1;
		} elseif(empty($flagsfilled[$current]) && preg_match("/^(\s*)'item'\s*=>\s*Array\(/", $ln, $m3)) {
			$out[] = $m3[1] . "'flags' => " . $mapflags[$current] . ",";
			$flagsfilled[$current] = 1;
		}
	}
	// 尾部结束标记前插入刷新配置
	if(trim($ln) === '?>') {
		$out[] = '';
		$out[] = '// ── NPC刷新配置（从 include/game/npcdict.func.php 迁入）──';
		$out[] = '// init = 开局刷新(rs_game mode&8)，add = 动态召唤(addnpc)';
		$out[] = '// pls: 0=无月之影, 34=英灵殿, 99=随机, 具体数字=固定地点, array=多地择一, null=按sub配置';
		$out[] = "\$npc_spawn_config = array(";
		$out[] = "\t'init' => array(";
		$out[] = "\t\t1  => array('num' => 1,   'pls' => 0),";
		$out[] = "\t\t14 => array('num' => 3,   'pls' => 99),";
		$out[] = "\t\t15 => array('num' => 0,   'pls' => 99),  // 不刷新，仅addnpc";
		$out[] = "\t\t19 => array('num' => 0,   'pls' => 0),   // 不刷新，仅addnpc";
		$out[] = "\t\t20 => array('num' => 10,  'pls' => 34),";
		$out[] = "\t\t21 => array('num' => 5,   'pls' => 34),";
		$out[] = "\t\t22 => array('num' => 2,   'pls' => 34),";
		$out[] = "\t\t24 => array('num' => 3,   'pls' => 34),";
		$out[] = "\t\t26 => array('num' => 1,   'pls' => 34),";
		$out[] = "\t\t88 => array('num' => 4,   'pls' => 32),";
		$out[] = "\t\t90 => array('num' => 280, 'pls' => 99),";
		$out[] = "\t\t91 => array('num' => 1,   'pls' => 99),";
		$out[] = "\t\t92 => array('num' => 100, 'pls' => null, 'exclude' => array('✦真实的火种')), // sub有各自pls，✦真实的火种不参与开局刷新";
		$out[] = "\t),";
		$out[] = "\t'add' => array(";
		$out[] = "\t\t1  => array('num' => 1,   'pls' => 0),";
		$out[] = "\t\t2  => array('num' => 16,  'pls' => 99),";
		$out[] = "\t\t4  => array('num' => 1,   'pls' => 33),";
		$out[] = "\t\t5  => array('num' => 2,   'pls' => 99),";
		$out[] = "\t\t6  => array('num' => 1,   'pls' => 99),";
		$out[] = "\t\t7  => array('num' => 3,   'pls' => 99),";
		$out[] = "\t\t9  => array('num' => 1,   'pls' => 0),";
		$out[] = "\t\t11 => array('num' => 6,   'pls' => 99),";
		$out[] = "\t\t12 => array('num' => 1,   'pls' => 99),";
		$out[] = "\t\t13 => array('num' => 3,   'pls' => 99),";
		$out[] = "\t\t15 => array('num' => 1,   'pls' => 99),";
		$out[] = "\t\t19 => array('num' => 1,   'pls' => 0),";
		$out[] = "\t\t25 => array('num' => 0,   'pls' => 99),";
		$out[] = "\t\t89 => array('num' => 1,   'pls' => 99),";
		$out[] = "\t\t90 => array('num' => 1,   'pls' => 99),";
		$out[] = "\t\t99 => array('num' => 1,   'pls' => 99),  // 迷之搬运工：仅通过addnpc生成";
		$out[] = "\t\t92 => array('num' => 100, 'pls' => 99),";
		$out[] = "\t),";
		$out[] = ");";
		$out[] = '';
		$out[] = '// sub级别pls覆盖（typeId 92篝火：每个sub有固定刷新位置）';
		$out[] = "\$npc_sub_pls = array(";
		$out[] = "\t92 => array(";
		$out[] = "\t\t'✦覆唱的篝火' => array(2, 15),";
		$out[] = "\t\t'✦爱恋的埋火' => array(3, 22),";
		$out[] = "\t\t'✦怜悯的永火' => array(18, 23),";
		$out[] = "\t\t'✦执念的残火' => array(20, 24),";
		$out[] = "\t\t'✦希望的焰火' => array(12, 29),";
		$out[] = "\t),";
		$out[] = ");";
	}
	// 头部注释插入
	if(trim($ln) === '$maps = Array') {
		$out[] = '// gameresource：地图与资源总配置（原 mapresource 更名定版）';
		$out[] = "// 'npc' 字段：该地图分支初始固定刷新的NPC类别（typeId，指向npcdict辞典模板）";
		$out[] = "// map 99 的 'npc'：全图随机刷新池的NPC类别";
		$out[] = '// 完整刷新参数（num/pls/sub级位置）见文末 $npc_spawn_config / $npc_sub_pls';
	}
	$out[] = $ln;
}

file_put_contents($file, implode("\n", $out) . "\n");

// ── 校验 ──
echo "填充统计:\n";
foreach($filled as $mid => $cnt) echo "  map $mid: $cnt 处分支\n";
$missed = array_diff(array_keys($mapnpc), array_keys($filled));
if($missed) { echo "错误：未填充的地图 " . implode(',', $missed) . "\n"; exit(1); }
echo "flags注入统计: " . implode(',', array_keys($flagsfilled)) . "\n";
echo "全部目标地图已填充\n";
