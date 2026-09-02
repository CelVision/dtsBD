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

### 2026-09-02 管理界面：gameresource/npcdict 配置编辑器（权限8）

新增两个管理入口（`admin.php` `$admin_cmd_list`，均权限8，菜单在"系统环境"列）：`resourcemng`（地图资源配置）与 `npcdictmng`（NPC辞典配置），均为展开表格式（外层编号+名字，点"展开"出编辑表）。

- **`include/admin/cfgfile.func.php`**：共用函数——`regenerate_gameresource_file()`/`regenerate_npcdict_file()`（var_export整文件重建，两文件均为纯数据文件）、`admin_cfg_decode()`（还原gstrfilter的htmlspecialchars）、`admin_cfg_typed()`（保持原字段类型写入）、`admin_map_itemrows()`/`admin_cfg_pagebtns()`（物品行HTML+分页导航）、`admin_npcdict_edit_table()`（NPC属性编辑表，仿npcmng布局）。
- **resourcemng**：每分支可编辑 plsinfo/xyinfo/bg/areainfo/isindoor/events/npc(typeId列表) 与物品掉落表（增删行）；**map 99不是地图**——单独渲染为"全图随机刷新池"区块（仅NPC+物品表）；物品表超过100行分页（99池515行会超过PHP max_input_vars=1000导致提交截断），保存采用合并语义：只处理POST中出现的行号，未提交的分页行保持原样。
- **npcdictmng**：每NPC条目可编辑基础属性/六熟练/装备6件套/包裹7格/描述/clubskillpara(JSON校验，非法输入拒绝并提示)；name/typeId/source/pass为结构键不可编辑。
- 保存流程：读当前房间文件→内存改→var_export重建→adminlog。**保存前自动备份**（`admin_cfg_backup()`把当前文件复制为`<file>.bak`，已为_1文件生成初始备份）；两个界面均有"从备份恢复"按钮（confirm确认，`restore`命令把`.bak`复制回当前文件，撤销最近一次保存）。类型保持：原字符串存字符串、原整数存整数，未改动字段原样保留（areainfo含`\"`的旧转义在保存时被规范化为`"`，属无害清理）。
- **测试**：`test_admin_cfgmng.php`（59项：重建回环一致性、列表/展开渲染、地图分支保存[改名/改行/删行/空修改]、全图随机池独立渲染+分页提交合并语义、NPC属性/clubskillpara保存、非法JSON拒绝、备份生成+恢复回滚验证）。**教训：临时测试文件路径必须显式指定（如`_98`），严禁`config()`——其_98→_1回退曾导致误删真实_1文件，已从git恢复并加路径守卫**。`test_tpl_compile.php`扩展至8个模板。

### 2026-09-02 开局刷新池修复 — 红暮双版本随机bug

**问题**：npcdict把 npc_1.php(sub)/addnpc_1.php(asub)/evonpc_1.php(esub) 三源合一后，`spawn_npc_all()`开局对typeId 1在 **红暮-自动托管(sub) 与 强版红暮(asub) 之间随机二选一**，约一半开局强版提前登场且自动托管缺席；type 14/21同理可能开局刷出esub进化目标（战斗模式梦美、黑色奪魂曲_evo等）。原版语义：开局只遍历npc_1.php，asub仅由addnpc()（如破灭之诗的`addnpc(1,0,1)`）召唤，esub仅作evonpc目标。

**修复**：`npcdict.func.php` 新增 `get_npc_init_pool($type)` — 同组优先取sub来源；纯asub组（92种火，字典全组标asub）回退用全部asub条目；esub与同组asub不参与开局刷新。`spawn_npc_all()` 与 `maphelp.php`/`test_maphelp_data.php` 的NPC展示改用该池。破灭之诗处理器（`item.func.php:2182`）无需改动：其 `DELETE 红暮-自动托管` + `addnpc(1,0,1)`→强版红暮 的链路本就正确。

**验证**：新增 `test_init_pool.php`（11项全过：type1池仅自动托管、100次选择确定、type14=3基础/21=10无evo、92回退+exclude后5种火、addnpc(1,0)指强版、进化目标仍可取数、自动托管数值与原版npc_1.php一致）；maphelp页无月之影NPC行仅"红暮-自动托管 ×1"，随机池NPC只显示3基础形态；`test_spawn_config_equiv.php` 等旧测试不受影响。

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

## evonpc 进化机制详细分析

### 触发开关

进化触发点在 `revcombat.func.php:612`：

```php
if($pd['club'] == 99 && $pd['type'])
{
    $npcdata = evonpc($pd['type'], $pd['name']);
    if($npcdata) {
        // 进化成功 → NPC 满血复活，继续战斗
        return 0;
    }
    // 进化失败（无进化模板）→ 走正常死亡流程
}
```

**开关是 `club == 99`**（"第一形态"社团）。只有 `club=99` 的 NPC 被打死后才会尝试进化。`club=99` 在 `clubskills_1.php:33` 定义了基础技能 `Array('s_hp','s_ad','f_heal')`。

### 哪些 NPC 有 club=99

| 来源文件 | 行号 | typeId | NPC名称 | 说明 |
|---------|------|--------|---------|------|
| npc_1.php | 120 | 14 | 父类（梦美/叶留佳/静流） | 女主三人组，开局刷新 |
| npc_1.php | 1092 | 21 | 黑色奪魂曲（sub） | 武神，开局刷新 |
| addnpc_1.php | 1151 | 12 | 父类（Dark Force幼体） | 未名存在，addnpc召唤 |
| addnpc_1.php | 1811 | 89 | 是TSEROF啦！（sub） | 残像回声，addnpc召唤 |
| addnpc_1.php | 1932 | 89 | 高中生·白神（sub） | 残像回声，addnpc召唤 |

### 进化链

进化数据在 `evonpc_1.php` 中，结构为 `$enpcinfo[typeId][当前NPC名] = 进化后完整属性`。

**typeId=12（Dark Force）：**
```
"Dark Force幼体" (club=99) → "Dark Force" (club=0, 终止)
```

**typeId=14（女主三人组）：**
```
"讲解员 梦美" (club=99) → "战斗模式 梦美" (club=4, 终止)
"喧哗少女 叶留佳" (club=99) → "本气（？） 叶留佳" (club=4, 终止)
"风纪委员 静流" (club=99) → "守卫者 静流" (club=98, 终止)
```

**typeId=21（武神）：**
```
"黑色奪魂曲" (club=99) → "黑色奪魂曲" (club=9, 终止)
```
注意：进化后名字不变，但 club 从 99 变成 9，下次被杀不再触发进化。

**typeId=89（残像回声，多级进化）：**
```
"是TSEROF啦！" (club=99) → "是TSEROF啦！" (club=99, 可继续进化)
  → 但 evonpc_1.php 中没有 "是TSEROF啦！" 的二次进化模板 → 实际终止

"高中生·白神" (club=99) → "白神·讨价还价" (club=99, 可继续进化)
  → "白神·讨价还价" (club=99) → "白神·接受" (club=19, 终止)
```

### 进化终止条件

进化链通过 **club 值变化** 来控制是否继续：
- 进化后 `club` 仍为 99 → 下次被杀会再次触发 `evonpc()`，但如果 `evonpc_1.php` 中没有对应名称的模板，`evonpc()` 返回 false，走正常死亡
- 进化后 `club` 变为其他值（0/4/9/19/98）→ 下次被杀不触发进化，直接死亡

### evonpc() 与 rs_game()/addnpc() 的关键区别

| 维度 | rs_game() / addnpc() | evonpc() |
|------|---------------------|----------|
| 数据来源 | `$npcinfo` / `$anpcinfo` | `$enpcinfo` |
| 继承机制 | 三层 `array_merge($npcinit, 父类, 子类)` | **无继承**，直接用完整属性 |
| 索引方式 | `[typeId]['sub'][idx]` | `[typeId][npcName]` |
| club 处理 | `changeclub($npc['club'], $npc)` 初始化社团技能 | 手动遍历 `$club_skillslist[$npc['club']]` 初始化 |
| DB 操作 | `array_insert`（新建NPC） | `UPDATE`（修改已有NPC） |
| 展平 | 数据是增量覆盖，运行时展平 | **数据已预展平**（父类+子类已合并） |

**关键发现：** `evonpc_1.php` 的数据本质就是 `array_merge($npcinit, 父类, 子类)` 的结果——已经展平了。`evonpc()` 函数里没有 `array_merge`，直接拿来做 DB UPDATE。

---

## 策略B方案（数据合并）

### 核心思路

1. **`npc_1.php` + `addnpc_1.php` 结构完全一致**，合并只需处理 6 个 typeId 冲突
2. **`evonpc_1.php` 是预展平数据**，不需要统一结构，原样挂载即可
3. 模板 `npcinfohelp.htm` 的 `sub`/`asub`/`esub` 访问方式可以保留

### 分步实施

#### B1：合并 npc_1.php + addnpc_1.php（低风险）

**统一结构设计：**
```php
$npctemplates[typeId] = array(
    // 父类属性（原 npc_1.php 或 addnpc_1.php 的顶层属性）
    'mode' => 1, 'num' => 3, 'club' => 99, 'mhp' => 7500, ...
    // 开局刷新子类（原 npc_1.php 的 sub）
    'sub' => array(0 => array('name' => '...', ...)),
    // 动态刷新子类（原 addnpc_1.php 的 sub）
    'asub' => array(0 => array('name' => '...', ...)),
);
```

**typeId 冲突处理（6个）：**

| typeId | npc_1.php | addnpc_1.php | 处理方式 |
|--------|-----------|-------------|---------|
| 1 | 红暮（弱版，mhp=7500） | 红暮（强版，mhp=75000） | 保留 npc_1 版为父类+sub，addnpc 版放入 asub |
| 15 | SANMA_TK（num=0） | 有定义 | 合并 sub，num 取 npc_1 版 |
| 19 | 真红暮/蓝凝（num=0） | 有定义 | 合并 sub，num 取 npc_1 版 |
| 89 | 残像回声（7个sub） | 残像回声（11个sub） | 保留 npc_1 sub，addnpc sub 放入 asub |
| 90 | 各路党派（4个sub） | 各路党派（4个sub） | 保留 npc_1 sub，addnpc sub 放入 asub |
| 92 | 种火（5个sub） | 种火（5个sub） | 保留 npc_1 sub，addnpc sub 放入 asub |

**改动文件：**
- 新建统一数据文件（合并 ~120KB 数据）
- `system.func.php`：`rs_game()` 遍历 `$npctemplates[$i]['sub']`；`addnpc()` 从 `$npctemplates[$type]['asub']` 取
- `resources.func.php`：`get_npcinit()` / `get_addnpcinfo()` 适配
- `help.php`：合并逻辑简化（sub/asub 已在同一数组中）
- `itemplace.func.php`：`get_npc_helpinfo()` 和 `get_item_npcdrop()` 合并逻辑简化
- `printitm.func.php`：合并逻辑简化

**不改：**
- `evonpc_1.php` 保持独立
- `evonpc()` 函数不改
- `npcinfohelp.htm` 模板不改（sub/asub key 名不变）
- `item.func.php` 中 17 处 `addnpc()` 调用不改

**预估工作量：~8h**

#### B2：将 evonpc_1.php 挂载到统一数据（低风险）

**做法：** 在统一数据文件中增加 `evolve` 字段：
```php
$npctemplates[14]['evolve'] = array(
    '讲解员 梦美' => array(...),  // 原 evonpc_1.php 的数据
    '喧哗少女 叶留佳' => array(...),
    ...
);
```

**改动文件：**
- 统一数据文件：追加 evolve 数据
- `system.func.php`：`evonpc()` 从 `$npctemplates[$type]['evolve'][$name]` 取
- 删除 `evonpc_1.php`

**不改：**
- `evonpc()` 的进化逻辑（无继承、DB UPDATE、技能初始化）全部不变
- 模板中 `esub` 的访问方式不变（`help.php` 仍从 evolve 字段构建 esub）

**预估工作量：~3h**

### 总工作量：~11h（B1 8h + B2 3h）

### 策略B对终极目标（方案C）的价值评估

**结论：性价比不高。** 策略B只是物理合并三个文件，保留 sub/asub/evolve 分支和 num/pls/mode 刷新参数。方案C要拆掉这些东西，策略B全保留了。唯一的副产品是摸清 typeId 冲突，但这用一个验证脚本就能拿到。

**建议跳过B，直接规划C。** 阶段A（统一 include 入口）对方案C有用——所有调用点已收敛到一个入口。

---

## 方案C：NPC辞典化 + 统一spawn系统（最终目标）

### 设计目标

- NPC 数据是纯属性辞典：`{ npcId: { 属性... } }`，不含 num/pls/mode 等刷新逻辑
- 刷新由统一 spawn 系统管理，传入 npcId + 数量 + 位置
- key 索引，不需要 sub/asub/evolve 分支
- 进化关系在辞典中用显式字段表达（如 `evolve_to`），不依赖 club==99 隐式触发
- 新系统独立开发，完成后再合并替换旧系统

### 当前 typeId 冲突清单

以下 typeId 同时出现在 `npc_1.php`（$npcinfo）和 `addnpc_1.php`（$anpcinfo）中：

#### typeId=1（红暮）

| 来源 | sub | NPC名称 | mhp | club | 说明 |
|------|-----|---------|-----|------|------|
| npc_1.php | sub[0] | 红暮-自动托管 | 7500 | 4 | 开局弱版，num=1 |
| addnpc_1.php | sub[0] | 红暮 | 75000 | 4 | addnpc强版，num=1 |

**性质：** 同一角色的弱版/强版，属性差距大（mhp 10倍，def 10倍）

#### typeId=15（抹杀使徒）

| 来源 | sub | NPC名称 | num | 说明 |
|------|-----|---------|-----|------|
| npc_1.php | sub[0] | 【SANMA_TK】 | 0 | 开局不刷新，仅help展示 |
| addnpc_1.php | sub[0] | 【SANMA_TK】 | 1 | addnpc召唤 |

**性质：** 同一NPC，npc_1版num=0（不刷），addnpc版num=1（召唤刷）。数据基本相同。

#### typeId=19（真红暮/蓝凝）

| 来源 | sub | NPC名称 | num | club | 说明 |
|------|-----|---------|-----|------|------|
| npc_1.php | sub[0] | 红暮 | 0 | 98 | 开局不刷新 |
| npc_1.php | sub[1] | 蓝凝 | 0 | 19 | 开局不刷新 |
| addnpc_1.php | sub[0] | 红暮 | 1 | 98 | addnpc召唤 |
| addnpc_1.php | sub[1] | 蓝凝 | 1 | 10 | addnpc召唤 |

**性质：** 同一角色对，npc_1版num=0（不刷），addnpc版num=1（召唤刷）。蓝凝的club不同（19 vs 10）。

#### typeId=90（小兵）

| 来源 | sub | NPC名称 | num | mhp | att | 说明 |
|------|-----|---------|-----|-----|-----|------|
| npc_1.php | sub[0-3] | 数据残影-A/B/C/D | 280 | 550 | 120 | 开局小兵，4种轮询 |
| addnpc_1.php | sub[0] | 迷之搬运工 | 1 | 520 | 80 | addnpc召唤的特殊小兵 |

**性质：** 完全不同的NPC，碰巧共用typeId=90。属性和装备都不同。

#### typeId=92（种火）

| 来源 | sub | NPC名称 | num | 说明 |
|------|-----|---------|-----|------|
| npc_1.php | sub[0-4] | ✦覆唱的篝火, ✦爱恋的埋火, ✦悲恸的永火, ✦执念的残火, ✦希望的焰火 | 100 | 开局种火，5种轮询 |
| addnpc_1.php | sub[0-4] | 同上（5种） | 100 | addnpc召唤种火 |
| addnpc_1.php | sub[10] | ✦真实的火种 | - | addnpc额外子类 |

**性质：** sub[0-4] 名称相同，父类属性几乎相同（仅装备技能标记微小差异：arbsk 'H' vs 'a'，arhsk 'A' vs 'B'）。addnpc版多一个 sub[10] 额外种火。

### 所有 typeId 总览

**npc_1.php $npcinfo 的 typeId：** 1, 14, 15, 19, 20, 21, 22, 24, 26, 88, 90, 91, 92

**addnpc_1.php $anpcinfo 的 typeId：** 1, 2, 4, 5, 6, 7, 9, 11, 12, 13, 15, 19, 25, 89, 90, 92

**evonpc_1.php $enpcinfo 的 typeId：** 12, 14, 21, 89

**npc_1.php $npcdescription 的 typeId：** 1, 4, 5, 6, 9, 11, 13, 14, 15, 19, 21, 22, 24, 88, 89, 90, 92
（注意：$npcdescription 覆盖了 npc_1 和 addnpc_1 两边的 NPC 描述，是统一的描述库）

**冲突 typeId（同时出现在 $npcinfo 和 $anpcinfo）：** 1, 15, 19, 90, 92

### 方案C实施计划（独立开发，完成后合并）

#### C1：设计辞典结构

纯属性辞典，每个 NPC 一个唯一 npcId：

```php
$npcdict = array(
    'hongmu_weak' => array(
        'name' => '红暮-自动托管', 'icon' => 7, 'gd' => 'f',
        'mhp' => 7500, 'att' => 750, 'def' => 750, ...
        'club' => 4, 'clubskill' => array(...),
        'evolve_to' => null,  // 无进化
    ),
    'hongmu_strong' => array(
        'name' => '红暮', 'icon' => 7, 'gd' => 'f',
        'mhp' => 75000, 'att' => 750, 'def' => 7500, ...
        'club' => 4, 'clubskill' => array(...),
        'evolve_to' => null,
    ),
    'mengmei_1' => array(
        'name' => '讲解员 梦美', ...
        'club' => 99,
        'evolve_to' => 'mengmei_2',  // 进化到战斗模式
    ),
    'mengmei_2' => array(
        'name' => '战斗模式 梦美', ...
        'club' => 4,
        'evolve_to' => null,  // 进化终止
    ),
);
```

**设计要点：**

- npcId 用语义化字符串（如 `hongmu_weak`），不用数字 typeId
- 不含 num/pls/mode 等刷新参数
- 进化关系用 `evolve_to` 显式指向下一个 npcId，不依赖 club==99
- 描述信息（$npcdescription）合并到辞典中或单独维护

#### C2：设计统一 spawn 接口

```php
function spawn_npc($npcId, $num = 1, $pls = 99, $options = array()) {
    // 1. 从辞典取属性
    // 2. 复制 $num 份，分配位置
    // 3. 社团技能初始化
    // 4. 写入 DB
    // 返回生成的 pid 列表
}
```

**刷新配置独立于辞典：**

```php
// 开局刷新配置
$spawn_config = array(
    'hongmu_weak' => array('num' => 1, 'pls' => 0),
    'mengmei_1'   => array('num' => 3, 'pls' => 99),
    'mob_a'       => array('num' => 70, 'pls' => 99),  // 数据残影-A
    // ...
);
```

#### C3：迁移数据

- [x] 从 npc_1.php 提取所有 $npcinfo 的 NPC → 展平 array_merge → 辞典条目（13个typeId，全部完成）
- [x] 从 addnpc_1.php 提取所有 $anpcinfo 的 NPC → 展平 → 辞典条目（16个typeId，**缺 typeId 92 的 ✦真实的火种**）
- [x] 从 evonpc_1.php 提取所有 $enpcinfo 的 NPC → 辞典条目（4个typeId: 12/14/21/89，进化形态已展平到辞典）
- [x] 建立 evolve_to 映射关系（$npc_evolve，4个typeId共8条映射）
- [~] 提取 $npcdescription → 辞典 description 字段（75/125有description，npc_1.php原$npcdescription仅17条，部分NPC无描述）
- [x] 生成 spawn_config（从原 num/pls 字段提取，已实现为 $npc_spawn_config + $npc_sub_pls）

**C3 验证结果 (2025-09-01)**：

- 冲突typeId合并正确：typeId 1/15/19/90/92 的 init+add 条目已合并到同一typeId下
- num/pls 已从辞典全部注释掉（各117处），0残留
- 进化形态在辞典中以原名或 `_evo` 后缀存在（如 黑色奪魂曲_evo、是TSEROF啦！_evo）
- typeId 14 辞典有6条（3原始+3进化：战斗模式 梦美/本气（？）叶留佳/守卫者 静流）
- typeId 89 辞典有10条（7原始+3进化：是TSEROF啦！_evo/白神·讨价还价/白神·接受）

**C3 遗留问题**：

- ~~typeId 92 addnpc 的 ✦真实的火种（第6个sub）未展平到辞典，需补充~~ → 已补充 (2025-09-01)
- ~~description 字段覆盖率 60%（75/125），原 $npcdescription 仅17条，部分NPC本身无描述~~ → 已合并 (2025-09-01)

#### $npcdescription 合并完成 (2025-09-01)

- $npcdescription 的 59 条描述+count 已全部并入 npcdict_1.php 的各 NPC 条目
- 每个 NPC 条目新增 `description` 和 `help_count` 字段
- 进化形态的描述通过 $npc_evolve 映射正确对应（如 typeId 14 的"讲解员 梦美"→"战斗模式 梦美"）
- 最终覆盖率：80/126 有描述（63%），无描述的主要是 typeId 20 英灵（34个）和进化形态
- 同时为每个 NPC 条目添加了 `source` 字段（sub/asub/esub），用于 help.php 分组

#### C4 进度 (2025-09-01)

**help.php + npcinfohelp.htm 已改写完成**：

- `help.php`：从 `include config('npctemplate')` 改为 `include config('npcdict')`，从辞典构建兼容的 `$npcinfo` 结构（sub/asub/esub 分组）
- `npcinfohelp.htm`：
  - description 直接从 `$npcinfo[$kind][$ksub][$key]['description']` 读取（不再依赖 `$npcdescription`）
  - count 从 `$npcinfo[$kind][$ksub][$key]['help_count']` 读取
  - `is_numeric($key)` 改为 `isset($npcinfo[...]['name'])`（因为辞典用 NPC名称 作为 key 而非数字下标）
- `$npcinit = array()` 设为空数组（`get_npc_helpinfo` 引用它但辞典已展平无需父类模板）

**✦真实的火种处理**：

- 已展平到 npcdict_1.php typeId 92
- spawn_config['init'][92] 新增 `'exclude' => array('✦真实的火种')`，开局刷新时排除
- `spawn_npc_all()` 已支持 exclude 过滤逻辑

#### C4：改写调用方 — 已完成 (2025-09-01)

**改写的函数**：

- `rs_game()` mode&8：替换为 `spawn_npc_all($now)` 调用，旧代码注释保留
- `addnpc($type,$sub,$num,$time,$anpcdata,$pls_override)`：委托给 `addnpc_compat()` → `spawn_npc()`，旧代码注释保留
- `evonpc($type,$name)`：委托给 `evolve_npc()`，旧代码注释保留
- `help.php`：从 `include config('npctemplate')` 改为 `include config('npcdict')`
- `npcinfohelp.htm`：description/help_count 从 `$npcinfo` 直接读取

**addnpc_compat 兼容层修复**：

- 修复 `addnpc_compat()` 仅过滤 `source='asub'` 条目，与原 addnpc_1.php 的 sub 数组对齐
- 修复9个NPC的 source 标签（typeId 1/15/19/92 的条目从 sub 改为 asub）
- 补充缺失的 typeId 90 '迷之搬运工' 到辞典
- 补充 typeId 19 '红暮' 的 source 标签（第二次出现，脚本遗漏）
- `addnpc()` 签名增加 `$pls_override` 参数透传

**测试结果**：

- 所有修改文件 `php -l` 语法检查通过
- addnpc_compat 映射验证：16个typeId全部通过（typeId 11 为编码差异假阳性，typeId 92 sub[10] 从未被 addnpc 调用）
- 所有 addnpc() 调用方（item.func.php / revclubskills_extra.func.php / alive.php）参数兼容
- evonpc() 调用方（revcombat.func.php）参数兼容

**回退方法**：

备份文件位于各文件同目录的 `.bak_c4` 后缀：

- `include/system.func.php.bak_c4`
- `include/game/npcdict.func.php.bak_c4`
- `include/game/item.func.php.bak_c4`
- `include/game/revcombat.func.php.bak_c4`
- `include/game/revclubskills_extra.func.php.bak_c4`
- `alive.php.bak_c4`
- `help.php.bak_c4`
- `templates/default/npcinfohelp.htm.bak_c4`
- `gamedata/cache/npcdict_1.php.bak_c4`

回退命令（PowerShell）：

```powershell
Copy-Item include\system.func.php.bak_c4 include\system.func.php -Force
Copy-Item include\game\npcdict.func.php.bak_c4 include\game\npcdict.func.php -Force
Copy-Item help.php.bak_c4 help.php -Force
Copy-Item templates\default\npcinfohelp.htm.bak_c4 templates\default\npcinfohelp.htm -Force
Copy-Item gamedata\cache\npcdict_1.php.bak_c4 gamedata\cache\npcdict_1.php -Force
```

或仅回退单个函数：删除 `system.func.php` 中的 `/* OLD CODE PRESERVED FOR ROLLBACK` 注释块，恢复旧代码即可。

#### C5：合并替换

- 新系统通过测试后，替换旧系统
- 删除 npc_1.php / addnpc_1.php / evonpc_1.php / npctemplate_1.php
- 删除 $npcinfo / $anpcinfo / $enpcinfo / $npcinit 变量

### 需要用户决定的事项

1. **冲突 typeId 的 npcId 分配** — 是否给弱版/强版分配不同的新 ID？
2. **npcId 命名规范** — 用字符串（如 `hongmu_weak`）还是数字（如 `101`）？
3. **进化关系是否保留 club==99 机制** — 还是用 evolve_to 完全替代？
4. **spawn_config 放在哪里** — 同一个辞典文件？单独配置文件？数据库？

---

## 方案C — 阶段2：新函数文件创建 (2025-09-01)

### 完成内容

创建了 `include/game/npcdict.func.php`，实现方案C的核心函数，与旧系统并行运行。

#### 函数清单

| 函数 | 用途 | 替代 |
|------|------|------|
| `get_npcdict()` | 加载辞典（单例缓存） | `get_addnpcinfo()` + `get_npcinit()` |
| `get_npcdict_names($type)` | 取typeId下所有NPC名 | — |
| `get_npcdict_data($type, $name)` | 取单个NPC展平数据 | 三层 `array_merge` |
| `spawn_npc($type, $name, $num, ...)` | 按名字生成NPC | `addnpc($type, $sub, $num, ...)` |
| `spawn_npc_random($type, $num, ...)` | 随机选一个生成 | 随机 `addnpc` 调用 |
| `spawn_npc_all($time)` | 开局批量刷新 | `rs_game()` mode&8 |
| `evolve_npc($type, $name)` | NPC进化 | `evonpc($type, $name)` |
| `addnpc_compat($type, $sub, ...)` | 旧参数兼容层 | 渐进迁移用 |

#### 索引方式变更

- 旧：`addnpc(89, 0, 1)` — typeId + 数字下标
- 新：`spawn_npc(89, '电掣部长 米娜', 1)` — typeId + NPC名称字符串

#### 辞典字段清理

- `pls`（刷新地点）已从 `npcdict_1.php` 中注释掉，因为方案C的目标是辞典只存纯属性，刷新位置由独立 spawn 配置管理
- `num`（开局刷新总数）含义：**typeId级别的总数**，不是每个子NPC的独立数量。展平时被错误复制到每个子NPC条目中，后续应移出辞典至 spawn 配置

#### `num` 语义问题（2025-09-01 发现）

**原始 `rs_game()` mode&8 逻辑**（`system.func.php:284-306`）：

```php
foreach ($npcinfo as $i => $npcs){
    if (sizeof($npcs['sub']) > $npcs['num']) shuffle($npcs['sub']);
    for($j = 1; $j <= $npcs['num']; $j++) {
        $sub = $j % sizeof($npc['sub']);
        $npc = array_merge($npcinit, $npcs, $npcs['sub'][$sub]);
        // 写入DB
    }
}
```

- `num` 是父类（typeId）级别的属性，表示该 typeId 总共生成多少个 NPC
- 刷新时通过 `$j % subnum` 轮询分配给各子 NPC
- sub 数量 > num 时：shuffle 后取前 num 个，每个生成1个
- sub 数量 < num 时：部分子 NPC 重复生成

**展平产生的问题**：`gen_npcdict.php` 将父类 `num` 复制到每个子 NPC 条目，导致 typeId=20（20个子NPC，父类 num=10）的每个子NPC都显示 `num=10`，语义变为"每个NPC各刷新10个"，实际应为"该typeId总共刷新10个，轮询分配"。

**结论**：`num` 和 `pls` 一样属于 spawn 配置，不属于 NPC 纯属性字典。`spawn_npc_all()` 已正确从第一个条目读取 typeId 级别的 `num`，但该字段应从辞典中移出。

#### 迁移路径

1. 调用方先改用 `addnpc_compat()`（不改参数，内部转换）
2. 逐步替换为 `spawn_npc()` 用名字索引
3. `rs_game()` mode&8 替换为 `spawn_npc_all()`
4. `evonpc()` 替换为 `evolve_npc()`
5. 删除旧函数

#### sub 级别 `num`/`pls` 覆盖问题（2025-09-01 发现）

**typeId 92（篝火）结构**：

```
父类: mode=1, num=100, pls=注释掉(//'pls' => 99)
  sub 0: ✦覆唱的篝火, num=20, pls=Array(2,15)
  sub 1: ✦爱恋的埋火, num=20, pls=Array(3,22)
  sub 2: ✦怜悯的永火, num=20, pls=Array(18,23)
  sub 3: ✦执念的残火, num=20, pls=Array(20,24)
  sub 4: ✦希望的焰火, num=20, pls=Array(12,29)
```

**关键发现**：
- sub 级别有自己的 `num` 和 `pls` 字段，`array_merge` 后会覆盖父类值
- sub 的 `pls` 是固定位置数组（如 `Array(2,15)`），刷新时 `array_rand` 择一
- sub 的 `num=20`，5×20=100=父类 num，疑似每个 sub 各刷20个
- **但 `rs_game()` 代码实际不读取 sub 级别的 `num`**，只按 `$j % $subnum` 轮询
- **用户指出**：sub 4（✦希望的焰火）按设计不应在开局刷新，但当前轮询逻辑会刷出它
- 篝火应刷到固定位置（sub 的 pls 数组），而非随机

### 待决问题

- sub 级别的 `pls` 覆盖已通过 `$npc_sub_pls` 处理（typeId 92 篝火）
- spawn 配置目前写死在 `npcdict.func.php`，后续可考虑移至独立配置文件
- `spawn_npc_all()` 中 DELETE 逻辑是否需要更精细的条件

#### spawn 配置实现 (2025-09-01)

`pls` 和 `num` 已从 `npcdict_1.php` 中全部注释掉（各117处）。

在 `npcdict.func.php` 中新增刷新配置：

- `$npc_spawn_config['init']` — 开局刷新配置（13个typeId），从 `npc_1.php` 父类提取
- `$npc_spawn_config['add']` — 动态召唤配置（16个typeId），从 `addnpc_1.php` 父类提取
- `$npc_sub_pls` — sub级别pls覆盖（typeId 92 篝火5个sub的固定刷新位置数组）
- `get_npc_spawn_config($type, $mode)` — 读取typeId级spawn配置
- `get_npc_sub_pls($type, $name)` — 读取sub级pls覆盖

函数更新：
- `spawn_npc()` — pls 优先级：`pls_override > sub_pls > spawn_config('add') > 默认随机`
- `spawn_npc_all()` — num/pls 从 `spawn_config('init')` 读取，sub级pls从 `$npc_sub_pls` 读取

---

## 回退指南

### 回退状态机心跳
1. `common.inc.php`：恢复98-111行的内联状态机代码（从git历史恢复）
2. `chat.php`：删除12-18行的锁+load_gameinfo+advance_gamestate代码
3. `system.func.php`：删除1307-1412行的advance_gamestate函数

### 回退template_render修复
1. `command.php`：将5处 `ob_start()/include/ob_get_clean()` 改回 `template_render()` 调用
