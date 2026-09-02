<?php

define('CURSCRIPT', 'help');

require './include/common.inc.php';
require './include/game.func.php';
include_once GAME_ROOT.'./include/game/itemplace.func.php';

include config('npcdict',$gamecfg);
include_once GAME_ROOT.'./include/game/npcdict.func.php';
$npcinit = array(); // get_npc_helpinfo 引用，辞典已展平无需父类模板
for ($i=1; $i<=6; $i++) $itemlst[$i]=$i;

// 从辞典构建 $npcinfo 兼容结构（sub/asub/esub 分组）
$npcinfo = array();
foreach($npcdict as $type => $npcs) {
	foreach($npcs as $name => $npc) {
		$src = isset($npc['source']) ? $npc['source'] : 'sub';
		$npcinfo[$type][$src][$name] = $npc;
	}
	// 保留大类属性（取第一个NPC的字段作为父类）
	if(!empty($npcs)) {
		$first = reset($npcs);
		$npcinfo[$type]['mode'] = isset($first['mode']) ? $first['mode'] : 1;
	}
}
$npcinfo = get_npc_helpinfo($npcinfo);

$ty1[1]=1; $ty1[2]=88; 
$ty2[1]=Array(5,'asub'); $ty2[2]=Array(6,'asub'); 
$ty2a[1]=Array(19,'asub'); #真红蓝
$ty3[1]=Array(11,'asub');
$ty4[1]=90; $ty4[2]=92;
$ty5[1] = Array(2,'asub');
$ty6[1]=14; $ty6[2]=4; 
$ty6e[1]=Array(14,'esub'); #女主第二形态情报
$ty7[1]=Array(13,'asub'); 
$ty8[1]=Array(15,'asub'); 
$ty9[1]=22;
$ty10[1]=21;
$ty11[1]=Array(89,'asub'); 
$ty11e[1]=Array(89,'esub'); #电掣NPC第二形态情报
$ty12[1]=24;
$ty25a[1] = Array(25,'asub'); #佣兵NPC

$extrahead = <<<EOT
<STYLE type=text/css>
BODY {
	FONT-SIZE: 10pt;MARGIN: 0; color:#eee; FONT-FAMILY: "Trebuchet MS","Gill Sans","Microsoft Sans Serif",sans-serif;
}
A {
	COLOR: #eee
}
A:visited {
	COLOR: #eee
}
A:active {
	color: #98fb98;text-decoration:underline
}
P{ line-height:16px
}

DIV.help {
	PADDING-LEFT: 1em;PADDING-right: 1em
}

.subtitle2 {
	font-family: "微软雅黑"; color: #98fb98; width: 100%;font-size: 16px;font-weight:900;
}

</STYLE>
EOT;

include template('help_npc');

?>
