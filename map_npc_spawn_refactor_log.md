# 地图分支npc字段结构化试点日志（2026-09-03）

## 目标（试点）

将固定刷新NPC从全局 `npc_spawn_config['init']` 下沉到 gameresource 地图分支 `npc` 字段，实现"应该刷=会刷"的展示/刷新同源。试点对象：SCP生物（type 88，图32固定刷4只）。验证通过后再推广其他固定图类。

## npc字段双语义（兼容设计）

- **扁平 `Array(typeId,...)`**：引用标注（现状兼容），数量查全局config，不入刷新计划——图0红暮、图34英灵殿系、种火图、99池均为此形态，行为不变
- **结构化 `Array(typeId => num)`**：图级固定刷新，数量直接由地图分支定义——图32两分支均为 `Array(88 => 4)`
- 判别规则：数组key非连续索引（`range(0,n-1)`）即结构化
- 同一type配多图时后者覆盖（当前语义：固定刷新type↔图一一对应）

## 数据流

`spawn_npc_all` 开局时：`get_map_npc_plan()` 遍历35图选中分支（`$mapid`，未设兜底分支0）展开结构化npc为计划表 → 主循环 cfg 取值**地图计划优先、全局config兜底**。主循环体零改动：数量/位置/sub轮询/遍历顺序/pid 与改造前完全一致。

## 改动清单

- `gamedata/cache/gameresource_1.php` — 图32两分支 npc 结构化(88=>4)；init 删 88 条目
- `include/game/npcdict.func.php` — 新增 `get_map_npc_plan()`；`spawn_npc_all` cfg 分流
- `include/game/maphelp.func.php`（新） — `maphelp_npcword`/`maphelp_npcname_word`/`maphelp_eventword`/`maphelp_mapfeatures` 抽公共（maphelp.php 与测试共用同源，消除复制漂移）
- `maphelp.php` — 4函数改为 include 公共文件；`kindword`/`skword`/`findrate` 留页面
- `include/admin/resourcemng.php` + `templates/default/admin_resourcemng.htm` — npcword 输入语法：`typeId`（扁平）或 `typeId:数量`（结构化）；混输/非法项**拒绝保存+警告**；结构化渲染 `88:4`
- `migrate_gameresource.php` — 32图注入同步
- 测试：`test_map_npc_spawn.php`（新，17项）、`test_spawn_config_equiv.php`（init基准12类+plan等价+全量展开等价）、`test_admin_cfgmng.php`（+7项语法用例）、`test_maphelp_data.php`（改include同源）

## 等价性结论

旧 init(含88条目) 与 新(init无88 + 地图plan含88) **全量展开逐type `===` 全等**——开局SCP刷新行为与改造前完全一致。进行中的局不受影响（init 仅开局执行）。

## 后续推广路径（试点已验证）

- 固定图类（1红暮/20-26英灵殿系）：npc 改 `typeId:num` + 删全局 init 条目即可，机制复用
- 随机散布类（14/90/91）：留 99 池段承载（随机池总量）
- 种火 sub 级（92）：需设计 sub 粒度入图的表达（如 `92 => Array('sub' => ..., 'num' => ...)`）
- add 模式（事件召唤，17类）：与地图无关，留全局
