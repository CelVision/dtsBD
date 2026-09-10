<?php
/**
 * 一次性脚本：把 mapitemresource_1.php 的物品数据合入 mapresource_1.php
 * 每个地图 branch 下加 'item' => Array(...) 和 'npc' => Array()
 * map_id 99（全图随机池）作为特殊条目追加到 $maps 末尾
 *
 * 用法：php merge_mapitem.php
 * 输出：gamedata/cache/mapresource_merged.php（人工检查后替换 mapresource_1.php）
 */

$root = __DIR__ . '/';
$src = $root . 'gamedata/cache/mapitemresource_1.php';
$mapsrc = $root . 'gamedata/cache/mapresource_1.php';
$dst = $root . 'gamedata/cache/mapresource_merged.php';

// 1. 加载物品数据
include $src; // 得到 $mapitems[map_id][branch][] = Array(area,num,name,kind,eff,sta,skind)

// 2. 加载地图数据
include $mapsrc; // 得到 $maps[map_id][branch] = Array(plsinfo,...)

// 3. 把物品数据转为 PHP Array 字符串
function item_arr_str($items) {
    $out = "Array(\n";
    foreach($items as $it) {
        list($iarea, $inum, $iname, $ikind, $ieff, $ista, $iskind) = $it;
        // 所有字段保持字符串类型，与原CSV解析行为一致
        $iarea = str_replace("'", "\\'", $iarea);
        $inum = str_replace("'", "\\'", $inum);
        $iname = str_replace("'", "\\'", $iname);
        $ikind = str_replace("'", "\\'", $ikind);
        $ieff = str_replace("'", "\\'", $ieff);
        $ista = str_replace("'", "\\'", $ista);
        $iskind = str_replace("'", "\\'", $iskind);
        $out .= "\t\t\tArray('$iarea', '$inum', '$iname', '$ikind', '$ieff', '$ista', '$iskind'),\n";
    }
    $out .= "\t\t)";
    return $out;
}

// 4. 读 mapresource_1.php 原文，在每个 branch 的 'isindoor' => 'x', 行后插入 item/npc 字段
$content = file_get_contents($mapsrc);
if($content === false) die("无法读取 $mapsrc\n");

// 逐行处理
$lines = explode("\n", $content);
$outlines = array();
$inserted_count = 0;
$current_map = null;
$current_branch = null;

foreach($lines as $idx => $line) {
    $outlines[] = $line;
    
    // 检测地图ID行（小写array），如 "    0 => array(" 或 "    4=> array("
    if(preg_match('/^\s+(\d+)\s*=>\s*array\(/', $line, $m)) {
        $current_map = (int)$m[1];
        continue;
    }
    // 检测分支行（大写Array），如 "      0=>Array(" 或 "      1 => Array("
    if($current_map !== null && preg_match('/^\s+(\d+)\s*=>\s*Array\(/', $line, $m)) {
        $current_branch = (int)$m[1];
        continue;
    }
    
    // 检测 isindoor 行（branch 数据的最后一个字段），在其后插入 item/npc
    if($current_map !== null && $current_branch !== null && preg_match('/^\s*[\'"]isindoor[\'"]\s*=>\s*[\'"]\d*[\'"]\s*,?\s*$/i', trim($line))) {
        $has_item = isset($mapitems[$current_map][$current_branch]) && !empty($mapitems[$current_map][$current_branch]);
        $item_str = $has_item ? item_arr_str($mapitems[$current_map][$current_branch]) : "Array()";
        
        $outlines[] = "\t\t'item' => $item_str,";
        $outlines[] = "\t\t'npc' => Array(),";
        $inserted_count++;
        $current_branch = null; // 一个 branch 处理完
    }
}

echo "已插入 item/npc 字段到 $inserted_count 个 branch\n";

// 5. 追加 map_id 99（全图随机池）
$pool_str = isset($mapitems[99][0]) ? item_arr_str($mapitems[99][0]) : "Array()";
$pool_99 = "\n// 全图随机物品池（原 mapitemresource map_id 99）\n  99 => array(\n    0 => Array(\n\t\t'item' => $pool_str,\n\t\t'npc' => Array(),\n    ),\n  ),\n);";

// 找到 $maps 数组的结束 ");" 并替换（避开PHP结束标记）
$out = implode("\n", $outlines);
$phpend = strpos($out, '?' . '>');
if($phpend !== false) {
    // 在PHP结束标记前找最后一个 ");"
    $pos = strrpos(substr($out, 0, $phpend), ');');
} else {
    $pos = strrpos($out, ');');
}
if($pos !== false) {
    $out = substr($out, 0, $pos) . ltrim($pool_99, "\n") . substr($out, $pos + 2);
}

file_put_contents($dst, $out);
echo "已输出 $dst\n";

// 6. 验证：加载合并后的文件，对比物品数量
$before_count = 0;
foreach($mapitems as $im => $brs) foreach($brs as $ib => $list) $before_count += count($list);
echo "原 mapitemresource 物品条目总数: $before_count\n";

unset($maps);
include $dst;
$after_count = 0;
foreach($maps as $im => $brs) foreach($brs as $ib => $br) if(isset($br['item'])) $after_count += count($br['item']);
echo "合并后 mapresource item 字段条目总数: $after_count\n";

if($before_count == $after_count) {
    echo "✓ 数量一致，迁移成功\n";
} else {
    echo "✗ 数量不一致！before=$before_count after=$after_count\n";
}

// 7. 逐条对比验证
$diff = 0;
foreach($mapitems as $im => $brs) {
    foreach($brs as $ib => $list) {
        $merged = isset($maps[$im][$ib]['item']) ? $maps[$im][$ib]['item'] : array();
        if(count($list) != count($merged)) {
            echo "  ✗ map $im branch $ib: 原" . count($list) . "条, 合并后" . count($merged) . "条\n";
            $diff++;
        } else {
            foreach($list as $k => $orig) {
                if($orig !== $merged[$k]) {
                    echo "  ✗ map $im branch $ib item $k 内容不一致\n";
                    echo "    原: " . implode(',', $orig) . "\n";
                    echo "    新: " . implode(',', $merged[$k]) . "\n";
                    $diff++;
                }
            }
        }
    }
}
echo $diff == 0 ? "✓ 全部条目逐条一致\n" : "✗ 有 $diff 处差异\n";
