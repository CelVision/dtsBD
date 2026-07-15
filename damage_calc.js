// ======================== GAME CONFIG ========================
const CFG = {
  skillinfo:{N:'wp',P:'wp',K:'wk',G:'wg',C:'wc',D:'wd',F:'wf',J:'wg',B:'wc'},
  skill_dmg:{N:0.6,P:0.6,K:0.65,G:0.6,C:0.4,D:0.75,F:0.4,J:0.7,B:0.5},
  dmg_fluc:{N:15,P:15,K:40,G:20,C:5,D:25,F:10,J:10,B:10},
  def_kind:{P:'P',N:'P',K:'K',G:'G',J:'G',C:'C',B:'C',D:'D',F:'F'},
  hitrate_obbs:{N:80,P:80,K:75,G:70,C:70,D:60,F:85,J:10,B:65},
  hitrate_max:{N:90,P:90,K:85,G:95,C:96,D:70,F:96,J:98,B:90},
  hitrate_r:{N:0.025,P:0.025,K:0.025,G:0.05,C:0.25,D:0.02,F:0.1,J:0.2,B:0.15},
  counter_obbs:{N:75,P:85,K:85,G:50,C:75,D:0,F:95,J:50,B:30},
  rangeinfo:{N:3,P:3,K:3,G:7,C:5,D:0,F:1,J:8,B:5},
  pose_atk:[0,100,0,-25,25,-50,177,-77],
  pose_def:[0,25,0,-25,-25,-50,-333,77],
  pose_atk_active:1, pose_def_active:1,
  tactic_atk:[0,20,-25,25,-33],
  tactic_def:[0,-10,50,-15,0],
  tactic_atk_active:1, tactic_def_active:1,
  wth_atk:[10,10,0,-5,-10,-20,-15,0,0,7,30,-7,-20,-5,-10,-10,-10,10,5],
  wth_def:[10,30,0,0,-3,-15,-10,0,-20,-30,-50,-5,-20,-3,-20,5,-30,30,35],
  pls_atk:[10,0,0,-5,0,5,5,-5,-5,5,0,-15,-5,0,-10,0,0,5,-5,-15,-5,0,0,0,0,0,10,-10,5,0,0,0,-20,0,15],
  pls_def:[0,-10,0,0,-10,0,0,0,0,0,-10,5,0,-10,0,0,5,0,0,0,0,0,-10,-10,-10,0,0,0,5,5,5,0,0,-5,0],
  inf_att_p:{a:0.65,u:0.4},
  inf_def_p:{b:0.65,i:0.8,w:0.65},
  specialrate:{N:40,n:30,y:30,B:95,b:95},
  ex_attack:['p','u','i','d','e','w','f','k'],
  ex_def_kind:{p:'q',u:'U',f:'U',i:'I',k:'I',e:'E',w:'W',d:'D'},
  ex_good_wep:{p:'K',u:'G',i:'C',d:'D',e:'P',w:'D'},
  ex_good_club:{p:8,e:7},
  ex_base_dmg:{p:15,u:25,i:10,d:1,e:15,w:20,f:5,k:5},
  ex_max_dmg:{p:120,u:180,i:120,d:0,e:180,w:180,f:0,k:0},
  ex_wep_dmg:{p:10,u:5,i:12,d:2,e:10,w:12,f:4,k:5},
  ex_skill_dmg:{p:15,u:20,i:20,d:500,e:20,w:15,f:40,k:30},
  ex_dmg_fluc:{p:15,u:30,i:10,d:20,e:5,w:15,f:30,k:10},
  ex_inf:{p:'p',u:'u',i:'i',e:'e',w:'w',f:'u',k:'i'},
  ex_inf_punish:{p:2,u:1,i:0.75,d:1,e:1,w:1.5,f:1.5,k:1.5},
  ex_inf_r:{p:5,u:10,i:5,e:5,w:5,f:25,k:25},
  ex_max_inf_r:{p:60,u:40,i:50,e:50,w:40,f:70,k:80},
  ex_skill_inf_r:{p:0.1,u:0.05,i:0.08,e:0.08,w:0.08,f:0.1,k:0.1},
  itemspkinfo:{A:'全系防御',a:'属性防御',B:'伤害抹消',b:'属性抹消',C:'防投',c:'重击辅助',D:'防爆',d:'爆炸',E:'绝缘',e:'电击',F:'防符',f:'灼焰',G:'防弹',g:'同志',H:'HP制御',h:'伤害制御',I:'防冻',i:'冻气',J:'超量素材',j:'多重',K:'防斩',k:'冰华',L:'致残',l:'热恋',M:'陷阱探测',m:'陷阱迎击',N:'冲击',n:'贯穿',o:'一发',P:'防殴',p:'带毒',q:'防毒',R:'混沌',r:'连击',S:'消音',s:'调整',U:'防火',u:'火焰',V:'诅咒',v:'灵魂绑定',W:'隔音',w:'音波',X:'直死',x:'奇迹',y:'破格',Z:'菁英',z:'天然'},
  exdmgname:{p:'毒',u:'火',i:'冰',d:'爆',e:'电',w:'音',f:'炎',k:'冰华'},
  wthname:['晴天','大晴','多云','小雨','暴雨','台风','雷雨','下雪','起雾','浓雾','瘴气','龙卷风','暴风雪','冰雹','离子暴','辐射尘','臭氧洞','极光','光玉雨'],
  plsname:['无月之影','端点','空无一人的高校','雪之镇','索拉利斯','荒废的河岸街','红魔馆','清水池','白穗神社','墓地','麦斯克林','对天使用作战本部','夏之镇','三体星','澄空学园','守矢神社','常磐森林','常磐台中学','秋之镇','精灵中心','春之镇','圣Gradius学园','初始之树','幻想世界','永恒的世界','妖精驿站','键刃墓场','花菱商厦','FARGO前基地','风祭森林','天使队移动格纳库','和田町研究所','SCP研究设施','雏菊之丘','英灵殿'],
};

// ======================== PRESETS ========================
const PRESETS = {
  // === 执行官 subs (ID 7) ===
  yuban:{name:'电击使 御坂美琴',type:1,att:1500,def:7500,lvl:160,hp:25500,mhp:25500,sp:400,msp:400,club:9,pose:1,tactic:3,inf:'',wepk:'WG',wepe:1777,weps:999,wepsk:'eeeeeyc',wp:777,wk:777,wg:2500,wc:777,wd:777,wf:777,artsk:'H',arbsk:'Aa',arhsk:'',arask:'c',arfsk:'',arbe:2700,arhe:1500,arae:1480,arfe:1480,arbs:120,arhs:120,aras:120,arfs:120,skills:{}},
  banchi:{name:'班主任 坂持金发',type:1,att:1500,def:7500,lvl:160,hp:25500,mhp:25500,sp:400,msp:400,club:9,pose:1,tactic:3,inf:'',wepk:'WG',wepe:1777,weps:999,wepsk:'iiiiiyc',wp:777,wk:777,wg:2500,wc:777,wd:777,wf:777,artsk:'H',arbsk:'Aa',arhsk:'',arask:'c',arfsk:'',arbe:2700,arhe:1500,arae:1480,arfe:1480,arbs:120,arhs:120,aras:120,arfs:120,skills:{}},
  yukari:{name:'花之领主 风见幽香',type:1,att:1500,def:7500,lvl:160,hp:25500,mhp:25500,sp:400,msp:400,club:9,pose:1,tactic:3,inf:'',wepk:'WF',wepe:777,weps:999,wepsk:'pppppyc',wp:777,wk:777,wg:777,wc:777,wd:777,wf:2500,artsk:'H',arbsk:'Aa',arhsk:'',arask:'c',arfsk:'',arbe:2700,arhe:1500,arae:1480,arfe:1480,arbs:120,arhs:120,aras:120,arfs:120,skills:{}},
  // === 红暮 (ID 1, original) ===
  hongmu:{name:'红暮',type:1,att:750,def:7500,lvl:75,hp:75000,mhp:75000,sp:4000,msp:4000,club:4,pose:2,tactic:3,inf:'',wepk:'WG',wepe:1750,weps:9999,wepsk:'rfn',wp:777,wk:777,wg:1420,wc:777,wd:777,wf:777,artsk:'H',arbsk:'Ab',arhsk:'',arask:'',arfsk:'',arbe:3000,arhe:1444,arae:0,arfe:0,arbs:3000,arhs:1444,aras:0,arfs:0,skills:{sk_c4_roar:1}},
  // === 红暮 (ID 19, 强化) ===
  hongmu_s:{name:'红暮(强化)',type:1,att:3300,def:2500,lvl:251,hp:20800,mhp:20800,sp:2800,msp:2800,club:98,pose:2,tactic:3,inf:'',wepk:'WK',wepe:1107,weps:9999,wepsk:'rfnv',wp:1888,wk:1888,wg:1888,wc:1888,wd:1888,wf:1888,artsk:'Z',arbsk:'Aa',arhsk:'m',arask:'H',arfsk:'b',arbe:4000,arhe:4000,arae:4000,arfe:4000,arbs:3300,arhs:3300,aras:3300,arfs:3300,skills:{}},
  // === 蓝凝 (ID 19 sub1) ===
  lanning:{name:'蓝凝',type:1,att:1999,def:2800,lvl:251,hp:9999998,mhp:9999998,sp:1600,msp:1600,club:10,pose:1,tactic:3,inf:'',wepk:'WF',wepe:1999,weps:9999,wepsk:'rknv',wp:1888,wk:1888,wg:1888,wc:1888,wd:1888,wf:1888,artsk:'Z',arbsk:'Aa',arhsk:'m',arask:'H',arfsk:'b',arbe:4000,arhe:4000,arae:4000,arfe:4000,arbs:3300,arhs:3300,aras:3300,arfs:3300,skills:{}},
  // === 思念体-蓝凝 (ID 9) ===
  lanning_s:{name:'思念体-蓝凝',type:1,att:9999,def:9999,lvl:97,hp:9999,mhp:9999,sp:9999,msp:9999,club:0,pose:1,tactic:3,inf:'',wepk:'WK',wepe:4000,weps:360,wepsk:'rkd',wp:777,wk:777,wg:777,wc:777,wd:777,wf:777,artsk:'Hc',arbsk:'Aa',arhsk:'',arask:'',arfsk:'',arbe:3000,arhe:1444,arae:1444,arfe:1444,arbs:3000,arhs:3000,aras:3000,arfs:3000,skills:{}},
  // === Hank (ID 11) ===
  hank:{name:'Hank',type:0,att:88,def:88,lvl:30,hp:2888,mhp:2888,sp:888,msp:888,club:0,pose:1,tactic:3,inf:'',wepk:'WD',wepe:88,weps:888,wepsk:'ewuip',wp:100,wk:100,wg:100,wc:100,wd:100,wf:100,artsk:'c',arbsk:'a',arhsk:'F',arask:'H',arfsk:'m',arbe:888,arhe:888,arae:888,arfe:888,arbs:888,arhs:888,aras:888,arfs:888,skills:{}},
  // === 冴月 麟 (ID 5) ===
  sazuki:{name:'冴月麟',type:1,att:333,def:333,lvl:33,hp:3333,mhp:3333,sp:333,msp:333,club:9,pose:1,tactic:3,inf:'',wepk:'WK',wepe:133,weps:333,wepsk:'dr',wp:233,wk:233,wg:233,wc:233,wd:233,wf:233,artsk:'H',arbsk:'A',arhsk:'cW',arask:'UI',arfsk:'qE',arbe:333,arhe:333,arae:333,arfe:333,arbs:333,arhs:333,aras:333,arfs:333,skills:{}},
  // === ■ (ID 4) ===
  black:{name:'■',type:1,att:1000,def:1000,lvl:45,hp:3000,mhp:3000,sp:200,msp:200,club:0,pose:1,tactic:2,inf:'',wepk:'WP',wepe:800,weps:20,wepsk:'fcr',wp:175,wk:175,wg:175,wc:175,wd:175,wf:175,artsk:'c',arbsk:'',arhsk:'',arask:'',arfsk:'',arbe:0,arhe:0,arae:0,arfe:0,arbs:0,arhs:0,aras:0,arfs:0,skills:{}},
  // === Acg_Xilin (ID 6) ===
  xilin:{name:'Acg_Xilin',type:1,att:750,def:550,lvl:45,hp:2800,mhp:2800,sp:200,msp:200,club:4,pose:1,tactic:2,inf:'',wepk:'WG',wepe:360,weps:600,wepsk:'rew',wp:175,wk:175,wg:175,wc:175,wd:175,wf:175,artsk:'c',arbsk:'',arhsk:'',arask:'',arfsk:'',arbe:150,arhe:75,arae:75,arfe:75,arbs:100,arhs:100,aras:100,arfs:100,skills:{sk_c4_roar:1}},
  // === SANMA_TK (ID 18) ===
  sanma:{name:'【SANMA_TK】',type:1,att:1000,def:1000,lvl:45,hp:3000,mhp:3000,sp:200,msp:200,club:0,pose:1,tactic:2,inf:'',wepk:'WG',wepe:400,weps:9999,wepsk:'peNn',wp:175,wk:175,wg:175,wc:175,wd:175,wf:175,artsk:'Z',arbsk:'',arhsk:'',arask:'',arfsk:'',arbe:0,arhe:0,arae:0,arfe:0,arbs:0,arhs:0,aras:0,arfs:0,skills:{}},
  // === 电掣部长 米娜 (ID 20 sub0) ===
  mina:{name:'电掣部长米娜',type:1,att:150,def:888,lvl:55,hp:3888,mhp:3888,sp:3888,msp:3888,club:98,pose:1,tactic:3,inf:'',wepk:'WG',wepe:400,weps:9999,wepsk:'v',wp:300,wk:300,wg:300,wc:300,wd:300,wf:300,artsk:'H',arbsk:'Z',arhsk:'Z',arask:'',arfsk:'Z',arbe:400,arhe:360,arae:360,arfe:360,arbs:220,arhs:220,aras:220,arfs:220,skills:{}},
  // === 是TSEROF啦！ (ID 20 sub1) ===
  tserof:{name:'是TSEROF啦！',type:1,att:107,def:422,lvl:1,hp:4201,mhp:4201,sp:4201,msp:4201,club:99,pose:1,tactic:3,inf:'',wepk:'WK',wepe:400,weps:9999,wepsk:'p',wp:300,wk:300,wg:300,wc:300,wd:300,wf:300,artsk:'',arbsk:'Z',arhsk:'Z',arask:'',arfsk:'Z',arbe:500,arhe:480,arae:640,arfe:320,arbs:350,arhs:440,aras:70,arfs:200,skills:{}},
  // === 笼中鸟 (ID 20 sub2) ===
  cagebird:{name:'笼中鸟',type:1,att:77,def:522,lvl:1,hp:4444,mhp:4444,sp:3333,msp:3333,club:9,pose:1,tactic:3,inf:'',wepk:'WF',wepe:40,weps:9999,wepsk:'',wp:100,wk:100,wg:100,wc:100,wd:100,wf:100,artsk:'H',arbsk:'Z',arhsk:'Z',arask:'',arfsk:'Z',arbe:200,arhe:480,arae:0,arfe:320,arbs:17,arhs:60,aras:0,arfs:600,skills:{}},
  // === 坚韧之子·拉姆 (ID 20 sub3) ===
  ram:{name:'坚韧之子·拉姆',type:1,att:201,def:305,lvl:60,hp:2904,mhp:2904,sp:2233,msp:2233,club:13,pose:1,tactic:3,inf:'',wepk:'WN',wepe:333,weps:9999,wepsk:'N',wp:300,wk:300,wg:300,wc:300,wd:300,wf:300,artsk:'',arbsk:'Z',arhsk:'Z',arask:'',arfsk:'Z',arbe:500,arhe:480,arae:220,arfe:320,arbs:350,arhs:440,aras:100,arfs:200,skills:{}},
  // === 高中生·白神 (ID 20 sub4) ===
  shirakami:{name:'高中生·白神',type:1,att:1,def:8848,lvl:1,hp:800,mhp:800,sp:600,msp:600,club:99,pose:1,tactic:3,inf:'',wepk:'WP',wepe:5,weps:5,wepsk:'',wp:2,wk:2,wg:0,wc:1,wd:3,wf:5,artsk:'',arbsk:'Z',arhsk:'Z',arask:'',arfsk:'Z',arbe:20,arhe:60,arae:0,arfe:20,arbs:20,arhs:20,aras:0,arfs:20,skills:{}},
  // === 诚心使魔·阿摩尔 (ID 20 sub5) ===
  amour:{name:'诚心使魔·阿摩尔',type:1,att:251,def:311,lvl:50,hp:2608,mhp:2608,sp:1978,msp:1978,club:19,pose:1,tactic:3,inf:'',wepk:'WF',wepe:9900,weps:9999,wepsk:'',wp:56,wk:178,wg:2,wc:7,wd:30,wf:9900,artsk:'',arbsk:'Z',arhsk:'Z',arask:'',arfsk:'Z',arbe:300,arhe:300,arae:0,arfe:300,arbs:350,arhs:350,aras:0,arfs:350,skills:{}},
  // === 繁花的烈火 (ID 20 sub6) ===
  flame:{name:'✦繁花的烈火',type:1,att:251,def:311,lvl:50,hp:307,mhp:307,sp:1978,msp:1978,club:19,pose:1,tactic:3,inf:'',wepk:'WF',wepe:9900,weps:9999,wepsk:'',wp:56,wk:178,wg:2,wc:7,wd:30,wf:9900,artsk:'',arbsk:'Z',arhsk:'Z',arask:'',arfsk:'Z',arbe:300,arhe:300,arae:0,arfe:300,arbs:350,arhs:350,aras:0,arfs:350,skills:{}},
  // === 水濑 名雪-改 (ID 2 sub0) ===
  minase:{name:'水濑名雪-改',type:1,att:750,def:550,lvl:45,hp:2800,mhp:2800,sp:200,msp:200,club:0,pose:1,tactic:2,inf:'',wepk:'WD',wepe:300,weps:500,wepsk:'dNne',wp:175,wk:175,wg:175,wc:175,wd:175,wf:175,artsk:'c',arbsk:'',arhsk:'',arask:'',arfsk:'',arbe:150,arhe:75,arae:75,arfe:75,arbs:100,arhs:100,aras:100,arfs:100,skills:{}},
  // === 立华 奏-改 (ID 2 sub1) ===
  tachibana:{name:'立华奏-改',type:1,att:750,def:550,lvl:45,hp:2800,mhp:2800,sp:200,msp:200,club:0,pose:1,tactic:2,inf:'',wepk:'WP',wepe:300,weps:500,wepsk:'uNne',wp:175,wk:175,wg:175,wc:175,wd:175,wf:175,artsk:'c',arbsk:'',arhsk:'',arask:'',arfsk:'',arbe:150,arhe:75,arae:75,arfe:75,arbs:100,arhs:100,aras:100,arfs:100,skills:{}},
  // === 思念体-海马 濑人 (ID 2 sub2) ===
  kaiba:{name:'思念体-海马濑人',type:1,att:750,def:550,lvl:45,hp:2800,mhp:2800,sp:200,msp:200,club:0,pose:1,tactic:2,inf:'',wepk:'WK',wepe:300,weps:500,wepsk:'dNne',wp:175,wk:175,wg:175,wc:175,wd:175,wf:175,artsk:'c',arbsk:'',arhsk:'',arask:'',arfsk:'',arbe:150,arhe:75,arae:75,arfe:75,arbs:100,arhs:100,aras:100,arfs:100,skills:{}},
  // === 思念体-触手众 (ID 2 sub3) ===
  tentacle:{name:'思念体-触手众',type:1,att:750,def:550,lvl:45,hp:2800,mhp:2800,sp:200,msp:200,club:0,pose:1,tactic:2,inf:'',wepk:'WF',wepe:300,weps:500,wepsk:'wNne',wp:175,wk:175,wg:175,wc:175,wd:175,wf:175,artsk:'c',arbsk:'',arhsk:'',arask:'',arfsk:'',arbe:150,arhe:75,arae:75,arfe:75,arbs:100,arhs:100,aras:100,arfs:100,skills:{}},
};

// ======================== UTILITIES ========================
function $(id){return document.getElementById(id)}
function val(id,f=0){const el=$(id);if(!el)return f;if(el.type==='checkbox')return el.checked?1:0;const v=parseFloat(el.value);return isNaN(v)?f:v}
function sval(id,f=''){const el=$(id);return el?el.value:f}
function toggleCollapse(el){el.classList.toggle('collapsed')}
function diceroll(max){return Math.floor(Math.random()*(max+1))}
function getItmskArray(skv){if(!skv)return[];const r=[];for(let i=0;i<skv.length;i++){if(CFG.itemspkinfo[skv[i]])r.push(skv[i])}return r}
function getFluc(mode){if(mode==='avg')return 0.7;if(mode==='max')return 1.0;if(mode==='min')return 0.4;return(4+Math.floor(Math.random()*7))/10}
function checkDef(mode,failRate){if(mode==='always')return true;if(mode==='never')return false;return diceroll(99)>failRate}
function parseSkNames(skv){const keys=getItmskArray(skv);return keys.map(k=>`${k}=${CFG.itemspkinfo[k]}`).join(', ')}
function updateSkDisplay(id,displayId){const v=sval(id,'');const el=$(displayId);if(el)el.textContent=v?parseSkNames(v):''}

// ======================== UI GENERATION ========================
function makeRow(label,id,val,type,opts){
  const o=opts||[];
  let s=`<div class="row"><label>${label}</label>`;
  if(type==='select'){s+=`<select id="${id}">`;o.forEach(x=>s+=`<option value="${x.v}"${x.v==val?' selected':''}>${x.t}</option>`);s+='</select>'}
  else if(type==='check'){s+=`<input type="checkbox" id="${id}"${val?' checked':''}>`}
  else{s+=`<input type="${type||'number'}" id="${id}" value="${val}">`}
  s+='</div>';return s
}
function makeChecks(prefix,items){
  let s='<div class="checks">';
  items.forEach(x=>s+=`<label><input type="checkbox" id="${prefix}_${x.id}"><span>${x.n}</span></label>`);
  s+='</div>';return s
}
function makeSection(title,collapsed,html){
  return `<div class="section"><h3 class="collapsible${collapsed?' collapsed':''}" onclick="toggleCollapse(this)">${title}</h3><div class="content">${html}</div></div>`
}

const PA_SKILLS=[
  {id:'c1_crit',n:'猛击'},{id:'c4_roar',n:'咆哮'},{id:'c4_aiming',n:'瞄准'},{id:'c4_sniper',n:'穿杨'},
  {id:'c2_raiding',n:'强袭'},{id:'c9_lb',n:'必杀'},{id:'c3_potential',n:'潜能'},{id:'c10_decons',n:'解构'},
  {id:'c13_master',n:'宗师'},{id:'c13_kungfu',n:'拳法'},{id:'c7_overload',n:'过载'},{id:'c8_expert',n:'特攻'},
  {id:'c7_emp',n:'脉冲'},{id:'c9_iceheart',n:'冰心'},{id:'buff_shield',n:'护盾'},{id:'c19_purity',n:'莹心'},
  {id:'c12_enmity',n:'底力'},{id:'c2_butcher',n:'解牛'},{id:'c1_bjack',n:'闷棍'},
];
const PD_SKILLS=[
  {id:'c1_def',n:'格挡'},{id:'c13_parry',n:'消力'},{id:'c12_garrison',n:'根性'},{id:'c9_spirit',n:'灵力'},
  {id:'buff_shield',n:'护盾'},{id:'c19_purity',n:'莹心'},{id:'c8_deadheal',n:'死疗'},
];

function buildAttackerUI(){
  const poseOpts=[['0','0 通常'],['1','1 攻击'],['2','2 防御'],['3','3 反击'],['4','4 躲避'],['5','5 静养'],['6','6 暴走'],['7','7 待机']];
  const tacOpts=[['0','0 通常'],['1','1 攻击'],['2','2 防御'],['3','3 反击'],['4','4 躲避']];
  const clubOpts=[['0','无'],['1','1'],['2','2'],['3','3'],['4','4'],['5','5'],['6','6'],['7','7'],['8','8'],['9','9 灵力'],['10','10 天赋'],['11','11'],['12','12'],['13','13']];
  let html='<h2>⚔️ 攻击方 (Attacker)</h2>';
  html+=makeSection('基础属性',false,
    makeRow('名称','pa_name','Attacker','text')+
    makeRow('类型','pa_type',1,'select',[[{v:0,t:'玩家'},{v:1,t:'NPC'}]])+
    makeRow('att 攻击','pa_att',1500)+makeRow('def 防御','pa_def',7500)+
    makeRow('等级','pa_lvl',75)+makeRow('HP/MaxHP','pa_hp',25500)+
    `<div class="row"><label></label><input type="number" id="pa_mhp" value="25500"></div>`+
    makeRow('SP/MaxSP','pa_sp',4000)+`<div class="row"><label></label><input type="number" id="pa_msp" value="4000"></div>`+
    makeRow('社团','pa_club',9,'select',clubOpts.map(x=>({v:x[0],t:x[1]})))+
    makeRow('姿态','pa_pose',1,'select',poseOpts.map(x=>({v:x[0],t:x[1]})))+
    makeRow('策略','pa_tactic',3,'select',tacOpts.map(x=>({v:x[0],t:x[1]})))+
    makeRow('受伤 inf','pa_inf','','text')
  );
  html+=makeSection('武器',false,
    makeRow('武器代码','pa_wepk','WG','text')+makeRow('wepe 效能','pa_wepe',1777)+
    makeRow('weps 耐久','pa_weps',999)+makeRow('wepsk 属性','pa_wepsk','eeeeeyc','text')+
    `<div class="mini" id="pa_wepsk_display" style="margin-left:88px;color:var(--cyan)"></div>`+
    `<div class="mini">wepk第2字符=武器系: N/P/K/G/C/D/F/J/B</div>`
  );
  html+=makeSection('熟练度',true,
    makeRow('wp 殴打','pa_wp',777)+makeRow('wk 斩刺','pa_wk',777)+makeRow('wg 射击','pa_wg',2500)+
    makeRow('wc 投掷','pa_wc',777)+makeRow('wd 引信','pa_wd',777)+makeRow('wf 灵力','pa_wf',777)
  );
  html+=makeSection('主动技能',true,makeChecks('pa_sk',PA_SKILLS));
  html+=makeSection('饰品&防具属性',true,
    makeRow('饰品artsk','pa_artsk','H','text')+`<div class="mini" id="pa_artsk_display" style="margin-left:88px;color:var(--cyan)"></div>`+
    makeRow('arb-sk','pa_arbsk','','text')+`<div class="mini" id="pa_arbsk_display" style="margin-left:88px;color:var(--cyan)"></div>`+
    makeRow('arh-sk','pa_arhsk','','text')+`<div class="mini" id="pa_arhsk_display" style="margin-left:88px;color:var(--cyan)"></div>`+
    makeRow('ara-sk','pa_arask','','text')+`<div class="mini" id="pa_arask_display" style="margin-left:88px;color:var(--cyan)"></div>`+
    makeRow('arf-sk','pa_arfsk','','text')+`<div class="mini" id="pa_arfsk_display" style="margin-left:88px;color:var(--cyan)"></div>`
  );
  $('pa_card').innerHTML=html
}
function buildDefenderUI(){
  const poseOpts=[['0','0 通常'],['1','1 攻击'],['2','2 防御'],['3','3 反击'],['4','4 躲避'],['5','5 静养'],['6','6 暴走'],['7','7 待机']];
  const tacOpts=[['0','0 通常'],['1','1 攻击'],['2','2 防御'],['3','3 反击'],['4','4 躲避']];
  const clubOpts=[['0','无'],['1','1'],['2','2'],['3','3'],['4','4'],['5','5'],['6','6'],['7','7'],['8','8'],['9','9'],['10','10'],['11','11'],['12','12'],['13','13']];
  let html='<h2>🛡️ 防守方 (Defender)</h2>';
  html+=makeSection('基础属性',false,
    makeRow('名称','pd_name','Defender','text')+
    makeRow('类型','pd_type',1,'select',[[{v:0,t:'玩家'},{v:1,t:'NPC'}]])+
    makeRow('def 防御','pd_def',88)+makeRow('等级','pd_lvl',1)+
    makeRow('HP/MaxHP','pd_hp',2888)+`<div class="row"><label></label><input type="number" id="pd_mhp" value="2888"></div>`+
    makeRow('社团','pd_club',0,'select',clubOpts.map(x=>({v:x[0],t:x[1]})))+
    makeRow('姿态','pd_pose',1,'select',poseOpts.map(x=>({v:x[0],t:x[1]})))+
    makeRow('策略','pd_tactic',3,'select',tacOpts.map(x=>({v:x[0],t:x[1]})))+
    makeRow('受伤 inf','pd_inf','','text')
  );
  html+=makeSection('武器(影响J受击加伤)',false,
    makeRow('武器代码','pd_wepk','WP','text')+makeRow('wepe 效能','pd_wepe',88)+
    makeRow('weps 耐久','pd_weps',999)+makeRow('wepsk 属性','pd_wepsk','','text')+
    `<div class="mini" id="pd_wepsk_display" style="margin-left:88px;color:var(--cyan)"></div>`
  );
  html+=makeSection('防具',false,
    makeRow('arb-e','pd_arbe',0)+makeRow('arb-sk','pd_arbsk','Aa','text')+makeRow('arb-s','pd_arbs',0)+
    `<div class="mini" id="pd_arbsk_display" style="margin-left:88px;color:var(--cyan)"></div>`+
    makeRow('arh-e','pd_arhe',0)+makeRow('arh-sk','pd_arhsk','','text')+makeRow('arh-s','pd_arhs',0)+
    `<div class="mini" id="pd_arhsk_display" style="margin-left:88px;color:var(--cyan)"></div>`+
    makeRow('ara-e','pd_arae',0)+makeRow('ara-sk','pd_arask','','text')+makeRow('ara-s','pd_aras',0)+
    `<div class="mini" id="pd_arask_display" style="margin-left:88px;color:var(--cyan)"></div>`+
    makeRow('arf-e','pd_arfe',0)+makeRow('arf-sk','pd_arfsk','','text')+makeRow('arf-s','pd_arfs',0)+
    `<div class="mini" id="pd_arfsk_display" style="margin-left:88px;color:var(--cyan)"></div>`+
    makeRow('饰品artsk','pd_artsk','H','text')+
    `<div class="mini" id="pd_artsk_display" style="margin-left:88px;color:var(--cyan)"></div>`
  );
  html+=makeSection('熟练度',true,
    makeRow('wp','pd_wp',100)+makeRow('wk','pd_wk',100)+makeRow('wg','pd_wg',100)+
    makeRow('wc','pd_wc',100)+makeRow('wd','pd_wd',100)+makeRow('wf','pd_wf',100)
  );
  html+=makeSection('防守技能',true,makeChecks('pd_sk',PD_SKILLS));
  $('pd_card').innerHTML=html
}
function buildEnvUI(){
  const wthOpts=CFG.wth_atk.map((v,i)=>({v:i,t:`${CFG.wthname[i]} (${v>=0?'+':''}${v}%/${CFG.wth_def[i]>=0?'+':''}${CFG.wth_def[i]}%)`}));
  const plsOpts=CFG.pls_atk.map((v,i)=>({v:i,t:`${CFG.plsname[i]} (${v>=0?'+':''}${v}%/${CFG.pls_def[i]>=0?'+':''}${CFG.pls_def[i]}%)`}));
  let html='<h2>🌍 环境与计算选项</h2><div class="row">'+
    makeRow('天气','env_weather',0,'select',wthOpts).replace('<div class="row">','')+
    makeRow('地点','env_pls',0,'select',plsOpts).replace('</div>','')+'</div>';
  html+='<div class="row">'+
    `<label>攻击类型</label><select id="opt_atk"><option value="0">先制攻击</option><option value="1">反击</option></select>`+
    `<label>浮动模式</label><select id="opt_fluc"><option value="avg">平均(×0.7)</option><option value="max">最大(×1.0)</option><option value="min">最小(×0.4)</option><option value="random">随机</option></select>`+
    `<label>物防判定</label><select id="opt_pdef"><option value="random">随机</option><option value="always">必生效</option><option value="never">必失效</option></select>`+
    `<label>属防判定</label><select id="opt_edef"><option value="random">随机</option><option value="always">必生效</option><option value="never">必失效</option></select>`+
    `<label>连击</label><select id="opt_combo"><option value="0">无</option><option value="2">2连</option><option value="3">3连</option><option value="4">4连</option><option value="5">5连</option></select>`+
    '</div>';
  $('env_card').innerHTML=html
}

// ======================== COLLECT INPUTS ========================
function collectAttacker(){
  const pa={name:sval('pa_name','Attacker'),type:val('pa_type',1),att:val('pa_att',1500),def:val('pa_def',7500),
    lvl:val('pa_lvl',75),hp:val('pa_hp',25500),mhp:val('pa_mhp',25500),sp:val('pa_sp',4000),msp:val('pa_msp',4000),
    club:val('pa_club',9),pose:val('pa_pose',1),tactic:val('pa_tactic',3),inf:sval('pa_inf',''),
    wepk:sval('pa_wepk','WG'),wepe:val('pa_wepe',1777),weps:val('pa_weps',999),wepsk:sval('pa_wepsk',''),
    wp:val('pa_wp',777),wk:val('pa_wk',777),wg:val('pa_wg',2500),wc:val('pa_wc',777),wd:val('pa_wd',777),wf:val('pa_wf',777),
    artsk:sval('pa_artsk',''),arbsk:sval('pa_arbsk',''),arhsk:sval('pa_arhsk',''),arask:sval('pa_arask',''),arfsk:sval('pa_arfsk','')};
  PA_SKILLS.forEach(s=>{const el=$('pa_sk_'+s.id);if(el&&el.checked)pa['sk_'+s.id]=1});
  return pa
}
function collectDefender(){
  const pd={name:sval('pd_name','Defender'),type:val('pd_type',1),def:val('pd_def',88),lvl:val('pd_lvl',1),
    hp:val('pd_hp',2888),mhp:val('pd_mhp',2888),club:val('pd_club',0),pose:val('pd_pose',1),tactic:val('pd_tactic',3),
    inf:sval('pd_inf',''),wepk:sval('pd_wepk','WP'),wepe:val('pd_wepe',88),weps:val('pd_weps',999),wepsk:sval('pd_wepsk',''),
    wp:val('pd_wp',100),wk:val('pd_wk',100),wg:val('pd_wg',100),wc:val('pd_wc',100),wd:val('pd_wd',100),wf:val('pd_wf',100),
    arbe:val('pd_arbe',0),arhe:val('pd_arhe',0),arae:val('pd_arae',0),arfe:val('pd_arfe',0),
    arbs:val('pd_arbs',0),arhs:val('pd_arhs',0),aras:val('pd_aras',0),arfs:val('pd_arfs',0),
    arbsk:sval('pd_arbsk',''),arhsk:sval('pd_arhsk',''),arask:sval('pd_arask',''),arfsk:sval('pd_arfsk',''),artsk:sval('pd_artsk','')};
  PD_SKILLS.forEach(s=>{const el=$('pd_sk_'+s.id);if(el&&el.checked)pd['skill_'+s.id]=1});
  return pd
}

// ======================== CALCULATION ENGINE ========================
function getWepKind(p){if(p.wep_kind)return p.wep_kind;p.wep_kind=p.wepk.length>1?p.wepk[1]:'N';return p.wep_kind}
function getWepSkill(p){if(p.wep_skill!==undefined)return p.wep_skill;getWepKind(p);const sk=CFG.skillinfo[p.wep_kind];let v=p[sk]||0;if(p.club==10)v=Math.round(p[sk]+(p.wp+p.wk+p.wc+p.wg+p.wd+p.wf)*0.25);p.wep_skill=v;return v}
function getExKeys(p){if(p.ex_keys)return p.ex_keys;p.ex_keys=[...getItmskArray(p.wepsk),...getItmskArray(p.arbsk),...getItmskArray(p.arhsk),...getItmskArray(p.arask),...getItmskArray(p.arfsk),...getItmskArray(p.artsk)];return p.ex_keys}

function calcBaseAtt(pa,pd,isCounter,env,L){
  getWepKind(pa);const ws=getWepSkill(pa);let wepe_t;
  if(pa.wep_kind==='N'){wepe_t=Math.round(ws*2/3);if(pa.sk_c13_kungfu)wepe_t=Math.round(ws)}
  else if(pa.wep_kind==='G'||pa.wep_kind==='J'){wepe_t=pa.wepe}
  else{wepe_t=pa.wepe*2}
  let base_att=pa.att+wepe_t;
  L.push({c:'phy',l:'基础攻击力',f:`att(${pa.att}) + wepe_t(${wepe_t})`,v:base_att});
  let pct=100;const wth=CFG.wth_atk[env.weather]||0,pls=CFG.pls_atk[env.pls]||0;
  let pose=0,tac=0;
  if(!isCounter&&CFG.pose_atk_active)pose=CFG.pose_atk[pa.pose]||0;else if(!CFG.pose_atk_active)pose=CFG.pose_atk[pa.pose]||0;
  if(isCounter&&CFG.tactic_atk_active)tac=CFG.tactic_atk[pa.tactic]||0;else if(!CFG.tactic_atk_active)tac=CFG.tactic_atk[pa.tactic]||0;
  pct+=wth+pls+pose+tac;pct=Math.max(pct,1);
  let inf_pct=100;if(pa.inf){for(const[k,v]of Object.entries(CFG.inf_att_p)){if(pa.inf.includes(k))inf_pct*=v}}
  base_att=Math.round(base_att*(pct/100)*(inf_pct/100));base_att=Math.max(base_att,1);
  L.push({c:'phy',l:'攻击力修正',f:`天气${wth}%+地点${pls}%+姿态${pose}%+策略${tac}%+受伤${inf_pct}%`,v:base_att});
  pa.base_att=base_att;return base_att
}

function calcBaseDef(pa,pd,isCounter,env,L){
  const base_def=pd.def,equip_def=(pd.arbe||0)+(pd.arhe||0)+(pd.arae||0)+(pd.arfe||0);
  let eff_equip=equip_def,chargeFlag=false;
  const paEx=getExKeys(pa);
  if(paEx.includes('N')&&diceroll(99)<CFG.specialrate.N){chargeFlag=true;eff_equip=Math.round(equip_def/2)}
  let sk_def=0;
  if(pd.skill_c1_def){sk_def+=Math.min(500,3*pd.wepe/100)}
  if(pd.skill_c13_parry){sk_def+=pd.wp||0}
  let total_def=base_def+eff_equip+sk_def;
  L.push({c:'phy',l:'基础防御力',f:`def(${base_def})+装备(${eff_equip}${chargeFlag?' [冲击半减]':''})${sk_def?'+技能('+sk_def+')':''}`,v:total_def});
  let pct=100;const wth=CFG.wth_def[env.weather]||0,pls=CFG.pls_def[env.pls]||0;
  let pose=0,tac=0;
  if(!isCounter&&CFG.pose_def_active)pose=CFG.pose_def[pd.pose]||0;else if(!CFG.pose_def_active)pose=CFG.pose_def[pd.pose]||0;
  if(isCounter&&CFG.tactic_def_active)tac=CFG.tactic_def[pd.tactic]||0;else if(!CFG.tactic_def_active)tac=CFG.tactic_def[pd.tactic]||0;
  pct+=wth+pls+pose+tac;pct=Math.max(pct,1);
  let inf_pct=100;if(pd.inf){for(const[k,v]of Object.entries(CFG.inf_def_p)){if(pd.inf.includes(k))inf_pct*=v}}
  let sk_var=100;
  if(pd.skill_c12_garrison){sk_var=100+30}
  total_def=Math.round(total_def*(pct/100)*(inf_pct/100));
  if(sk_var!=100)total_def=Math.round(total_def*(sk_var/100));
  total_def=Math.max(total_def,1);
  L.push({c:'phy',l:'防御力修正',f:`天气${wth}%+地点${pls}%+姿态${pose}%+策略${tac}%+受伤${inf_pct}%${sk_var!=100?'+根性'+sk_var+'%':''}`,v:total_def});
  pd.base_def=total_def;return total_def
}

function calcOriginalDmg(pa,pd,flucMode,L){
  const ws=getWepSkill(pa);const sd=CFG.skill_dmg[pa.wep_kind]||0.5;
  let dmg=(pa.base_att/pd.base_def)*ws*sd;
  const dfluc=CFG.dmg_fluc[pa.wep_kind]||15;
  const fluc=getFluc(flucMode);
  dmg=Math.round(dmg*((100+(dfluc>0?dfluc:0))/100)*fluc);
  L.push({c:'phy',l:'原始物理伤害',f:`(att${pa.base_att}/def${pd.base_def})×熟练${ws}×系数${sd}×浮动${fluc.toFixed(2)}`,v:dmg});
  pa.original_dmg=dmg;return dmg
}

function calcFixDmg(pa,pd,L){
  let fix=0,log='';
  if(pa.wep_kind==='J'){fix=Math.round(pa.wepe*2/3)+Math.round(pa.mhp/3);log=`J固伤: wepe*2/3(${Math.round(pa.wepe*2/3)}) + mhp/3(${Math.round(pa.mhp/3)})`}
  else if(pa.wep_kind==='F'){fix=pa.wepe;log=`F固伤: wepe(${pa.wepe})`}
  if(fix>0){L.push({c:'phy',l:'固定伤害',f:log,v:fix})}
  return fix
}

function calcDamageP(pa,pd,flucMode,L){
  const arr=[];
  // F weapon SP cost
  if(pa.wep_kind==='F'){
    let sp_cost_r=pa.club==9?0.2:0.25;
    if(pa.skill_c9_spirit){sp_cost_r*=0.5}
    const sp_max=sp_cost_r*pa.wepe;
    const sp_cost=Math.min(sp_max,pa.sp-1);
    const factor=pa.type?0.5:0.5+Math.round((sp_cost/sp_max)/2*10)/10;
    arr.push(Math.round(factor*100)/100);
    L.push({c:'phy',l:'灵力武器威力',f:`SP消耗${Math.ceil(sp_cost)}/${Math.round(sp_max)} → ${Math.round(factor*100)}%威力`,v:Math.round(factor*100)+'%'});
    if(!pa.type)pa.sp-=Math.ceil(sp_cost);
  }
  // Combo
  const comboTimes=val('opt_combo',0);
  if(comboTimes>1){
    const r={2:2,3:2.8};const p=r[comboTimes]||2.8+(comboTimes-3)*0.6;
    arr.push(p);
    L.push({c:'phy',l:'连击倍率',f:`${comboTimes}连击 → ×${p}`,v:'×'+p});
  }
  // Skills
  if(pa.sk_c1_crit){arr.push(1.5);L.push({c:'phy',l:'猛击',f:'×1.5',v:'×1.5'})}
  if(pa.sk_c9_lb){arr.push(2.0);L.push({c:'phy',l:'必杀',f:'×2.0',v:'×2.0'})}
  if(pa.sk_c3_potential){arr.push(1.2);L.push({c:'phy',l:'潜能',f:'×1.2',v:'×1.2'})}
  if(pa.sk_c4_roar){arr.push(1.2);L.push({c:'phy',l:'咆哮(物理)',f:'×1.2',v:'×1.2'})}
  if(pa.sk_c4_aiming){arr.push(1.15);L.push({c:'phy',l:'瞄准',f:'×1.15',v:'×1.15'})}
  if(pa.sk_c4_sniper){arr.push(1.15);L.push({c:'phy',l:'穿杨',f:'×1.15',v:'×1.15'})}
  if(pa.sk_c10_decons){arr.push(1.15);L.push({c:'phy',l:'解构',f:'×1.15',v:'×1.15'})}
  if(pa.sk_c13_master&&pa.wep_kind!='N'){arr.push(0.7);L.push({c:'phy',l:'宗师(武器减伤)',f:'×0.7',v:'×0.7'})}
  return arr
}

function calcDamageDefP(pa,pd,defMode,L){
  const arr=[];const pdEx=getExKeys(pd);
  // J weapon holder penalty
  if(pd.wepk&&pd.wepk[1]==='J'){arr.push(1.5);L.push({c:'phy',l:'J武器受击加伤',f:'×1.5',v:'×1.5'})}
  // Physical defense flags
  const paEx=getExKeys(pa);
  let phyDefFlag=null;
  // B=伤害抹消
  if(pdEx.includes('B')){
    const failRate=100-CFG.specialrate.B;
    if(checkDef(defMode,failRate)){phyDefFlag=2;L.push({c:'phy',l:'伤害抹消生效',f:'物理伤害→0',v:'抹消!'})
      // EMP check
      if(pa.sk_c7_emp){phyDefFlag=null;L.push({c:'phy',l:'脉冲无效化抹消',f:'',v:'无效化!'})}
    }else{L.push({c:'phy',l:'伤害抹消失效',f:'',v:'失效'})}
  }
  // A=全系防御
  if(phyDefFlag===null&&pdEx.includes('A')){
    if(checkDef(defMode,10)){phyDefFlag=1;L.push({c:'phy',l:'全系防御生效',f:'物理伤害×0.5',v:'半减!'})}
    else{L.push({c:'phy',l:'全系防御失效',f:'',v:'失效'})}
  }
  // Single def (P/K/G/C/D/F)
  if(phyDefFlag===null){
    const dk=CFG.def_kind[pa.wep_kind];
    if(dk&&pdEx.includes(dk)){
      if(checkDef(defMode,10)){phyDefFlag=1;arr.push(0.5);L.push({c:'phy',l:`${CFG.itemspkinfo[dk]}生效`,f:'物理伤害×0.5',v:'半减!'})}
      else{L.push({c:'phy',l:`${CFG.itemspkinfo[dk]}失效`,f:'',v:'失效'})}
    }
  }
  if(phyDefFlag===2){arr.push(0);L.push({c:'phy',l:'物理伤害被抹消',f:'×0',v:'0!'})}
  else if(phyDefFlag===1){arr.push(0.5);L.push({c:'phy',l:'全系防御减伤',f:'×0.5',v:'×0.5'})}
  // 热恋/同志
  if(pdEx.includes('l')){arr.push(0.5);L.push({c:'phy',l:'热恋',f:'×0.5',v:'×0.5'})}
  if(pdEx.includes('g')){arr.push(0.5);L.push({c:'phy',l:'同志',f:'×0.5',v:'×0.5'})}
  // 伤害制御
  if(pdEx.includes('h')){L.push({c:'phy',l:'伤害制御',f:'(在最终伤害阶段判定)',v:'待定'})}
  pd.phy_def_flag=phyDefFlag;
  return arr
}

function calcExDamage(pa,pd,exdefMode,flucMode,L){
  const paEx=getExKeys(pa);
  const exKeys=paEx.filter(x=>CFG.ex_attack.includes(x));
  if(exKeys.length===0)return 0;
  const pdEx=getExKeys(pd);
  let exDefFlag=null;
  // b=属性抹消
  if(pdEx.includes('b')){
    if(checkDef(exdefMode,100-CFG.specialrate.b)){exDefFlag=2;L.push({c:'ele',l:'属性抹消生效',f:'属性伤害→固定1/种类数',v:'抹消!'})
      if(pa.sk_c7_emp){exDefFlag=null;L.push({c:'ele',l:'脉冲无效化属抹',f:'',v:'无效化!'})}
    }else{L.push({c:'ele',l:'属性抹消失效',f:'',v:'失效'})}
  }
  // a=属性防御
  if(exDefFlag===null&&pdEx.includes('a')){
    if(checkDef(exdefMode,10)){exDefFlag=1;L.push({c:'ele',l:'属性防御生效',f:'属性伤害×0.5',v:'半减!'})}
    else{L.push({c:'ele',l:'属性防御失效',f:'',v:'失效'})}
  }
  // 单项属性防御 (防毒q/防火U/防冻I/绝缘E/隔音W/防爆D)
  if(exDefFlag===null){
    const singleDef=[];
    exKeys.forEach(ex=>{
      const dk=CFG.ex_def_kind[ex];
      if(dk&&pdEx.includes(dk)){
        if(checkDef(exdefMode,10)){singleDef.push(ex);L.push({c:'ele',l:`${CFG.itemspkinfo[dk]}对${CFG.exdmgname[ex]}生效`,f:'该属性×0.5',v:'半减!'})}
        else{L.push({c:'ele',l:`${CFG.itemspkinfo[dk]}对${CFG.exdmgname[ex]}失效`,f:'',v:'失效'})}
      }
    });
    if(singleDef.length>0)exDefFlag=singleDef;
  }
  // y=破格
  let pierceFlag=false;
  if(paEx.includes('y')){
    if(!checkDef(exdefMode,100-CFG.specialrate.y)){pierceFlag=true;exDefFlag=0;L.push({c:'ele',l:'破格(属穿)生效',f:'无视属性防御',v:'属穿!'})}
    else{L.push({c:'ele',l:'破格失效',f:'',v:'失效'})}
  }
  // 强袭
  if(pa.sk_c2_raiding&&exDefFlag&&exDefFlag!=2){exDefFlag=0;L.push({c:'ele',l:'强袭无视属防',f:'',v:'无视!'})}
  pd.ex_def_flag=exDefFlag;

  if(exDefFlag===2){
    const fixed=exKeys.length;
    L.push({c:'ele',l:'属性伤害(抹消)',f:`固定${fixed}点`,v:fixed});
    return fixed;
  }

  let totalEx=0;
  const ws=getWepSkill(pa);
  exKeys.forEach(ex=>{
    let edmg=CFG.ex_base_dmg[ex]+pa.wepe/CFG.ex_wep_dmg[ex]+ws/CFG.ex_skill_dmg[ex];
    // Good weapon check
    if(CFG.ex_good_wep[ex]===pa.wep_kind){edmg*=2;L.push({c:'ele',l:`${CFG.exdmgname[ex]}得意武器`,f:'伤害×2',v:''})}
    // Cap
    const cap=CFG.ex_max_dmg[ex];
    if(cap>0&&edmg>cap){edmg=cap}
    // Fluctuation
    const fluc=CFG.ex_dmg_fluc[ex];
    const f=getFluc(flucMode);
    edmg=Math.round(edmg*((100+fluc)/100)*f);
    // Injury punish
    if(pd.inf&&pd.inf.includes(CFG.ex_inf[ex])){edmg=Math.round(edmg*CFG.ex_inf_punish[ex]);L.push({c:'ele',l:`${CFG.exdmgname[ex]}异常加成`,f:`×${CFG.ex_inf_punish[ex]}`,v:''})}
    // Overload
    if(pa.sk_c7_overload){edmg=Math.round(edmg*1.5);L.push({c:'ele',l:`${CFG.exdmgname[ex]}过载`,f:'×1.5',v:''})}
    // Defense halving
    if(exDefFlag===1||(Array.isArray(exDefFlag)&&exDefFlag.includes(ex))){edmg=Math.round(edmg*0.5);L.push({c:'ele',l:`${CFG.exdmgname[ex]}属防半减`,f:'×0.5',v:''})}
    // Shield
    if(pd.skill_buff_shield){edmg=0;L.push({c:'ele',l:`${CFG.exdmgname[ex]}护盾`,f:'×0',v:'0'})}
    L.push({c:'ele',l:`${CFG.exdmgname[ex]}属性伤害`,f:`base${CFG.ex_base_dmg[ex]}+wepe/${CFG.ex_wep_dmg[ex]}+skill/${CFG.ex_skill_dmg[ex]}${cap>0?' cap'+cap:''} ×${f.toFixed(2)}`,v:edmg});
    totalEx+=edmg;
  });

  // Elemental total multipliers
  const exP=[];
  if(pa.sk_c4_roar){exP.push(1.8);L.push({c:'ele',l:'咆哮(属性)',f:'×1.8',v:'×1.8'})}
  if(pa.sk_c8_expert){exP.push(1.2);L.push({c:'ele',l:'特攻',f:'×1.2',v:'×1.2'})}
  exP.forEach(p=>{totalEx=Math.round(totalEx*p)});
  L.push({c:'ele',l:'属性伤害合计',f:exKeys.map(e=>CFG.exdmgname[e]).join('+'),v:totalEx});
  return totalEx;
}

function calcFinalDmgP(pa,pd,L){
  const arr=[];
  if(pa.sk_c2_raiding){arr.push(1.1);L.push({c:'fin',l:'强袭(最终)',f:'×1.1',v:'×1.1'})}
  if(pa.sk_c12_enmity){arr.push(1.15);L.push({c:'fin',l:'底力',f:'×1.15',v:'×1.15'})}
  if(pa.sk_c19_purity){arr.push(0.85);L.push({c:'fin',l:'莹心(pa)',f:'×0.85',v:'×0.85'})}
  if(pd.skill_c19_purity){arr.push(0.85);L.push({c:'fin',l:'莹心(pd)',f:'×0.85',v:'×0.85'})}
  return arr
}

function calcFinalDmgFix(pa,pd,dmg,L){
  let fix=dmg;
  if(pa.sk_c2_butcher){const sd=50+pa.lvl;fix+=sd;L.push({c:'fin',l:'解牛',f:`+${sd}`,v:fix})}
  if(pa.sk_c1_bjack){const sd=pd.msp-pd.sp;if(sd>0){fix+=sd;L.push({c:'fin',l:'闷棍',f:`+${sd}(体力差)`,v:fix})}}
  // 伤害制御
  const pdEx=getExKeys(pd);
  if(pdEx.includes('h')&&fix>=1950){
    if(checkDef('random',20)){fix=Math.round(fix*0.5);L.push({c:'fin',l:'伤害制御生效',f:'最终伤害×0.5',v:fix})}
    else{L.push({c:'fin',l:'伤害制御失效',f:'',v:'失效'})}
  }
  return fix
}

function calcBacklash(pa,dmg,L){
  if(dmg<1000){L.push({c:'bsh',l:'反噬',f:'伤害<1000 无反噬',v:'无'});return 0}
  let hp_d;
  if(dmg<2000){hp_d=Math.floor(pa.hp/2);L.push({c:'bsh',l:'反噬(1000-2000)',f:`floor(HP${pa.hp}/2)`,v:hp_d})}
  else if(dmg<5000){hp_d=Math.floor(pa.hp*2/3);L.push({c:'bsh',l:'反噬(2000-5000)',f:`floor(HP${pa.hp}×2/3)`,v:hp_d})}
  else{hp_d=Math.floor(pa.hp*4/5);L.push({c:'bsh',l:'反噬(5000+)',f:`floor(HP${pa.hp}×4/5)`,v:hp_d})}
  const paEx=getExKeys(pa);
  if(paEx.includes('H')){
    if(pa.sk_c7_emp){L.push({c:'bsh',l:'脉冲无效化HP制御',f:'',v:'无效化!'})}
    else{hp_d=Math.floor(hp_d/10);L.push({c:'bsh',l:'HP制御',f:'反噬÷10',v:hp_d})}
  }
  if(hp_d&&pa.sk_buff_shield){hp_d=0;L.push({c:'bsh',l:'护盾',f:'反噬→0',v:'0!'})}
  if(hp_d&&pa.sk_c9_iceheart){hp_d=Math.floor(hp_d*0.5);L.push({c:'bsh',l:'冰心',f:'反噬×0.5',v:hp_d})}
  if(hp_d>0){L.push({c:'bsh',l:'最终反噬伤害',f:`${pa.name}自身受到${hp_d}点反噬`,v:hp_d})}
  return hp_d
}

// ======================== MAIN CALCULATE ========================
function calculate(){
  const pa=collectAttacker(),pd=collectDefender();
  const env={weather:val('env_weather',0),pls:val('env_pls',0)};
  const isCounter=val('opt_atk',0)===1;
  const flucMode=sval('opt_fluc','avg');
  const defMode=sval('opt_pdef','random');
  const exdefMode=sval('opt_edef','random');
  const L=[];

  // === Phase 1: Base attack & defense ===
  calcBaseAtt(pa,pd,isCounter,env,L);
  calcBaseDef(pa,pd,isCounter,env,L);

  // === Phase 2: Original physical damage ===
  calcOriginalDmg(pa,pd,flucMode,L);
  let dmg=pa.original_dmg;

  // === Phase 3: Fixed damage ===
  const fixDmg=calcFixDmg(pa,pd,L);
  dmg+=fixDmg;

  // === Phase 4: Attacker damage multipliers ===
  const dmgP=calcDamageP(pa,pd,flucMode,L);
  // === Phase 5: Defender damage multipliers ===
  const dmgDefP=calcDamageDefP(pa,pd,defMode,L);
  const allP=[...dmgP,...dmgDefP];

  if(allP.includes(0)){
    dmg=0;
    L.push({c:'phy',l:'物理伤害',f:'倍率含0 → 伤害归零',v:0});
  }else{
    let logStr=`${dmg}`;
    allP.forEach(p=>{dmg=Math.round(dmg*p);logStr+=`×${p}`});
    L.push({c:'phy',l:'物理伤害计算',f:logStr+'＝',v:dmg});
  }
  dmg=Math.max(dmg,1);
  const pdamage=dmg;
  L.push({c:'phy',l:'最终物理伤害',f:'',v:dmg});

  // === Phase 6: Elemental damage ===
  const exDmg=calcExDamage(pa,pd,exdefMode,flucMode,L);
  dmg+=exDmg;

  // === Phase 7: Final damage multipliers ===
  const finP=calcFinalDmgP(pa,pd,L);
  if(finP.length>0){
    let logStr=`${dmg}`;
    finP.forEach(p=>{dmg=Math.round(dmg*p);logStr+=`×${p}`});
    L.push({c:'fin',l:'最终伤害系数',f:logStr+'＝',v:dmg});
  }

  // === Phase 8: Final damage fixed changes ===
  dmg=calcFinalDmgFix(pa,pd,dmg,L);
  dmg=Math.max(dmg,0);

  // === Phase 9: Backlash ===
  const backlash=calcBacklash(pa,dmg,L);

  // === Summary ===
  L.unshift({c:'sum',l:'攻击方',f:`${pa.name} → ${pd.name} (${isCounter?'反击':'先制'})`,v:''});
  L.unshift({c:'sum',l:'武器类型',f:`${pa.wep_kind} (熟练${getWepSkill(pa)}, 系数${CFG.skill_dmg[pa.wep_kind]})`,v:''});

  renderResults(L,dmg,pdamage,exDmg,backlash,pa,pd);
}

// ======================== RENDER RESULTS ========================
function renderResults(L,totalDmg,phyDmg,exDmg,backlash,pa,pd){
  let html='';
  // Summary box
  html+=`<div class="card" style="margin-bottom:8px;border:2px solid var(--green)">`;
  html+=`<div class="total">总伤害: ${totalDmg} 点</div>`;
  html+=`<div style="display:flex;justify-content:space-around;text-align:center">`;
  html+=`<div><span class="lb" style="color:var(--yellow)">物理伤害</span><br><span style="color:var(--yellow);font-size:18px;font-weight:bold">${phyDmg}</span></div>`;
  html+=`<div><span class="lb" style="color:var(--cyan)">属性伤害</span><br><span style="color:var(--cyan);font-size:18px;font-weight:bold">${exDmg}</span></div>`;
  html+=`<div><span class="lb" style="color:var(--purple)">反噬伤害</span><br><span style="color:var(--purple);font-size:18px;font-weight:bold">${backlash}</span></div>`;
  const kills = totalDmg>=pd.hp;
  html+=`<div><span class="lb" style="color:var(--red)">击杀?</span><br><span style="color:var(--red);font-size:18px;font-weight:bold">${kills?'✓ 击杀':'✗ 存活'}</span></div>`;
  html+=`</div>`;
  if(kills){
    html+=`<div style="text-align:center;color:var(--green);margin-top:4px">${pa.name} 对 ${pd.name} 造成了致命伤害！(${totalDmg} ≥ ${pd.hp} HP)</div>`;
  }else{
    const hits=Math.ceil(pd.hp/totalDmg);
    html+=`<div style="text-align:center;color:var(--orange);margin-top:4px">预计需要 ${hits} 次攻击击杀 (${pd.hp} HP ÷ ${totalDmg}/次)</div>`;
  }
  html+=`</div>`;

  // Step-by-step log
  const cls={phy:'physical',ele:'elemental',fin:'final',bsh:'backlash',sum:'summary'};
  const clsMap={phy:'phy',ele:'ele',fin:'fin',bsh:'bsh',sum:'sum'};
  L.forEach(step=>{
    const c=clsMap[step.c]||'';
    html+=`<div class="step ${c}">`;
    html+=`<div class="lb">${step.l}</div>`;
    if(step.f)html+=`<div class="fm">${step.f}</div>`;
    html+=`<div class="vl">${step.v}</div>`;
    html+=`</div>`;
  });

  $('results').innerHTML=html;
}

// ======================== PRESET LOADING ========================
function loadPreset(side,name){
  if(!name||!PRESETS[name])return;
  const p=PRESETS[name];
  if(side==='pa'){
    $('pa_name').value=p.name;$('pa_type').value=p.type;$('pa_att').value=p.att;$('pa_def').value=p.def;
    $('pa_lvl').value=p.lvl;$('pa_hp').value=p.hp;$('pa_mhp').value=p.mhp;$('pa_sp').value=p.sp;$('pa_msp').value=p.msp;
    $('pa_club').value=p.club;$('pa_pose').value=p.pose;$('pa_tactic').value=p.tactic;$('pa_inf').value=p.inf;
    $('pa_wepk').value=p.wepk;$('pa_wepe').value=p.wepe;$('pa_weps').value=p.weps;$('pa_wepsk').value=p.wepsk;
    $('pa_wp').value=p.wp;$('pa_wk').value=p.wk;$('pa_wg').value=p.wg;$('pa_wc').value=p.wc;$('pa_wd').value=p.wd;$('pa_wf').value=p.wf;
    $('pa_artsk').value=p.artsk||'';$('pa_arbsk').value=p.arbsk||'';$('pa_arhsk').value=p.arhsk||'';
    $('pa_arask').value=p.arask||'';$('pa_arfsk').value=p.arfsk||'';
    PA_SKILLS.forEach(s=>{const el=$('pa_sk_'+s.id);if(el)el.checked=!!(p.skills&&p.skills['sk_'+s.id])});
    // Refresh attribute name displays
    ['pa_wepsk','pa_artsk','pa_arbsk','pa_arhsk','pa_arask','pa_arfsk'].forEach(id=>updateSkDisplay(id,id+'_display'));
  }else{
    $('pd_name').value=p.name;$('pd_type').value=p.type;$('pd_def').value=p.def;$('pd_lvl').value=p.lvl;
    $('pd_hp').value=p.hp;$('pd_mhp').value=p.mhp;$('pd_club').value=p.club;$('pd_pose').value=p.pose;
    $('pd_tactic').value=p.tactic;$('pd_inf').value=p.inf;
    $('pd_wepk').value=p.wepk;$('pd_wepe').value=p.wepe;$('pd_weps').value=p.weps;$('pd_wepsk').value=p.wepsk;
    $('pd_wp').value=p.wp;$('pd_wk').value=p.wk;$('pd_wg').value=p.wg;$('pd_wc').value=p.wc;$('pd_wd').value=p.wd;$('pd_wf').value=p.wf;
    $('pd_arbe').value=p.arbe||0;$('pd_arhe').value=p.arhe||0;$('pd_arae').value=p.arae||0;$('pd_arfe').value=p.arfe||0;
    $('pd_arbs').value=p.arbs||0;$('pd_arhs').value=p.arhs||0;$('pd_aras').value=p.aras||0;$('pd_arfs').value=p.arfs||0;
    $('pd_arbsk').value=p.arbsk||'';$('pd_arhsk').value=p.arhsk||'';$('pd_arask').value=p.arask||'';$('pd_arfsk').value=p.arfsk||'';
    $('pd_artsk').value=p.artsk||'';
    PD_SKILLS.forEach(s=>{const el=$('pd_sk_'+s.id);if(el)el.checked=!!(p.skills&&p.skills['skill_'+s.id])});
    // Refresh attribute name displays
    ['pd_wepsk','pd_artsk','pd_arbsk','pd_arhsk','pd_arask','pd_arfsk'].forEach(id=>updateSkDisplay(id,id+'_display'));
  }
}

// ======================== INIT ========================
buildAttackerUI();
buildDefenderUI();
buildEnvUI();

// Attribute chart
function toggleChart(){
  const el=$('attr_chart');
  if(el.style.display==='none'){
    let html='<div class="card" style="border:1px solid var(--border)"><h2>📋 武器/防具属性对照表</h2>';
    html+='<table style="width:100%;border-collapse:collapse;font-size:12px">';
    html+='<tr style="border-bottom:1px solid var(--border)"><th style="text-align:left;padding:4px;color:var(--yellow)">代码</th><th style="text-align:left;padding:4px;color:var(--yellow)">中文名</th><th style="text-align:left;padding:4px;color:var(--yellow)">类型</th><th style="text-align:left;padding:4px;color:var(--yellow)">说明</th></tr>';
    const descs={
      A:'物理防御(全系)',a:'属性防御(半减)',B:'物理抹消(免疫)',b:'属性抹消(免疫)',C:'防投',c:'重击辅助(猛击)',
      D:'防爆',d:'爆炸',E:'绝缘(防电)',e:'电击(属性攻击)',F:'防符(灵力半减)',f:'灼焰(属性攻击)',
      G:'防弹(射击半减)',g:'同志(互不伤害)',H:'HP制御(反噬÷10)',h:'伤害制御(最终伤害半减)',
      I:'防冻',i:'冻气(属性攻击)',J:'超量素材',j:'多重',K:'防斩',k:'冰华(属性攻击)',
      L:'致残',l:'热恋(伤害半减)',M:'陷阱探测',m:'陷阱迎击',N:'冲击(防具半减)',n:'贯穿(防具半减)',
      o:'一发',P:'防殴',p:'带毒(属性攻击)',q:'防毒',R:'混沌',r:'连击',
      S:'消音',s:'调整',U:'防火',u:'火焰(属性攻击)',V:'诅咒',v:'灵魂绑定',
      W:'隔音',w:'音波(属性攻击)',X:'直死(NPC专用)',x:'奇迹',y:'破格(属穿)',Z:'菁英',z:'天然'
    };
    const types={A:'防具',a:'防具',B:'防具',b:'防具',C:'防具',c:'武器/饰品',D:'防具',d:'武器',E:'防具',e:'武器',F:'防具',f:'武器',G:'防具',g:'防具',H:'饰品',h:'防具',I:'防具',i:'武器',J:'武器',j:'武器',K:'防具',k:'武器',L:'武器',l:'防具',M:'防具',m:'防具',N:'武器',n:'武器',o:'武器',P:'防具',p:'武器',q:'防具',R:'武器',r:'武器',S:'武器',s:'武器',U:'防具',u:'武器',V:'武器',v:'武器',W:'防具',w:'武器',X:'武器',x:'武器',y:'武器',Z:'防具/饰品',z:'防具'};
    Object.entries(CFG.itemspkinfo).forEach(([k,v])=>{
      html+=`<tr style="border-bottom:1px solid #222"><td style="padding:3px 8px;color:var(--cyan);font-weight:bold;font-family:monospace">${k}</td><td style="padding:3px 8px">${v}</td><td style="padding:3px 8px;color:var(--muted)">${types[k]||''}</td><td style="padding:3px 8px;color:var(--muted)">${descs[k]||''}</td></tr>`;
    });
    html+='</table>';
    html+='<div style="margin-top:8px;color:var(--muted);font-size:11px">大写=防具属性(防御系) 小写=武器属性(攻击系) 部分属性可出现在武器或防具上</div>';
    html+='</div>';
    el.innerHTML=html;
    el.style.display='block';
  }else{
    el.style.display='none';
  }
}

// Register live attribute name displays
const skFields=[
  ['pa_wepsk','pa_wepsk_display'],['pa_artsk','pa_artsk_display'],['pa_arbsk','pa_arbsk_display'],
  ['pa_arhsk','pa_arhsk_display'],['pa_arask','pa_arask_display'],['pa_arfsk','pa_arfsk_display'],
  ['pd_wepsk','pd_wepsk_display'],['pd_artsk','pd_artsk_display'],['pd_arbsk','pd_arbsk_display'],
  ['pd_arhsk','pd_arhsk_display'],['pd_arask','pd_arask_display'],['pd_arfsk','pd_arfsk_display'],
];
skFields.forEach(([inputId,displayId])=>{
  const el=$(inputId);if(el)el.addEventListener('input',()=>updateSkDisplay(inputId,displayId));
  updateSkDisplay(inputId,displayId);
});
