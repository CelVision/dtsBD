<?php
/**
 * One-time conversion script: transforms flat CSV mapitem config
 * into structured heredoc format grouped by [map_id][branch].
 *
 * Place in game root, access via browser once, then delete.
 * Output: gamedata/cache/mapitemresource_1.php
 */
define('IN_GAME', true);
$root = __DIR__ . DIRECTORY_SEPARATOR;

$src = $root . 'gamedata/cache/mapitem_1.php';
$dst = $root . 'gamedata/cache/mapitemresource_1.php';

$lines = file($src, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Group items by imap => branch => CSV lines
$mapitems = array();

foreach ($lines as $line) {
    if (strpos($line, '<?') === 0) continue;
    if (strpos($line, '//') === 0) continue;
    if (strpos($line, '?>') === 0) continue;
    if (empty($line) || strpos($line, ',') === false) continue;

    $parts = explode(',', $line);
    if (count($parts) < 8) continue;

    $iarea  = trim($parts[0]);
    $imap   = trim($parts[1]);
    $inum   = trim($parts[2]);
    $iname  = trim($parts[3]);
    $ikind  = trim($parts[4]);
    $ieff   = trim($parts[5]);
    $ista   = trim($parts[6]);
    $iskind = trim($parts[7]);

    $branch = 0;
    if (!isset($mapitems[$imap])) $mapitems[$imap] = array();
    if (!isset($mapitems[$imap][$branch])) $mapitems[$imap][$branch] = '';
    // Build CSV line (without imap, since it's the key now)
    $mapitems[$imap][$branch] .= "{$iarea},{$inum},{$iname},{$ikind},{$ieff},{$ista},{$iskind},\n";
}

ksort($mapitems);

// Generate heredoc-format PHP file
// Ensure every map has both branch 0 and branch 1
$allMapIds = array_keys($mapitems);
$minMap = min($allMapIds);
$maxMap = max($allMapIds);

$out = "<?php\n\n";
$out .= "// Map item resource - structured by [map_id][branch]\n";
$out .= "// Each line: area,num,name,kind,eff,sta,skind\n";
$out .= "// area: 0=start, 1=1st禁区, ..., 99=every禁区\n";
$out .= "// map_id 99 = random map spawn pool\n";
$out .= "// Branch corresponds to \$mapid[map_id] selection\n\n";
$out .= "\$mapitems_str = Array\n(\n";

foreach ($mapitems as $mapId => $branches) {
    ksort($branches);
    $out .= "  {$mapId} => Array\n  (\n";
    // Branch 0: items (if any)
    if (isset($branches[0])) {
        $csv = rtrim($branches[0]);
        $out .= "    0 => <<<EOT\n{$csv}\nEOT,\n";
    } else {
        $out .= "    0 => <<<EOT\nEOT,\n";
    }
    // Branch 1: empty, for user to fill in
    if (isset($branches[1])) {
        $csv = rtrim($branches[1]);
        $out .= "    1 => <<<EOT\n{$csv}\nEOT,\n";
    } else {
        $out .= "    1 => <<<EOT\nEOT,\n";
    }
    $out .= "  ),\n";
}

$out .= ");\n\n";
// Self-parsing block: converts strings to array structure on include
$out .= "// Parse heredoc strings into array structure\n";
$out .= "\$mapitems = Array();\n";
$out .= "foreach(\$mapitems_str as \$_imap => \$_branches)\n";
$out .= "{\n";
$out .= "  foreach(\$_branches as \$_ibranch => \$_str)\n";
$out .= "  {\n";
$out .= "    \$_lines = explode(\"\\n\", trim(\$_str));\n";
$out .= "    foreach(\$_lines as \$_line)\n";
$out .= "    {\n";
$out .= "      \$_line = trim(\$_line);\n";
$out .= "      if(empty(\$_line) || strpos(\$_line, ',') === false) continue;\n";
$out .= "      \$_p = explode(',', \$_line);\n";
$out .= "      if(count(\$_p) < 7) continue;\n";
$out .= "      \$mapitems[\$_imap][\$_ibranch][] = Array(\n";
$out .= "        trim(\$_p[0]), trim(\$_p[1]), trim(\$_p[2]),\n";
$out .= "        trim(\$_p[3]), trim(\$_p[4]), trim(\$_p[5]), trim(\$_p[6])\n";
$out .= "      );\n";
$out .= "    }\n";
$out .= "  }\n";
$out .= "}\n";
$out .= "unset(\$mapitems_str);\n\n";
$out .= "?>\n";

file_put_contents($dst, $out);

$itemCount = 0;
foreach ($mapitems as $branches) {
    foreach ($branches as $csv) {
        $itemCount += substr_count($csv, "\n");
    }
}

echo "Conversion complete!<br>";
echo "Source: $src<br>";
echo "Output: $dst<br>";
echo "Map IDs: " . count($mapitems) . "<br>";
echo "Total items: $itemCount<br>";
echo "<br>Please verify the output file, then delete this script.<br>";
