# 帮助页面重构计划

## 已完成（2026-09-02 方案B实施）

帮助入口已改为悬浮下拉菜单（`header.htm`），从上到下：

1. **玩法介绍** — `helpintro.php` + `help_intro.htm`
   - 原 help.htm 的 FAQ~内定称号、特殊道具、天气、游戏周边、单机版、玩家守则
   - 顶部目录菜单的 道具合成/NPC简介/物品掉落表 三项改为指向子页面
   - 页内锚点已改为本页锚点
2. **合成表一览** — `helpmix.php` + `help_mix.htm`
   - 复用 help.php 的 mixhelp 缓存生成逻辑（filemtime + writeover）
   - 道具合成说明 + {template mixhelp} + 同调/超量合成说明
3. **地图百科** — `maphelp.php` + `maphelp.htm`（新页面）
   - 每个地图/分支形态：名称（坐标·室内/室外）+ areainfo简介 + 固定掉落物表（跟在简介后）+ 固定刷新NPC行
   - 固定NPC从 `$npc_spawn_config['init']` + `$npc_sub_pls` 推导（mapresource的npc字段暂空，数据迁入后可切换数据源）
   - map_id 99 全图随机掉落池单独成节
   - 全图随机刷新NPC汇总
   - 链接 itemhelp.php（按地点排序的完整掉落表）
4. **NPC图鉴** — `helpnpc.php` + `help_npc.htm`
   - 复用 help.php 的 npcinfo 构建（npcdict → npcinfo 兼容结构 + ty1~ty25a 分组）
   - NPC简介说明 + {template npchelp}

## 保留与回退

- 原 `help.php` + `help.htm` 完整保留，可随时回退（header不再直链但它仍可访问）
- `mapitemresource_1.php` 保留未删（物品数据已合入 mapresource）
- 拆分脚本：`split_help.php`（help.htm → 三个子模板的机械切分）
- 回归测试：`test_tpl_compile.php`（模板编译冒烟）、`test_maphelp_data.php`（地图百科数据构建）

## 后续待办

- 地图百科NPC数据源切换：mapresource各分支的 `npc` 字段填充后，`maphelp.php` 改读该字段（当前从 npcdict.func.php 的刷新配置推导）
- itemhelpmain.htm 的"返回帮助"链接与表单可改为指向 maphelp.php
- 原 help.php 若确认稳定，可改为 301 到 helpintro.php 或精简为目录页
- 地图百科可加锚点目录（35+地图较长）
