<?php
/**
 * Unified NPC template entry point (Strategy A wrapper).
 *
 * Includes all three NPC config files and provides their variables
 * in the includer's scope:
 *   - npc_1.php     => $npcinit, $npcinfo, $npcdescription
 *   - addnpc_1.php  => $anpcinfo
 *   - evonpc_1.php  => $enpcinfo
 *
 * Uses include (not include_once) so that variables are always set
 * in the caller's scope, even when included from different functions
 * in the same request.
 */

if(!defined('IN_GAME')) exit('Access Denied');

global $gamecfg;
if(empty($gamecfg)) $gamecfg = 1;

include config('npc', $gamecfg);
include config('addnpc', $gamecfg);
include config('evonpc', $gamecfg);
