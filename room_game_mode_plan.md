# 房间游戏模式（小列表地图）实施计划

目标：把"小列表地图游戏"做成房间级模式——不同房间绑定不同配置号（`gameresource_X.php`），
房间2用小列表白名单，房间1保持全量35图，互不影响。

## 一、调研结论（现状）

### 房间系统已存在且完善
- **数据隔离**：`users.roomid` → `common.inc.php:81` `$groomid` → `:96` `$tablepre = 'acbra3_s{N}_'`
  ——每房间独立物理表（players/mapitem/shopitem...），`rs_game mode&1` 用 reset.sql 建表，已支持。
- **game表**（基础前缀`acbra3_game`）：一行一房间（groomid主键），存 gameinfo
  （gamenum/gamestate/arealist/**mapinfo JSON**/gamevars...）。
  **mapinfo 本来就按房间独立存储**（各房间分支轮换结果互不相同）——小列表天然按房间生效。
- **大厅**：`index.php` `$roomact=create/join/exit/close`（AJAX）+ `?is_new` JSON房间列表；
  建房 `roommng_create_new_room`（IP限制、房主、自动分配房间号）。
- **自动升级**：`roommng_verify_db_game_structure()`（common.inc.php:65）——
  ALTER加列的现成机制，game表加字段往这里塞。
- **后台**：`admin roommng` 强制关闭/关闭闲置房间。

### 配置号体系已存在，只是被锁死在1
- `config($file,$cfg)`（`global.func.php:136`）：找 `cache/{file}_{cfg}.php`，**不存在自动回退 `_1.php`**。
- `rs_game:8` 已 `global $gamecfg` ——rs_game 内 gameresource（:51/:303）、shopitem（:391）
  加载**已按 $gamecfg**，无需改。
- `gamecfgmng`（admin）按号编辑 gamecfg_X 数值（areahour/areaadd/combolimit...）——模式数值差异载体已有。
- **缺口**：`gamedata/system.php:22 $gamecfg = 1` 全局写死，所有房间共用1号配置。

### 关键矛盾：加载顺序
`common.inc.php:54-61` 的8项 `require config('XXX',$gamecfg)` 早于 `:79` fetch udata / `:81` $groomid 判定。
按房间分配置，必须把"房间gamecfg"的确定提前到 :54 之前。

### 图id连续性假设（小列表的技术债，独立于房间）
仅7处依赖"图id=0..N-1连续"：
- `system.func.php:55` 轮换 `for($id=1;$id<33;$id++)`（33/34靠上限隐式跳过）
- `system.func.php:66` lockbranch扫描 `for($id=0;$id<35;$id++)`
- `system.func.php:81` mapinfo构建 `for($id=0;$id<35;$id++)`（删图踩空键）
- `system.func.php:259` arealist构建 `range(1,$plsnum-1)`
- `system.func.php:306` rs_map物品循环 `for($imap=0;$imap<$plsnum;$imap++)`
- `system.func.php:331` 99池落图 `rand(0,$plsnum-1)`
- `npcdict.func.php` spawn_npc_all 随机 `rand(0,$plsnum-1)` 直当图id
- `search.func.php:36` 移动判定 `$moveto >= $plsnum`（冗余上限，非连续误伤合法id）

天然安全（不用动）：`$mapinfo['plsinfo'][$pls]` 按键取名、`$arealist[rand(...)]` 下标语义
（传送/禁区扩张/幻影终端显示）、`array_search` 位置判定、`name:`寻靶、地图npc plan、flags派生表
（`derive_map_flaglists`/`migrate_mapinfo_flags` 都 foreach plsinfo 实际键）。

## 二、设计决策

1. **保留原图id不重排**：子集 `{0,3,7,12,19,33}` 用原编号。寻靶/固定刷新/事件/存档引用零改动。
2. **模式=配置号=房间属性**：game表加 `gamecfg` 列；建房时选定；进房后全局 $gamecfg=房间值。
   8项配置全按号切换（缺号回退1号=天然降级）。**不只地图：数值/战斗/俱乐部技能等都能按模式不同**。
3. **白名单不删数据**：gameresource 顶层 `$game_maps_mode`，被排除图数据保留（换模式零成本，maphelp全量展示）。
4. **入口图0锁死**：active 必含0（出生点+商店+最终防线语义绑定图0）。
5. **模式注册表**：`gamedata/system.php`（或新文件）定义 `$roommodes = Array(1=>'标准游戏', 2=>'小列表')`，
   1号恒为标准。大厅建房下拉、房间列表显示模式名都从这里读。

## 三、分阶段实施

### Phase 1：房间绑定配置号 ✅ 已完成（2026-09-04）
改动清单：
- `system.php`：`$roommodes = Array(1=>'常规模式', 2=>'快速模式')` 注册表
- `roommng.func.php`：`roommng_verify_db_game_structure()` 自动加 game表`gamecfg`列（默认1）；
  `roommng_create_new_room(&$udata, $roommode)` 建房写入模式号（未注册回退1）；
  `roommng_resolve_gamecfg($cuser)` 房间配置探测（未登录/未进房/房间不存在返0）
- `common.inc.php`：`$gtablepre`/cookie绑定提前，探测先于8项config()加载 → 全套配置按房间模式号切换
- `index.php`：create接`$roommode`；`?is_new` JSON带`roomModes`/每房间`mode`
- `roomlist.htm`：建房按钮展开模式选择（常规/快速）；启用游戏模式列
- `gamedata/cache/gameresource_2.php`：从1号派生基线（当前与1号一致，待定制）
- 测试：`test_room_mode.php` 25项（stub db）；全量回归绿

### Phase 2：快速模式（10图小列表）设计与实施

#### 2.1 地图分类tag ✅ 已实现（编辑器+工具，待用户打标）
- **数据落点**：`gameresource_1.php` 每图分支级 `'tag'` 键（与flags同级，var_export重建不丢）。
- **取值4类**：`growth`（一类·发育）/ `equip`（二类·装备）/ `shop`（商店）/ `seed`（种火）；无键或空=未分类。
- **约定**：同一图各分支标同一类（轮换不改变地图用途）；99池不标。
- **入口**：后台 resourcemng 展开区“分类tag”下拉（非法值拒收、未POST保持原样、可选未分类清空）。
- **辅助**：`php scan_maptags.php` 输出各类候选图清单/未分类清单/同图分支tag冲突警告。

#### 2.2 抽取配方与rs_game收口 ✅ 已实现（2026-09-04）
- **配方**（gameresource_2.php顶层）：`$game_maps_mode = Array('entry'=>0, 'pick'=>Array('growth'=>3,'equip'=>3,'seed'=>2,'shop'=>1), 'exclude'=>Array(34))`
- **抽图**：`rs_game_active_maps($maps,$cfg)`（system.func.php）——无配置/1号=全量回退；图级tag池取首个非空分支tag；
  每类`array_rand`抽取、池不足取全部、池空跳过
- **地图组装**：`rs_game_build_mapinfo($maps,$cfg)`——从rs_game mode&2抽出为独立函数；
  轮换改foreach(activelist)（entry不轮换）；33/34不轮换的硬编码由lockbranch数据驱动替代；
  mapinfo全字段键=入选图id（非连续子集）
- **arealist**：改从`mapinfo['plsinfo']`键集派生（0队首+其余shuffle）
- **rs_map物品循环**：改`foreach(array_keys($mapinfo['plsinfo']))`
- **随机落点密度（模拟摘出）**：99池物品与开局随机NPC改为“刷到完整大地图再摘出到本局”
  ——全量模拟池随机（`rs_game_sim_pools`派生三池drop/npc/npcdeep，排除按全量flags并集），
  落点不在本局的**丢弃该实例**（每图密度与全量模式一致）；模拟池存`$gamevars['sim_full_pls']`持久化
  （开局算+save_gameinfo写盘，运行时禁区刷新/后续读回）；全量模式模拟池=全集无丢弃=行为不变；
  开局NPC用`sim_npc_pls()`（NULL=丢弃跳过该NPC），运行时召唤仍用`rand_npc_pls()`（本局池，不丢弃）
- **spawn_npc_all/spawn_npc**：新增`rand_npc_pls()`池差集随机；
  sub_pls/cfg_pls数组过滤到本局图集；寻靶落废图(<35不在plsinfo)回退随机，隐藏图id(35+)放行
- **search移动判定**：删两处`$moveto >= $plsnum`连续id上限（非连续高id会误拒）
- 禁区系统全列表下标语义（arealist[rand(a,b)]/array_slice），零改动
- 测试：`test_maps_mode.php` 62项（mini非连续/全量等价/模拟摘出采样/真实数据演练：你打的tag恰好凑齐10张）

#### ⚑ 2号数据同步义务（重要）
- gameresource_2为**完整copy**（非差异补丁）：**1号数据/打tag更新后必须重派生2号**
  （copy 1号 + 顶部配方头），否则快速模式读旧数据抽空tag池（已踩过：旧2号无tag→抽取只剩0号图）
- 后续待办：resourcemng保存1号时自动同步2号，或派生工具化（migrate_gameresource.php加derive子命令）

**快速模式禁区节奏与入场经验 ✅（2026-09-04）**
- **禁区10分钟一禁**：新建 `gamedata/cache/gamecfg_2.php`（1号完整副本仅改 `$areahour = 10`）；
  走既有 `config('gamecfg',$gamecfg)` 按号加载（房间探测已在 common.inc.php 覆盖 `$gamecfg`），零代码改动；
  1号保持20分钟不变。`$areaadd=3` 不变：快速模式11图→约4禁全禁（短局节奏）
- **入场经验+2禁补偿**（`valid.php:102`）：`$exp = ($areanum + ($gamecfg == 2 ? 2 : 0)) * 20;`
  ——快速模式开局进入即得 exp40（=普通模式2禁进入），之后每禁+20照常叠加；普通模式行为不变
- **2号同步义务**（与gameresource_2同）：gamecfg_1 改动后必须重派生 gamecfg_2
  （copy 1号 + 保留头部与 `$areahour=10` 行，CRLF行尾）；
  `test_room_mode.php` 有归一化逐字节一致断言防漂移
- 测试：`test_room_mode.php` 第7/8节（gamecfg_2基线/加载解析/入场公式复算）

#### 2.3 弱版红暮与结局限制 ✅ 已实现（2026-09-04）
- **弱版红暮开局天然成立**：0号图npc字段typeId1→开局铺设取`sub`源=`红暮-自动托管`（7500血），
  强版（asub）不参与开局刷新（`get_npc_init_pool`）——无需派生npcdict_2
- **挑战卡链正常**：弱版红暮掉挑战卡→应约红暮&蓝凝（typeId19）固定刷0号无月之影（entry恒入选，无需fallback）→
  掉冰炎钥匙·炎/冰→元素分解（配方2）→合成游戏解除钥匙→**end3锁定解除=快速模式主结局线，完整保留**
- **破灭之诗链断**（gameresource_2固有差异②）：init池typeId14三女主num=0不刷→歌词卡绝版→
  破灭之诗/强版红暮/真红暮蓝凝(addnpc4`name:雏菊之丘`)/全图hack全部不可达；
  addnpc4的落废图fallback=`rand_npc_pls()`随机本局（本就不会触发）
- **end7幻境解离断**（`item.func.php:2033`）：`『G.A.M.E.O.V.E.R』`使用按`gamecfg==2`拒绝——
  该链走C.H.A.O.S（琉璃血/武神线）与三女主无关，typeId14断不掉它，需单独断
- 原方案"派生npcdict_2改typeId1属性"作废（开局弱版本就如此）
- 测试：`test_room_mode.php` 第6b节（init14双号断言+头部差异标记+end7分支顺序）

#### 2.5 新刷新机制 ✅ 已实现（2026-09-04，用户定稿：按禁区阶段刷+虚空地图平摊）
- **刷新节奏**：维持现有按禁区阶段刷（每禁刷下一阶段iarea物品）；快速模式10分钟一禁自然加密节奏
- **99池全图随机平摊**（两处，`$gamecfg == 2`分支）：
  - 99池物品（`system.func.php:377` rs_game mode&16）：落点从“模拟全量+摘出丢弃”
    改为直接随机本局图池（排除norandom_drop）——**总量保持**，每图密度上升适应短局
  - 99池NPC（`npcdict.func.php:274` sim_npc_pls开局路径）：同上平摊（走`rand_npc_pls`语义，
    280小兵+90种火全量刷入本局图，躲deepzone/排除norandom_npc逻辑保留）；
    **运行时召唤（addnpc pls=99）本来就是本局随机，无需改**
- 全量模式摘出语义不变（密度与全量一致）；全量模式下两种算法等价（模拟池=全集）
- 每图item/npc字段（非99池）本来就只刷本局图，无需改
- 测试：`test_room_mode.php` 第6c节（sim_npc_pls双模式行为+rs_game分支顺序）；
  `test_init_pool.php` 用例5修正（type1走map_plan而非全局init池，历史遗留）

### Phase 3：治理与边界
1. **maphelp**：保留全量35图展示（百科性质）；游戏内移动目标列表按 plsinfo 本局集合（现状已区分）。
2. **寻靶失败**：小列表不含33 → 破灭之诗篝回退随机+log（npcdict 现有行为，不改）。
3. **管理界面**：resourcemng 编辑器支持选配置号（编辑 gameresource_2）；roommng 房间列表显示模式。
4. **禁区节奏**：入选数变小 → 全图开放变快，`areahour/areaadd` 是 gamecfg_2 数值问题，非代码。
5. **旧局迁移**：load_gameinfo 的 flags/events 迁移均 foreach plsinfo 实际键，天然适配非连续；
   旧局都是1号开局，小列表局开局即带全字段，无跨模式迁移需求。

## 四、风险与注意事项

- **common.inc.php 顺序重排是全站入口**，Phase 1 必须配等价回归测试；重排只移动"变量绑定"，
  不改逻辑，所有房间 gamecfg=1 时与现状逐字节等价。
- **$gamecfg 全局污染**：所有走 common.inc.php 的入口统一；maphelp.php 等独立入口也走 common ✓。
- **config() 回退语义**：gameresource_2 存在=**完整替换**不是差异合并——派生必须完整复制。
- **gamenum 全局递增**（建房取 max(gamenum)+0）：跨模式共用计数，无影响。
- **性能**：每请求多1-2条轻量SELECT（房间探测），可接受；后续可与 fetch udata 合并成一次查询。

## 五、实施顺序（当前待办）

1. ✅ Phase 1 房间绑定配置号（已完成，见上）
2. ✅ Phase 2.1 地图分类tag机制（编辑器下拉+批量打标+scan_maptags工具）
3. ✅ 用户打标（35图已打：growth12/equip11/shop2/seed3+无月英灵殿待定）
4. ✅ Phase 2.2 抽取配方+rs_game收口+rand改造+test_maps_mode（真实数据恰好10张）
5. ⬜ Phase 2.3 弱版红暮（npcdict_2派生）+ 结局限制（winmode收窄仅"解除锁定"）
6. ⬜ Phase 2.5 新刷新机制（定稿后实施）
7. ⬜ Phase 3 治理项：2号自动同步/resourcemng选配置号/禁区节奏数值（gamecfg_2）/battle.func.php缺失警告排查

验证：`php test_maps_mode.php`、`php test_room_mode.php`、
回归 `test_map_flags.php`/`test_spawn_config_equiv.php`/`test_map_npc_spawn.php`/`test_admin_cfgmng.php`。
