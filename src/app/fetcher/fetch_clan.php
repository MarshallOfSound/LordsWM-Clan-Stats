<?php
set_time_limit(0);
require_once(dirname(__DIR__) . "/autoload.php");
$clanID = $argv[1];
$clan = new \LWM\Clan($clanID);

$clan->newSave();

foreach ($clan->getMemberIds() as $id) {

}