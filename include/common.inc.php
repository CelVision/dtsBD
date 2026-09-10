<?php

//set_magic_quotes_runtime(0);

define('IN_GAME', TRUE);
define('GAME_ROOT', substr(dirname(__FILE__), 0, -7));
define('GAMENAME', 'bra');

if(version_compare(PHP_VERSION, '4.3.0', '<')) {
	exit('PHP version must >= 4.3.0!');
}
require GAME_ROOT.'./include/global.func.php';
require GAME_ROOT.'./include/system.func.php';
require GAME_ROOT.'./include/user.func.php';
error_reporting(E_ALL);
set_error_handler('gameerrorhandler');
$magic_quotes_gpc = false;
extract(gstrfilter($_COOKIE));
extract(gstrfilter($_POST));
extract(gstrfilter($_GET));
//$_GET = gstrfilter($_GET);
$_REQUEST = gstrfilter($_REQUEST);
$_FILES = gstrfilter($_FILES);

require GAME_ROOT.'./config.inc.php';



//$errorinfo ? error_reporting(E_ALL) : error_reporting(0);
date_default_timezone_set('Etc/GMT');
//$now = time() + $moveutmin*60;
$now = time() + $moveut*3600 + $moveutmin*60;   
list($sec,$min,$hour,$day,$month,$year,$wday) = explode(',',date("s,i,H,j,n,Y,w",$now));


//if($attackevasive) {
//	include_once GAME_ROOT.'./include/security.inc.php';
//}

require GAME_ROOT.'./include/db_'.$database.'.class.php';
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
//$db->select_db($dbname);
unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

require GAME_ROOT.'./gamedata/system.php';
require GAME_ROOT.'./include/init.func.php';
require GAME_ROOT.'./include/news.func.php';
require GAME_ROOT.'./include/resources.func.php';
require GAME_ROOT.'./include/roommng.func.php';
require GAME_ROOT.'./include/game/revclubskills.func.php';
require GAME_ROOT.'./include/game/dice.func.php';
require GAME_ROOT.'./include/game/titles.func.php';
//房间配置探测：玩家所在房间绑定的游戏模式号（game表gamecfg列），决定下方所有config()按几号加载
//必须先于require config(...)系列执行——进快速模式房间后全套配置按2号加载，缺号自动回退1号
$gtablepre = $tablepre;
$cuser = & ${$gtablepre.'user'};
$cpass = & ${$gtablepre.'pass'};
$room_gamecfg = roommng_resolve_gamecfg($cuser);
if(!empty($room_gamecfg)) $gamecfg = $room_gamecfg;
// 房间私有resource副本（gameresource_room_{id}）：房间成员的gameresource类config()调用优先命中自己的副本
// （各房间配置互不影响；副本由建房派生/关房删除，见roommng_spawn_room_resource）
$room_resource_id = 0;
if(!empty($room_resolved_id) && file_exists(GAME_ROOT.'./gamedata/cache/gameresource_room_'.$room_resolved_id.'.php')) {
	$room_resource_id = (int)$room_resolved_id;
}

require config('resources',$gamecfg);
require config('gamecfg',$gamecfg);
require config('combatcfg',$gamecfg);
require config('clubskills',$gamecfg);
require config('dialogue',$gamecfg);
require config('audio',$gamecfg);
require config('tooltip',$gamecfg);
require config('titles',$gamecfg);

if($need_update_db_structrue) roommng_verify_db_game_structure();

ob_start();

$roomlist = Array();
$result = $db->query("SELECT * FROM {$gtablepre}game WHERE groomid>0");
while($roominfo = $db->fetch_array($result))
{
	$roomlist[$roominfo['groomid']] = $roominfo;
}

if($cuser) $udata = fetch_userdata_by_username($cuser);

$groomid = isset($udata['roomid']) ? $udata['roomid'] : 0;

if(!empty($groomid))
{
	$result = $db->query("SELECT * FROM {$gtablepre}game WHERE groomid='$groomid'");
	if(!$db->num_rows($result))
	{
		roommng_create_new_room($udata);
		/*$gr = $db->query("SELECT gamenum FROM {$gtablepre}game WHERE groomid=0");
		$gnums = $db->result($result, 0) + $groomid;
		$starttime = $now + $startmin*5;
		$db->query("INSERT INTO {$gtablepre}game (gamenum,groomid,groomnums,gamestate,starttime) VALUES ('$gnums','$groomid','1','0','$starttime')");*/
	}
}

$tablepre = !empty($groomid) ? $tablepre.'s'.$groomid.'_' : $tablepre;

if(CURSCRIPT !== 'chat')
{
	$lockfile = !empty($groomid) ? "process_room{$groomid}.lock" : 'process.lock';
	$plock=fopen(GAME_ROOT.'./gamedata/'.$lockfile,'ab');
	flock($plock,LOCK_EX);
	load_gameinfo();
	advance_gamestate();
	
	//除拉取聊天以外的访问都判定一下是否有新的站内信。
	include_once GAME_ROOT.'./include/messages.func.php';
	$new_messages = message_check_new($cuser);
	
	fclose($plock); 
}
?>
