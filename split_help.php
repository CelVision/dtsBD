<?php
/**
 * 一次性脚本：把 help.htm 拆分为 help_intro.htm / help_mix.htm / help_npc.htm
 * 行号基于 2026-09-02 版本，拆分前先校验锚点行内容
 */
$src = 'templates/default/help.htm';
$lines = file($src, FILE_IGNORE_NEW_LINES);
if(!$lines) die("读取失败\n");
echo "总行数: " . count($lines) . "\n";

// 校验关键锚点
$checks = array(
    112 => '{template skillhelp}',
    1898 => '<!--',
    2129 => '-->',
    2130 => '道具合成',
    2133 => '合成成功时',
    2333 => '特殊道具',
    2914 => 'NPC简介',
    2917 => '{template npchelp}',
    2920 => '物品掉落表',
    2929 => '游戏周边',
    3005 => '{template footer}',
);
foreach($checks as $ln => $needle) {
    $idx = $ln - 1;
    if($idx >= count($lines) || strpos($lines[$idx], $needle) === false) {
        die("行 $ln 校验失败，期望含 '$needle'，实际: " . (isset($lines[$idx]) ? $lines[$idx] : 'N/A') . "\n");
    }
}
echo "锚点校验通过\n";

// 公共头尾
$head = <<<EOT
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

{template header}


<div class="subtitle">%s</div>

<HR>
EOT;

$tail = "\n{template footer}\n";

// ── help_intro.htm: 1-1897 + 2333-2913 + 2929-3004 ──
$intro = array();
for($i = 0; $i < 1897; $i++) $intro[] = $lines[$i];          // 1-1897
for($i = 2332; $i < 2913; $i++) $intro[] = $lines[$i];       // 2333-2913
for($i = 2928; $i < 3004; $i++) $intro[] = $lines[$i];       // 2929-3004（不含footer行）
// 替换标题
$intro[11] = '<div class="subtitle">玩法介绍</div>';  // 第12行是subtitle（0-index 11）
// 菜单表中 道具合成/NPC简介/物品掉落表 改为指向子页面
foreach($intro as $k => $v) {
    if(strpos($v, 'help.php#道具合成') !== false) $intro[$k] = str_replace('help.php#道具合成', 'helpmix.php', $v);
    if(strpos($v, 'help.php#NPC简介') !== false) $intro[$k] = str_replace('help.php#NPC简介', 'helpnpc.php', $v);
    if(strpos($v, 'help.php#物品掉落表') !== false) $intro[$k] = str_replace('help.php#物品掉落表', 'maphelp.php', $v);
}
file_put_contents('templates/default/help_intro.htm', implode("\n", $intro) . $tail);
echo "help_intro.htm: " . count($intro) . " 行\n";

// ── help_mix.htm: 头 + 2130-2332 + 尾 ──
$mixhead = sprintf($head, '合成表一览');
$mix = array($mixhead);
for($i = 2129; $i < 2332; $i++) $mix[] = $lines[$i];         // 2130-2332
file_put_contents('templates/default/help_mix.htm', implode("\n", $mix) . $tail);
echo "help_mix.htm: " . count($mix) . " 行\n";

// ── help_npc.htm: 头 + 2914-2918 + 尾 ──
$npchead = sprintf($head, 'NPC图鉴');
$npc = array($npchead);
for($i = 2913; $i < 2918; $i++) $npc[] = $lines[$i];         // 2914-2918
// 追加物品掉落表入口说明（原2920-2928的掉落表说明放到intro尾部了，这里补NPC相关收尾）
$npc[] = '<br><br>';
$npc[] = '<p><span class="yellow">提示：将光标悬浮在NPC头像上可查看其大头像与详细资料。</span></p>';
file_put_contents('templates/default/help_npc.htm', implode("\n", $npc) . $tail);
echo "help_npc.htm: " . count($npc) . " 行\n";

echo "拆分完成\n";
