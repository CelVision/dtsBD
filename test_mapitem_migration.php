<?php
/**
 * 功能测试：对比新旧数据源在 mode&16 物品刷新逻辑下的结果
 * 模拟 system.func.php 的刷新遍历，验证合入 mapresource 后产出一致
 */

// 模拟刷新
define('IN_GAME', TRUE);

$old_mapitems = array();
$maps = array();

// 旧数据源
include 'gamedata/cache/mapitemresource_1.php'; // $mapitems
$old_mapitems = $mapitems;
unset($mapitems, $mapitems_str);

// 新数据源
include 'gamedata/cache/mapresource_1.php'; // $maps

// 模拟 $mapid（0/1随机）和各禁区阶段
mt_srand(42); // 固定种子保证可比

function simulate_refresh(&$items_src, $is_new, $mapid, $an) {
    $result = array(
        'items' => array(),
        'traps' => array(),
    );
    $plsnum = 35;
    for($imap = 0; $imap < $plsnum; $imap++) {
        $ibranch = isset($mapid[$imap]) ? $mapid[$imap] : 0;
        if($is_new) {
            if(!isset($items_src[$imap][$ibranch]['item'])) continue;
            $itemlist = $items_src[$imap][$ibranch]['item'];
        } else {
            if(!isset($items_src[$imap][$ibranch])) continue;
            $itemlist = $items_src[$imap][$ibranch];
        }
        foreach($itemlist as $item) {
            list($iarea,$inum,$iname,$ikind,$ieff,$ista,$iskind) = $item;
            if(($iarea == $an)||($iarea == 99)) {
                if($iname == '煤气罐' && $imap == 0) continue; // 模拟nocoal跳过逻辑不重要，两边一致
                for($j = $inum; $j>0; $j--) {
                    $entry = "$iname|$ikind|$ieff|$ista|$iskind|$imap";
                    if(strpos($ikind,'TO')===0){
                        $result['traps'][] = $entry;
                    }else{
                        $result['items'][] = $entry;
                    }
                }
            }
        }
    }
    // 全图随机池 imap=99（随机部分跳过，仅比较存在性和数量）
    if($is_new) {
        if(isset($items_src[99][0]['item'])) {
            foreach($items_src[99][0]['item'] as $item) {
                list($iarea,$inum,$iname,$ikind,$ieff,$ista,$iskind) = $item;
                if(($iarea == $an)||($iarea == 99)) {
                    for($j = $inum; $j>0; $j--) {
                        $result['items'][] = "$iname|$ikind|$ieff|$ista|$iskind|POOL";
                    }
                }
            }
        }
    } else {
        if(isset($items_src[99][0])) {
            foreach($items_src[99][0] as $item) {
                list($iarea,$inum,$iname,$ikind,$ieff,$ista,$iskind) = $item;
                if(($iarea == $an)||($iarea == 99)) {
                    for($j = $inum; $j>0; $j--) {
                        $result['items'][] = "$iname|$ikind|$ieff|$ista|$iskind|POOL";
                    }
                }
            }
        }
    }
    return $result;
}

// 测试多种 mapid 组合和禁区阶段
$test_cases = array(
    'mapid_all_0_area_0' => array(array_fill(0, 35, 0), 0),
    'mapid_all_0_area_1' => array(array_fill(0, 35, 0), 1),
    'mapid_all_0_area_2' => array(array_fill(0, 35, 0), 2),
    'mapid_all_0_area_99s' => array(array_fill(0, 35, 0), 99),
    'mapid_all_1_area_0' => array(array_fill(0, 35, 1), 0),
    'mapid_all_1_area_1' => array(array_fill(0, 35, 1), 1),
    'mapid_mixed_area_1' => array(array_merge(array(0), array_fill(1, 16, 0), array_fill(17, 16, 1)), 1),
);

$all_pass = true;
foreach($test_cases as $name => $case) {
    list($mapid, $an) = $case;
    $old_result = simulate_refresh($old_mapitems, false, $mapid, $an);
    $new_result = simulate_refresh($maps, true, $mapid, $an);
    
    $old_items = implode(';', $old_result['items']);
    $new_items = implode(';', $new_result['items']);
    $old_traps = implode(';', $old_result['traps']);
    $new_traps = implode(';', $new_result['traps']);
    
    if($old_items === $new_items && $old_traps === $new_traps) {
        echo "PASS $name: items=" . count($old_result['items']) . " traps=" . count($old_result['traps']) . "\n";
    } else {
        echo "FAIL $name\n";
        if($old_items !== $new_items) {
            echo "  items差异: old " . count($old_result['items']) . " vs new " . count($new_result['items']) . "\n";
            $old_arr = explode(';', $old_items);
            $new_arr = explode(';', $new_items);
            $diff = array_diff($old_arr, $new_arr);
            foreach(array_slice($diff, 0, 3) as $d) echo "  仅旧有: $d\n";
            $diff2 = array_diff($new_arr, $old_arr);
            foreach(array_slice($diff2, 0, 3) as $d) echo "  仅新有: $d\n";
        }
        if($old_traps !== $new_traps) {
            echo "  traps差异: old " . count($old_result['traps']) . " vs new " . count($new_result['traps']) . "\n";
        }
        $all_pass = false;
    }
}

// 测试 get_item_place 的查找逻辑（不含商店/合成部分）
function test_item_place(&$src, $is_new, $target) {
    $found = array();
    foreach($src as $imap => $branches) {
        foreach($branches as $ibranch => $branch) {
            $itemlist = $is_new ? (isset($branch['item']) ? $branch['item'] : array()) : $branch;
            foreach($itemlist as $item) {
                list($iarea,$inum,$iname) = $item;
                if($iname == $target) {
                    $found[] = "$iarea,$imap,$ibranch,$inum";
                }
            }
        }
    }
    sort($found);
    return implode(';', $found);
}

$targets = array('煤气罐', '面包', '【最终机枪防线】', '伏特加', '疗伤药', '★蔷薇水晶★');
foreach($targets as $t) {
    $old_found = test_item_place($old_mapitems, false, $t);
    $new_found = test_item_place($maps, true, $t);
    if($old_found === $new_found) {
        echo "PASS item_place '$t': " . count(explode(';', $old_found)) . " 处\n";
    } else {
        echo "FAIL item_place '$t'\n  old: $old_found\n  new: $new_found\n";
        $all_pass = false;
    }
}

echo $all_pass ? "\n=== 全部测试通过 ===\n" : "\n=== 存在失败 ===\n";
