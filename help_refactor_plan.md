# 帮助页面重构计划

## 现状

- `help.php` 是一个超长单页面，包含：战斗说明、道具说明、武器属性、NPC简介、合成表、物品掉落表等
- 导航通过 `help.php#锚点` 跳转，所有内容一次性加载
- `header.htm:56` 中帮助入口是普通 `<a href="help.php">` 链接
- 空想梦境入口已用 `span.drop` + `.dropdown-menu` 实现悬浮下拉（`header.htm:57-66`）

## 目标

将帮助入口改为悬浮下拉菜单，提供多个子页面入口：
- NPC百科
- 地图百科
- 玩家帮助（战斗/道具等基础说明）
- 合成表
- 物品掉落表（已有 `itemhelp.php`）

## 方案

### 方案A：纯锚点（最小工作量）
- 改 `header.htm` 1处：help 链接改成 `span.drop` 下拉
- 菜单项链接到 `help.php#NPC简介`、`help.php#道具合成`、`itemhelp.php` 等
- 零PHP改动，纯HTML

### 方案B：独立页面（推荐，后续升级）
- 新建 `npchelp.php`：从 `help.php` 拆出NPC百科（加载辞典 + `npcinfohelp.htm`）
- 新建 `maphelp.php`：地图百科（加载 `mapresource_1.php` + 新模板）
- 合成表拆出或继续用锚点
- 改 `header.htm`：下拉菜单链接到各自独立页面

### 方案C：Tab切换
- `help.php` 加 URL参数 `?tab=npc` / `?tab=map` / `?tab=mix` 控制显示内容
- 模板条件渲染对应区块

## 现有基础设施

- CSS：`style_20190718.css:606-637` 已有 `.drop` / `.dropdown-menu` 纯CSS悬浮下拉
- HTML模式：`header.htm:57-66` 空想梦境已用此模式
- `help.php:21-40` 已从 `$npcdict` 构建 `$npcinfo` 兼容结构
- `npcinfohelp.htm` 模板已适配新 `$npcinfo` 结构

## 待决

- 地图百科需要新模板和数据展示设计
- 合成表是否需要独立页面还是继续锚点
- 先用方案A快速上线，后续升级到方案B
