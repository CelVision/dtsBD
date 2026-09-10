<?php
// check_gamestate.php - Query current game state from DB
require __DIR__ . '/config.inc.php';

$mysqli = new mysqli($dbhost, $dbuser, $dbpw, $dbname);
if ($mysqli->connect_errno) {
    die("DB connect failed: " . $mysqli->connect_error . "\n");
}

// Game table
$res = $mysqli->query("SELECT groomid, gamestate, gamenum, validnum, alivenum, deathnum, areanum, arealist, weather, winmode, winner, lastupdate, starttime FROM {$tablepre}game ORDER BY groomid");
if ($res && $res->num_rows > 0) {
    echo "=== Game Table ===\n";
    while ($row = $res->fetch_assoc()) {
        echo sprintf("  Room %d: state=%s gamenum=%s alive=%s dead=%s valid=%s area=%s weather=%s winmode=%s winner=%s\n",
            $row['groomid'], $row['gamestate'], $row['gamenum'], $row['alivenum'], $row['deathnum'],
            $row['validnum'], $row['areanum'], $row['weather'], $row['winmode'], $row['winner']);
        echo "  arealist: " . substr($row['arealist'], 0, 80) . "...\n";
        echo "  lastupdate: " . date('Y-m-d H:i:s', $row['lastupdate']) . "  starttime: " . date('Y-m-d H:i:s', $row['starttime']) . "\n";
    }
} else {
    echo "No game records found.\n";
}

// Player counts
$res = $mysqli->query("SELECT type, COUNT(*) as cnt, SUM(CASE WHEN hp>0 AND state<10 THEN 1 ELSE 0 END) as alive_cnt FROM {$tablepre}players GROUP BY type");
if ($res) {
    echo "\n=== Players ===\n";
    while ($row = $res->fetch_assoc()) {
        $typeLabel = $row['type'] == 0 ? 'Human' : 'NPC(type=' . $row['type'] . ')';
        echo "  $typeLabel: total={$row['cnt']} alive={$row['alive_cnt']}\n";
    }
}

// Recent news
$res = $mysqli->query("SELECT * FROM {$tablepre}newsinfo ORDER BY nid DESC LIMIT 5");
if ($res && $res->num_rows > 0) {
    echo "\n=== Recent News (last 5) ===\n";
    while ($row = $res->fetch_assoc()) {
        echo "  [{$row['nid']}] " . date('m-d H:i:s', $row['time']) . " news={$row['news']} a={$row['a']} b={$row['b']}\n";
    }
}

// Recent chat
$res = $mysqli->query("SELECT * FROM {$tablepre}chat ORDER BY cid DESC LIMIT 5");
if ($res && $res->num_rows > 0) {
    echo "\n=== Recent Chat (last 5) ===\n";
    while ($row = $res->fetch_assoc()) {
        echo "  [{$row['cid']}] " . date('m-d H:i:s', $row['time']) . " type={$row['type']} send={$row['send']} msg=" . mb_substr($row['msg'], 0, 40) . "\n";
    }
}

// Lock files
echo "\n=== Lock Files ===\n";
$lockFiles = glob(__DIR__ . '/gamedata/process*.lock');
foreach ($lockFiles as $lf) {
    echo "  " . basename($lf) . " (" . filesize($lf) . " bytes, modified " . date('H:i:s', filemtime($lf)) . ")\n";
}
if (empty($lockFiles)) echo "  (none)\n";

$mysqli->close();
