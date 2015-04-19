<?php
set_time_limit(0);
require_once(dirname(__DIR__) . "/autoload.php");

$user = (new \LWM\User($argv[1], $argv[2], $argv[3]));

//echo $user->getContent();
$user->save();