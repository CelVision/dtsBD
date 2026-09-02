# NPC/地图物品/刷新系统优化计划

## 项目目标

将NPC刷新规则从硬编码抽离到数据配置中，统一NPC模板库，实现NPC与地图物品刷新逻辑的统一管理。

---

## 现有架构分析

### 三套NPC配置文件

#### 1. `npc_1.php` (2617行) — 初始NPC模板

**结构：**
```
$npcinit          — 全局默认属性模板（所有NPC的基类属性）
$npcinfo[typeId]  — 按typeId组织的NPC大类定义
  ├─ mode         — 1=固定sub顺序, 2=随机sub
  ├─ num          — 生成数量
  ├─ 共享属性      — mhp/att/def/lvl/club/装备等（父类属性）
  └─ sub[idx]     — 子类变体数组（小类）
      ├─ name     — NPC名称
      ├─ icon     — 头像
      ├─ 覆盖属性  — 只写与父类不同的字段，array_merge时覆盖父类
      └─ ...
$npcdescription[typeId][sub][idx] — NPC描述文本（纯展示用）
```

**大类(typeId)列表（来自npc_1.php）：**
| typeId | typeinfo名称 | sub数量 | mode | 说明 |
|--------|-------------|---------|------|------|
| 1 | 红杀将军 | 1 | 1 | 红暮，开局固定刷新 |
| 14 | 数据碎片 | 3 | 1 | 梦美/叶留佳/静流，开局刷新，被杀后进化 |
| 15 | 抹杀使徒 | 1 | 1 | SANMA_TK，num=0不自动刷新 |
| 19 | 参战者 | 2 | 1 | 真红暮/蓝凝，num=0不自动刷新 |
| 20 | 英雄 | 34 | 2 | 英灵殿NPC，随机选sub |
| 21 | 武神 | 10 | 2 | 英灵NPC，随机选sub |
| 22 | 天神 | 2 | 1 | 冴月麟MK-II/四面BOSS |
| 24 | 巫师 | 3 | 1 | 开发组NPC |
| 88 | ■■ | 4 | 1 | SCP系列 |
| 89 | 残像回声 | 7 | 1 | 种火相关，num=0 |
| 90 | 各路党派 | 4 | 1 | 小兵，num=75 |
| 91 | 各路党派 | 4 | 1 | 小兵（另一批） |
| 92 | 种火 | 5 | 1 | 种火，num=30 |

#### 2. `addnpc_1.php` (2903行) — 动态追加NPC模板

**结构与npc_1.php完全相同**：`$anpcinfo[typeId]` + `sub[idx]`

**与npc_1.php的区别：**
- typeId 1（红暮）：属性更强（mhp 75000 vs 7500，def 7500 vs 750，wepe 1750 vs 1280）
- typeId 2（全息幻象）：npc_1.php中没有，仅addnpc有，8个sub变体
- typeId 4（拟似意识■）：npc_1.php中没有，仅addnpc有
- typeId 5（杏仁豆腐）：npc_1.php中没有，仅addnpc有
- typeId 6（黑幕）：npc_1.php中没有，仅addnpc有
- typeId 7（幻影执行官）：npc_1.php中没有，仅addnpc有
- typeId 9（红杀菁英）：npc_1.php中没有，仅addnpc有
- typeId 11（真职人）：npc_1.php中没有，仅addnpc有，6个sub
- typeId 12（未名存在）：npc_1.php中没有，仅addnpc有
- typeId 13（循环使者）：npc_1.php中没有，仅addnpc有，3个sub
- typeId 15（抹杀使徒）：与npc_1.php重复但有差异
- typeId 19（参战者）：与npc_1.php重复
- typeId 25（佣兵）：仅addnpc有，11个sub
- typeId 89（残像回声）：与npc_1.php重复，sub更多(11个 vs 7个)
- typeId 90（各路党派）：与npc_1.php重复
- typeId 92（种火）：与npc_1.php重复

**关键发现：typeId 1/15/19/89/90/92 在两个文件中都有定义，内容有差异**

#### 3. `evonpc_1.php` (553行) — NPC进化模板

**结构完全不同：**
```
$enpcinfo[typeId][npcName] — 按typeId+NPC名称索引
  ├─ 进化后的完整属性
  └─ ...
```

**与npc_1.php/addnpc_1.php的区别：**
- 不用sub数组，直接按NPC名称(name)作为key
- 每个条目是进化后的完整属性（不是增量覆盖）
- 进化链：如 type=14 的"讲解员 梦美" → 进化为"战斗模式 梦美"
- 支持多级进化：如 type=89 的"高中生·白神" → "白神·讨价还价" → "白神·接受"

**已定义进化的typeId：**
| typeId | 进化NPC数 | 说明 |
|--------|----------|------|
| 12 | 1 | Dark Force幼体 → Dark Force |
| 14 | 3 | 梦美/叶留佳/静流 → 战斗形态 |
| 21 | 1 | 黑色奪魂曲 → 进化形态 |
| 89 | 3 | 白神系列三级进化 |

### 父类/子类压缩机制

**核心逻辑在 `rs_game()` mode&8（`system.func.php:284-353`）：**

```php
foreach ($npcinfo as $i => $npcs) {
    $npc = array_merge($npcinit, $npcs);        // 第1层：npcinit基类 ← 父类属性
    $sub = $j % $subnum;
    $npc = array_merge($npc, $npcs['sub'][$sub]); // 第2层：父类 ← 子类覆盖属性
    $npc['hp'] = $npc['mhp'];
    $npc['sp'] = $npc['msp'];
}
```

**三层继承：**
1. `$npcinit` — 全局默认值（空名/0属性/空装备）
2. `$npcinfo[typeId]` — 大类属性（共享的mhp/att/def/装备等）
3. `$npcinfo[typeId]['sub'][idx]` — 小类属性（只写差异部分，如name/icon/wep）

**mode字段决定sub选择方式：**
- `mode=1`：按顺序取sub（sub 0, 1, 2, ...），num控制生成总数
- `mode=2`：随机选sub，num控制生成总数

**addnpc()函数（`system.func.php:867`）使用相同机制：**
```php
$anpc = array_merge($npcinit, $anpcinfo[$type]);
$anpc = array_merge($anpc, $anpcinfo[$type]['sub'][$sub]);
```

### 大类/小类编号体系

当前typeId就是大类编号，sub的idx就是小类编号。例如：
- **typeId=2（全息幻象）, sub=4** → "熵魔法传人 Howling"
- **typeId=90（各路党派）, sub=0** → 小兵类型0

用 `(typeId, subIdx)` 二元组可以唯一标识一个NPC小类模板。

### 刷新触发点

| 触发时机 | 调用 | 刷新内容 | NPC刷新? |
|---------|------|---------|---------|
| 游戏开始 | `rs_game(1+2+4+8+16+32)` | 地图+NPC+物品+商店 | ✅ mode&8 |
| 禁区扩张 | `rs_game(16+32)` | 物品+商店 | ❌ |
| 道具使用 | `addnpc(type,sub,num)` | 单个NPC | ✅ 事件驱动 |
| NPC被杀 | `evonpc(type,name)` | NPC进化 | ✅ 事件驱动 |

---

## 优化方案（分阶段）

### 第一阶段：统一NPC模板库（低风险）

**目标：** 合并三个NPC配置文件为一个，消除重复定义

**产出：** `npctemplate_1.php`

**结构设计：**
```php
$npctemplates = Array(
    // 大类编号 => 大类定义
    1 => Array(
        'name' => '红杀将军',
        'mode' => 1,
        'num' => 1,
        // 父类共享属性
        'pass' => 'bra', 'club' => 4, 'mhp' => 7500, ...
        'sub' => Array(
            // 小类编号 => 小类覆盖属性
            0 => Array('name' => '红暮-自动托管', 'icon' => 7, ...),
        ),
    ),
    ...
);

// 进化规则：用引用代替独立定义
$npc_evolve = Array(
    14 => Array(
        '讲解员 梦美' => Array('evolve_to' => '战斗模式 梦美', ...),
        ...
    ),
    ...
);
```

**兼容策略：**
- 保留 `$npcinfo` / `$anpcinfo` / `$enpcinfo` 变量名作为兼容别名
- `get_npcinit()` / `get_addnpcinfo()` 函数不改接口
- `addnpc()` / `evonpc()` 函数不改接口

### 第二阶段：地图数据中声明NPC刷新规则（中风险）

**目标：** 在 `mapresource_1.php` 中增加 `spawns` 字段

**结构设计：**
```php
$maps[2][0] = Array(
    'plsinfo' => 'RF高校',
    // ... 其他地图属性 ...
    'spawns' => Array(
        // 禁区次数 => 刷新列表
        0 => Array(
            Array(2, 4, 3),  // (大类, 小类, 数量) — 开局刷3个type=2 sub=4的NPC
        ),
        2 => Array(
            Array(2, 0, 6),  // 第2次禁区刷6个type=2 sub=0
        ),
    ),
);
```

**修改点：**
- `rs_game()` mode&8：从 `spawns` 读取刷新规则，替代遍历 `$npcinfo`
- `add_once_area()` → `rs_game()`：禁区扩张时也处理NPC刷新

### 第三阶段：迁移硬编码addnpc调用（较高风险）

**目标：** 将 `item.func.php` 中的硬编码 `addnpc()` 调用迁移到数据配置

---

## 变更日志

### 2026-08-28 状态机心跳 + template_render修复

#### 已完成

**1. 状态机抽离为 `advance_gamestate()` 函数**
- 文件：`include/system.func.php` (1307-1412行)
- 将 `common.inc.php` 中110行内联状态机逻辑封装为独立函数
- 包含：游戏准备/开始判定、禁区扩张/警告、停止激活/连斗判定、反挂机、gameover

**2. `common.inc.php` 玩家请求侧瘦身**
- 文件：`include/common.inc.php` (103-104行)
- 原110行内联状态机 → `load_gameinfo()` + `advance_gamestate()` 一行调用
- 玩家请求仍执行状态机作为兜底

**3. `chat.php` 加入状态机心跳**
- 文件：`chat.php` (12-18行)
- chat轮询时获取房间锁 → load_gameinfo → advance_gamestate → 释放锁
- 每15秒轮询自然驱动状态推进，玩家挂机也能推进游戏

**4. `command.php` template_render作用域bug修复**
- 文件：`command.php` (791-830行)
- 问题：`template_render()` 是函数，include在函数内执行时模板无法访问全局变量
- 修复：5处 `template_render()` 调用改回 `ob_start()` + `include template()` + `ob_get_clean()`
- 模板在顶层作用域执行，$hp/$name/$iconImg等变量正常访问

#### 待验证
- [ ] 游戏开局NPC正常生成
- [ ] 禁区扩张时物品正常刷新
- [ ] 玩家移动后头像/属性正常显示
- [ ] chat轮询状态推进正常

### 2026-08-28 NPC配置文件统一入口（策略A）

#### 已完成

**目标：** 将三个NPC配置文件（`npc_1.php`、`addnpc_1.php`、`evonpc_1.php`）的include统一为单一入口 `npctemplate_1.php`，业务逻辑零改动。

**1. 新建 `gamedata/cache/npctemplate_1.php`**
- 统一入口文件，内部 include 三个原始文件
- 使用 `include`（非 `include_once`）确保变量始终在调用者作用域中可用
- 通过 `config('npctemplate', $gamecfg)` 解析路径，兼容多gamecfg

**2. 更新 `include/resources.func.php`（2处）**
- `get_addnpcinfo()`：`config("addnpc")` → `config("npctemplate")`
- `get_npcinit()`：`config("npc")` → `config("npctemplate")`

**3. 更新 `include/system.func.php`（2处活跃 + 1处注释）**
- `rs_game()` mode&8 (277行)：`include_once config('npc')` → `include config('npctemplate')`
- `evonpc()` (994行)：`include_once config('evonpc')` → `include config('npctemplate')`
- `addnpc()` (875行)：注释块内的引用保持原样 `config('npc')`（死代码）

**4. 更新 `help.php`（3处→1处）**
- 3个 `include config('npc'/'addnpc'/'evonpc')` 合并为1个 `include config('npctemplate')`

**5. 更新 `include/game/itemplace.func.php`（3处→1处）**
- `get_item_npcdrop()` 中3个 include 合并为1个 `include config('npctemplate',1)`

**6. 更新 `include/devtools/printitm.func.php`（3处→1处）**
- 3个 `include_once config('npc'/'addnpc'/'evonpc')` 合并为1个 `include config('npctemplate')`

#### 验证结果
- ✅ PHP lint 通过（全部6个文件）
- ✅ 功能验证通过：`$npcinit`(96键)、`$npcinfo`(13 typeIds)、`$anpcinfo`(16 typeIds)、`$enpcinfo`(4 typeIds)、`$npcdescription`(17条) 均正确加载
- ✅ 无残留的 `config('npc'/'addnpc'/'evonpc')` 引用（注释块除外）

#### 未改动（策略B目标）
- 三个原始文件 `npc_1.php`、`addnpc_1.php`、`evonpc_1.php` 保持不变
- `$npcinfo`/`$anpcinfo`/`$enpcinfo` 变量结构未统一
- `npcinfohelp.htm` 模板未改动
- `item.func.php` 中硬编码 `addnpc()` 调用未迁移

#### 回退方法
1. 删除 `gamedata/cache/npctemplate_1.php`
2. 各文件中将 `config('npctemplate',...)` 改回原始的 `config('npc'/'addnpc'/'evonpc',...)`
3. `help.php`、`itemplace.func.php`、`printitm.func.php` 需要恢复为3个独立 include

---

## 回退指南

### 回退状态机心跳
1. `common.inc.php`：恢复98-111行的内联状态机代码（从git历史恢复）
2. `chat.php`：删除12-18行的锁+load_gameinfo+advance_gamestate代码
3. `system.func.php`：删除1307-1412行的advance_gamestate函数

### 回退template_render修复
1. `command.php`：将5处 `ob_start()/include/ob_get_clean()` 改回 `template_render()` 调用
