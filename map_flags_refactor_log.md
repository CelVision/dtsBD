# 地图特性flags配置化日志（2026-09-03）

## 目标

将散落在系统代码里的 6 处地图硬编码（写死地图 id 0/32/33/34）抽离为 `gameresource_1.php` 地图分支的 `'flags'` 特性字段，实现数据配置化 + 管理台可视化编辑。同一图 id 的轮换分支可配不同特性（分支级差异由此实现）。

## 特性语义

| flag | 语义 | 替代的原硬编码 |
| --- | --- | --- |
| `deepzone` | NPC 主动避开的危险图（躲禁区追杀/安全落点） | `resources_1.php` 的 `$deepzones = Array(0,32,33,34)` |
| `norandom_drop` | 全图随机池（map 99）物品不落入 | `rs_game` mode&16 的 `rand(1,..)` 隐性排 0 + `while($rmap==34)` |
| `norandom_npc` | 全图随机池 NPC 不刷入 | `spawn_npc_all` 的 `rand(1,..)` 隐性排 0 + `while($rpls==34)` |
| `noesc_tp` | 躲禁区被动传送/英灵殿 gate 传送不去 | `add_once_area` 两处 + `event_valhalla_gate` 的 `while($pls==34)` |
| `lockbranch` | 开局锁死基础分支不参与轮换 | `rs_game` mode&2 的写死三行 `$mapid[0/33/34]=0` |

## 数据流

1. **开局**：`rs_game` mode&2 组装 `mapinfo` 时提取**选中分支**的 `flags` → 存 DB（`derive_map_flaglists()` 同步派生全局 4 表，供同调用的 mode&16 使用）
2. **每请求**：`load_gameinfo()` 解码 mapinfo → `migrate_mapinfo_flags()` 补旧局默认（见下）→ `derive_map_flaglists()` 派生 `$deepzones/$noranddrop_pls/$norandnpc_pls/$noesc_pls` → 消费点（全部为函数内 `global`，时序安全：common.inc.php `:103→:104` 之间无调用）
3. **消费点**：rs_game mode&16（物品落点）、spawn_npc_all（NPC 出生）、add_once_area（躲禁区传送×2）、event_valhalla_gate、get_safe_plslist

## 改动清单

- `gamedata/cache/gameresource_1.php` — 0-0/32-0/33-0/34-0 加 flags + 头部语义注释
- `include/system.func.php` — mode&2 删写死三行→lockbranch 循环+flags 提取+save 前派生；mode&16 落点改排除表；add_once_area 两处传送改 `$noesc_pls`
- `include/global.func.php` — 新增 `migrate_mapinfo_flags()` + `derive_map_flaglists()`，load_gameinfo 接入
- `include/game/npcdict.func.php` — spawn_npc_all 随机出生改排除表（hidding 类避 `deepzone∪norandnpc`）
- `include/game/event.func.php` — valhalla_gate 传送落点改 `$noesc_pls`
- `gamedata/cache/resources_1.php` — 删写死 `$deepzones`
- `include/admin/resourcemng.php` + `templates/default/admin_resourcemng.htm` — 展开区加"特性flags"输入框（逗号分隔，可新增/清空/保留；99 池区不显示）+ 可用值说明行
- `migrate_gameresource.php` — 同步 flags 注入逻辑（防重跑丢失）
- 测试：`test_map_flags.php`（新增 35 项）、`test_admin_cfgmng.php`（+9 项 flags 用例）

## 行为等价性

- 旧局（flags 重构前开局）：migration 精确复刻旧行为——0=`deepzone+双排除`、32/33=`deepzone`、34=`deepzone+双排除+noesc_tp`（34 无 lockbranch：旧局本就锁分支），其余图无特性
- 新局数据：0 号补 `lockbranch`（0 号本就是锁分支图，消除删写死后 `$mapid[0]` 未定义 Notice）；32 号**不锁**（原代码 32 参与轮换，分支 1 为空占位由 fallback 兜底）
- 全图池落点 `rand(1,..)→rand(0,..)` 起点显式化：0 号由 flag 排除，覆盖面与旧版一致（33 图）

## 顺带修复的现存 bug

`rs_game(16+32)`（禁区扩张路径）mode&16 块缺 `global $mapinfo` → `$plsnum=0` → **每次禁区扩张每图物品实际不刷新**、99 池落点 `rand(1,-1)` 在 PHP7.4 swap 成 -1/0/1。已在 mode&16 补 global，扩张时物品刷新恢复正常（开局路径本就正常，不受影响）。

## 测试结果

- `php test_map_flags.php` — 35 项全过：数据断言 / lockbranch 锁分支 / 组装提取 / 分支级差异 / 迁移复刻 / 派生 4 表 / 异常降级 / 2 万次落点蒙特卡洛
- `php test_admin_cfgmng.php` — 全过（含 flags 保存/清空/保留/新增/渲染/99 池隔离 9 项）
- `php test_spawn_config_equiv.php`、`php test_tpl_compile.php` — 回归全过

## 注意事项

- flags **仅对新开局生效**（开局提取进 mapinfo 快照）；进行中的局靠 migration 补默认
- 编辑器改 flags 后下局才体现
- 全图池/旧局 migration 均无 `lockbranch` 干扰派生表（lockbranch 仅 rs_game 开局消费）
