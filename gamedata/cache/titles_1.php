<?php 

if(!defined('IN_GAME')) exit('Access Denied'); 
  
  $titles_list = Array 
  (
    0 => '参展者',
    1 => '幻想',
    2 => '流星',
    3 => '黑客',
    4 => '最后一步',
    5 => '越红者',
    6 => '跨过彩虹',
    7 => '树形图',
    8 => 'TERRA',
    9 => '素描本',
    10 => '未来战士',
    11 => '寻星者',
    12 => '寂静洪流',
    13 => 'l33t',
    14 => '赌玉狂魔',
    15 => '时代眼泪',
    16 => '卸腿者',
    17 => '吉祥物',
    18 => '替天行道',
    19 => '美食家',
    20 => '补给掠夺者',
    21 => '神农',
    22 => '贝爷',
    23 => '热血机师',
    24 => '殴系爱好者',
    25 => '苍蓝之光',
    26 => '斩系爱好者',
    27 => '合二为一',
    28 => '钥刃大师',
    29 => '勇闯仙境',
    30 => '射系爱好者',
    31 => '黑洞边缘',
    32 => '重枪爱好者',
    33 => '光的道路',
    34 => '决斗者',
    35 => '加速同调',
    36 => '聚集的祈愿',
    37 => '平和之心',
    38 => '投系爱好者',
    39 => '红烧天堂',
    40 => '爆系爱好者',
    41 => '★刷刷刷★',
    42 => '★啪啪啪★',
    43 => '皇家烈焰',
    44 => '灵系爱好者',
    45 => '五行大师',
    46 => '贤者之石',
    47 => '知地利',
    48 => '知人和',
    49 => '混沌爱好者',
    50 => '混沌的深渊',
    51 => '棍棒爱好者',
    52 => '无情打钉者',
    53 => '磨刀爱好者',
    54 => '无情磨刀者',
    55 => '补丁爱好者',
    56 => '无情补丁',
    57 => '都市传说',
    58 => '除错大师',
    59 => '风驰电掣',
    60 => '暴雷骤雨',
    61 => '生存者',
    62 => '生存大师',
    63 => '实用主义者',
    64 => '现实主义者',
    65 => '脚底抹油',
    66 => '叶子钦定！',
    67 => '最后的荣光',
    68 => '执念的焰火',
    69 => '奇迹的篝火',
    70 => '开路的明火',
    71 => '挑战者',
    72 => '超越自我',
    73 => '超越人类',
    74 => '元素大师',
    75 => '二度打',
    76 => 'G.D.M',
    77 => '黄雀在此',
    78 => '猫咪在这！',
    79 => '大侠无名',
    80 => '极轻很轻',
    81 => '下毒党的希望',
    82 => '下毒党的荣光',
    83 => '下雷党的希望',
    84 => '下雷党的荣光',
    85 => '删除！',
    86 => '风纪委员',
    87 => '风卷的象征',
    88 => '勤奋好学',
    89 => '提高水平',
    90 => '复仇者',
    91 => '裁决者',
    92 => '城堡',
    93 => '海豹杀手',
    94 => '上帝之鞭',
    95 => '键·四季赞歌',
    96 => '★一发逆转！★',
    97 => '『ＥＸ』',
    98 => '剑圣',
    99 => 'R.T.S',
    100 => '✦约定的铁胃✦',
    101 => '✦空真理✦',
    102 => '✦光坂最强✦',
    103 => '✦ＬＢ的羁绊✦',
    104 => '✦莫非无敌✦',
    105 => 'KEY男',
    106 => '哲学家',
    107 => '铁头',
    108 => '神触',
    109 => '银之键',
    110 => '前向星',
    111 => '百变魔法使',
    112 => '换装迷宫',
    113 => '四面骰',
    114 => '大只佬',
    115 => '草木飞花',
    116 => '手下留情',
    117 => 'LOOP',
    118 => '虚拟体',
    119 => 'Daemon',
    120 => '弱子',

    # 第一期社区意见征询活动纪念称号：
    6001 => 'Lv.1 柠檬水',
    6002 => 'Lv.2 柠檬水',

    # 长期BUG提交奖励：软件测试工程师Lv.1~Lv.6
    7771 => 'Lv.1 昆虫学者',
    7772 => 'Lv.2 昆虫专家',
    7773 => 'Lv.3 鸟类学者',
    7774 => 'Lv.4 鸟类专家',
    7775 => 'Lv.5 语言学者',
    7776 => 'Lv.6 语言学家',
  );

  // rare: 0 - tgrey; 1 - tlime; 2 - tclan; 3 - tmagenta; 4 - torange; 5 - minirainbow;

  $title_desc = Array
  (
    # class-样式 title-提示悬浮框 img-图片url，会覆盖样式与悬浮框 

    # 0. 参展者
    0 => Array('class' => 'tgrey', 'title'=>" “参展者就是参加会展的人。你还记得自己是来参加动漫展的吧？”
      - 入场奖励：获得参加会展用的背包，替代校服
      - 获取方式：开局自带
    "),
    # 1. 幻想
    1 => Array('class' => 'tlime', 'title'=>" “……”
      - 入场奖励：暂无
      - 获取方式：完成成就「永恒世界的住人」的阶段二「幻想世界的往人」
    "),
    # 2. 流星
    2 => Array('class' => 'tclan', 'title'=>" “……”
      - 入场奖励：暂无
      - 获取方式：完成成就「永恒世界的住人」的阶段三「永恒的覆唱」
    "),
    # 5. 越红者
    5 => Array('class' => "tlime", 'title'=>" “……”
      - 入场奖励：获得一把红杀训练用的铁剑
      - 获取方式：完成成就「冒烟突火」的阶段二「红杀将军」
    "),
    # 6. 跨过彩虹
    6 => Array('class' => "tclan", 'title'=>" “……”
      - 入场奖励：获得一本奇怪的书，蓝凝似乎很喜欢它
      - 获取方式：完成成就「深度冻结」的阶段二「跨过彩虹」
    "),
    # 8.TERRA
    8 => Array('class' => "tlime", 'title'=>" “……”
      - 入场奖励：获得篝酱曾经用过的奇妙武器
      - 获取方式：完成成就「篝火的引导」的阶段二「世界的树形图」
    "),
    # 11. 寻星者
    11 => Array('class' => "tmagenta", 'title'=>" “过去的寻星者，今日的领航星。”
      - 入场奖励：获得额外斩系熟练，装备武器『寻星勇者』
      - 获取方式：完成成就「寻星急袭」
    "),
    # 13. l33t
    13 => Array('class' => "tmagenta", 'title'=>"  “l33t是什么意思？”
      - 入场奖励：获得KEY弹大礼包，可以通过合成来获得真货
      - 获取方式：完成成就「233MAX」
    "),
    # 14. 赌玉狂魔
    14 => Array('class' => "tmagenta", 'title'=>" “马有四条腿，少一条不打紧。”
      - 入场奖励：牺牲了腿，获得了更多金钱
      - 获取方式：完成成就「真名解放」
    "),
    # 19. 美食家
    19 => Array('class' => 'tlime', 'title'=>" “虽然他吃的大部分都是糊糊。”
      - 入场奖励：开局携带更好的补给……应该是这样吧？
      - 获取方式：完成成就「及时补给」的阶段二「衣食无忧」
    "),
    # 20. 补给掠夺者
    20 => Array('class' => 'tclan', 'title' => " “糊糊不能吃吗？”
      - 入场奖励：暂无
      - 获取方式：完成成就「及时补给」的阶段三「奥义很爽」
    "),
    # 24. 殴系爱好者
    24 => Array('class' => 'tlime', 'title'=>" “殴系到底有什么武器用？冰棍棒吗？”
      - 入场奖励：获得额外的殴系熟练度，并自带称号卡
      - 获取方式：暂无
    "),
    # 26. 斩系爱好者
    26 => Array('class' => 'tlime', 'title'=>" “斩系爱好者已经濒临灭绝。”
      - 入场奖励：获得额外的斩系熟练度，并自带称号卡
      - 获取方式：暂无
    "),
    # 38. 投系爱好者
    38 => Array('class' => 'tlime', 'title'=>" “小黄有点弱了，削下红莲龙吧！”
      - 入场奖励：获得额外的投系熟练度，并自带称号卡
      - 获取方式：暂无
    "),
    # 40. 爆系爱好者
    40 => Array('class' => 'tlime', 'title'=>" “体力恢复	桔黄色的果酱 150/249”
      - 入场奖励：获得额外的爆系熟练度，并自带称号卡
      - 获取方式：暂无
    "),
    # 30. 射系爱好者
    30 => Array('class' => 'tlime', 'title'=>" “「掠夺」使红暮获得了997元！”
      - 入场奖励：获得额外的射系熟练度，并自带称号卡
      - 获取方式：暂无
    "),
    # 44. 灵系爱好者
    44 => Array('class' => 'tlime', 'title'=>" “其实他没有超能力。”
      - 入场奖励：获得额外的灵系熟练度，并自带称号卡
      - 获取方式：暂无
    "),
    # 63. 实用主义者
    63 => Array('class' => "tlime", 'title'=>" “润！”
      - 入场奖励：获得额外的全系熟练度，并自带能开出防具等物品的福袋
      - 获取方式：完成成就「逃避可耻？」的阶段一
    "),
    # 66. 叶子钦定！
    66 => Array('class' => "tlime", 'title'=>" “小叶子认为你做得对！”
      - 入场奖励：获得「核子补给」
      - 获取方式：完成成就「核爆全灭」的阶段二「麻烦制造机？」
    "),
    # 69. 奇迹的篝火
    69 => Array('class' => "tclan", 'title'=>" “这是我们的使命。”
      - 入场奖励：可以看见游戏中大部分骰子的结果
      - 获取方式：完成成就「幻境解离」的阶段一
    "),
    
    # 120. 弱子
    120 => Array('img'=>"img/nicktest.png",),

    # 6001~6002 第一期意见征询活动纪念头衔
    6001 => Array('class' => 'tlime', 'title' => " “这个游戏以前其实有过留言板系统。”
      - 入场奖励：暂无
      - 获取方式：在官方讨论区参与了第一期意见征询活动"),
    6002 => Array('class' => 'tlime', 'title' => " “后来它变成了小广告系统。”
      - 入场奖励：暂无
      - 获取方式：在官方讨论区参与了第一期意见征询活动，并提供了被采纳的建议"),

    # 7771~7776 软件测试工程师Lv.1~Lv.6
    7771 => Array('class' => 'tlime', 'title' => " “他很擅长鉴定网络热门昆虫。” 
      - 入场奖励：暂无
      - 获取方式：帮助开发团队捉住了 1 只 bug"),
    7772 => Array('class' => 'tclan', 'title' => " “他已经不再满足于只鉴定昆虫了。”
      - 入场奖励：暂无
      - 获取方式：帮助开发团队捉住了 5 只 bug"),
    7773 => Array('class' => 'tlime', 'title' => " “鸽子汤就是用鸽子做的汤，要做鸽子汤，首先要将生姜切成……”
      - 入场奖励：暂无
      - 获取方式：帮助开发团队捉住了 10 只 bug"),
    7774 => Array('class' => 'tclan', 'title' => " “我是小八哥、我是你……”
      - 入场奖励：暂无
      - 获取方式：帮助开发团队捉住了 15 只 bug"),
    7775 => Array('class' => 'tmagenta', 'title' => " “文字，是句读的韵律，是魔法的咒语。”
      - 入场奖励：暂无
      - 获取方式：帮助开发团队捉住了 30 只 bug"),
    7776 => Array('class' => 'tmagenta', 'title' => " “文字，使愚者千虑，供智者隐喻。”
      - 入场奖励：暂无
      - 获取方式：帮助开发团队捉住了 50 只 bug"),
  );

  //所有在player表里登记过的合法字段都可以写进里面 //非法内容会被自动过滤掉……大概吧
  //同时，也支持合法的运算符，写在 [::] 内可以自动识别…… //呃试着做了下感觉有风险，还是写死吧，暂时只支持 += -= *= /= 四种吧，应该够用了吧？
  //比如入场时斩熟+50 就写 'wk' => '[:+=:]50',
  $title_valid = Array
  (
    # 120. 弱子
    120 => Array(
      'clbpara' => Array('cheater' => 1,),
    ),
    # 0. 参展者
    0 => Array(
      'arb' => '二次元漫展背包', 'arbk' => 'DB', 'arbe' => 5,  'arbs' => 5, 'arbsk' => '^',
    ),
    # 66. 叶子钦定！
    66 => Array(
      'itm5' => '核子面包', 'itmk5' => 'HH', 'itme5' => 237,  'itms5' => 5, 'itmsk5' => '',
      'itm6' => '核子矿泉水', 'itmk6' => 'HS', 'itme6' => 237,  'itms6' => 5, 'itmsk6' => '',
    ),
    # 11. 寻星者
    11 => Array(
      'wk' => '[:+=:]50',
      'wep' => '『寻星勇者』', 'wepk' => 'WK', 'wepe' => 90,  'weps' => 35, 'wepsk' => 'd',
    ),
    # 8.TERRA
    8 => Array(
      'itm6' => '篝酱的奇迹☆胶带～棍', 'itmk6' => 'WP', 'itme6' => 42,  'itms6' => 300, 'itmsk6' => 'dej',
    ),
    # 5. 越红者
    5 => Array(
      'itm6' => '红杀铁剑', 'itmk6' => 'WK', 'itme6' => 60,  'itms6' => 30, 'itmsk6' => 'N',
    ),
    # 6. 跨过彩虹
    6 => Array(
      'itm6' => 'AZURE RONDO模样的杏仁豆腐', 'itmk6' => 'WF', 'itme6' => 233,  'itms6' => 9, 'itmsk6' => 'd',
    ),
    # 13. l33t
    13 => Array(
      'itm1' => '键 希望弹模样的杏仁豆腐', 'itmk1' => 'WC', 'itme1' => 500,  'itms1' => 1, 'itmsk1' => 'z',
      'itm2' => '键 燃烧弹模样的杏仁豆腐', 'itmk2' => 'WF', 'itme2' => 500,  'itms2' => 1, 'itmsk2' => 'z',
      'itm3' => '键 生命弹模样的杏仁豆腐', 'itmk3' => 'WG', 'itme3' => 500,  'itms3' => 1, 'itmsk3' => 'z',
      'itm4' => '键 未来弹模样的杏仁豆腐', 'itmk4' => 'WP', 'itme4' => 500,  'itms4' => 1, 'itmsk4' => 'z',
      'itm5' => '键 催泪弹模样的杏仁豆腐', 'itmk5' => 'WD', 'itme5' => 500,  'itms5' => 1, 'itmsk5' => 'z',
      'itm6' => '键 旅途弹模样的杏仁豆腐', 'itmk6' => 'WK', 'itme6' => 500,  'itms6' => 1, 'itmsk6' => 'z',
    ),
    # 14. 赌玉狂魔
    14 => Array(
      'money' => '[:+=:]3210',
      'wep' => '增殖之腿', 'wepk' => 'WCP', 'wepe' => 10,  'weps' => '∞', 'wepsk' => 'd',
      'arf' => '腿？', 'arfk' => 'DF', 'arfe' => 9, 'arfs' => 19, 'arfsk' => 'VxK',
    ),
    # 19. 美食家
    19 => Array(
      'itm1' => '裱花布里欧修', 'itmk1' => 'HH', 'itme1' => 255,  'itms1' => 10, 'itmsk1' => 'z',
      'itm2' => '埃斯卡依云斐济水', 'itmk2' => 'HS', 'itme2' => 255,  'itms2' => 20, 'itmsk2' => 'z',
      'itm5' => '强效西柚汁', 'itmk5' => 'HB', 'itme5' => 127,  'itms5' => 15, 'itmsk5' => 'z',
    ),
    # 24. 殴系爱好者
    24 => Array(
      'wp' => '[:+=:]50',
      'itm6' => '「街头霸王」称号卡', 'itmk6' => 'ZB', 'itme6' => 1,  'itms6' => 1, 'itmsk6' => '',
    ),
    # 26. 斩系爱好者
    26 => Array(
      'wk' => '[:+=:]50',
      'itm6' => '「见敌必斩」称号卡', 'itmk6' => 'ZB', 'itme6' => 2,  'itms6' => 1, 'itmsk6' => '',
    ),
    # 38. 投系爱好者
    38 => Array(
      'wc' => '[:+=:]50',
      'itm6' => '「灌篮高手」称号卡', 'itmk6' => 'ZB', 'itme6' => 3,  'itms6' => 1, 'itmsk6' => '',
    ),
    # 40. 爆系爱好者
    40 => Array(
      'wd' => '[:+=:]50',
      'itm6' => '「拆弹专家」称号卡', 'itmk6' => 'ZB', 'itme6' => 5,  'itms6' => 1, 'itmsk6' => '',
    ),
    # 30. 射系爱好者
    30 => Array(
      'wg' => '[:+=:]50',
      'itm6' => '「狙击鹰眼」称号卡', 'itmk6' => 'ZB', 'itme6' => 4,  'itms6' => 1, 'itmsk6' => '',
    ),
    # 44. 灵系爱好者
    44 => Array(
      'wf' => '[:+=:]50',
      'itm6' => '「超能力者」称号卡', 'itmk6' => 'ZB', 'itme6' => 9,  'itms6' => 1, 'itmsk6' => '',
    ),
    # 63. 实用主义者
    63 => Array(
      'wp' => '[:+=:]25','wk' => '[:+=:]25','wc' => '[:+=:]25','wd' => '[:+=:]25','wg' => '[:+=:]25','wf' => '[:+=:]25',
      'itm6' => 'SPECIAL TECH 「特选科技」', 'itmk6' => 'p0O1', 'itme6' => 1,  'itms6' => 1, 'itmsk6' => '',
    ),
  );

?>