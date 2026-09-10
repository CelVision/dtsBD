# 帮助页面重构计划

## 已完成（2026-09-02 方案B实施 + gameresource定版）

### 帮助页面拆分

帮助入口已改为悬浮下拉菜单（`header.htm`），从上到下：

1. **玩法介绍** — `helpintro.php` + `help_intro.htm`
   - 原 help.htm 的 FAQ~内定称号、特殊道具、天气、游戏周边、单机版、玩家守则
   - 顶部目录菜单的 道具合成/NPC简介/物品掉落表 三项改为指向子页面
   - 页内锚点已改为本页锚点
2. **合成表一览** — `helpmix.php` + `help_mix.htm`
   - 复用原 mixhelp 缓存生成逻辑（filemtime + writeover）
   - 道具合成说明 + {template mixhelp} + 同调/超量合成说明
3. **地图百科** — `maphelp.php` + `maphelp.htm`（已升级为综合卡片表格，见下）
4. **NPC图鉴** — `helpnpc.php` + `help_npc.htm`
   - 复用 npcinfo 构建（npcdict → npcinfo 兼容结构 + ty1~ty25a 分组）

### 地图百科综合表格（2026-09-02 完成）

每张地图卡片（每个分支形态一张，共67张）整合：

- **背景图头像**：分支 `bg` 字段 → `img/location/{bg}.jpg` 作单元格背景（cover填充；缺图回退 `-1.png`）
- 上表：地图名称 / 分支形态（分支形态N·固定形态） / 坐标 / 环境（室内·室外） / **地图特性**（商店·安全箱·医院，来自 `$shops`/`$depots`/`$hospitals`） / **基准遇敌率**（40%+`$pls_find_modifier`修正，连斗+20%·死斗+40%说明在页首） / **特殊事件**（分支 `events` → 中文名）
- 下表：**简介**（areainfo） / **固定掉落物表**（刷新时间·名称·类型·效/耐·属性·数量） / **固定刷新NPC**（分支 `npc` 字段 → typeId → npcdict 名称+sub位置+刷新数量）
- **未填充分支（plsinfo为空，共24个）**：显示「（待补充）」灰字，其余单元格留空/—
- map 99 全图随机池（item/npc 两key，与普通地图同结构）单独成节；链接 itemhelp.php
- 页首说明：分支随机选一、遇敌率与发现率关系、地图特性释义
- 附带：`itemhelp.htm` 缓存自动再生（地点列由旧“端点”占位变为分支真名）

### gameresource 定版（原 mapresource 更名投产）

- `gamedata/cache/mapresource_1.php` → **`gamedata/cache/gameresource_1.php`**（git mv，物品数据已合入）
- **每个地图分支的 `npc` 字段**：该地初始固定刷新的 typeId（引用 npcdict 辞典模板，不复制数据）
  - map 0=无月之影(1红暮)，map 32=SCP研究设施(88)，map 34=英灵殿(20,21,22,24,26)
  - type 92 种火按sub固定位置：篝火→2/15，埋火→3/22，永火→18/23，残火→20/24，焰火→12/29
- **map 99（全图随机池，非地图）**：`item`=随机散落物品515件，`npc`=随机池类别(14女主,90数据残影,91残影-？？？)
- **文末迁入** `$npc_spawn_config`（init/add两模式13+17类）+ `$npc_sub_pls`（92种火5个sub）
- **`npcdict.func.php` 只保留函数**：配置经 `load_npc_spawn_data()` 懒加载自 gameresource（等价性测试通过：与原内嵌配置数组===完全相等，含默认值/幂等/函数作用域）
- 调用点全部改名（9处）：system.func.php×2、global.func.php events迁移块、printitm.func.php、itemplace.func.php、help/helpmix/itemhelp/maphelp.php

## 保留与回退

- 原 `help.php` + `help.htm` 完整保留，可随时回退
- `mapitemresource_1.php`、`mapresource_1.php.bak_merge`、`npcdict_1.php.bak_c4` 保留（回退用）
- 迁移/验证脚本：`migrate_gameresource.php`（npc字段填充+配置迁入）、`verify_gameresource.php`（数据结构验证）、`test_spawn_config_equiv.php`（**新旧配置等价性回归**）、`test_maphelp_data.php`（新版含特性/遇敌率/事件/未填充分支统计）、`check_maphelp_content.php`（HTTP渲染内容检查，16项全部通过）、`test_tpl_compile.php`

## 后续待办

- spawn_npc_all 可改为直接读地图分支 npc 字段（当前仍按 typeId 配置遍历辞典，行为一致）
- itemhelpmain.htm 的"返回帮助"链接与表单可改为指向 maphelp.php
- 原 help.php 若确认稳定，可精简为目录页
- 地图百科可加锚点目录（35+地图较长）
