<?php
ob_start();
date_default_timezone_set('Pacific/Auckland');
require '../vendor/autoload.php';
require './app/autoload.php';

$app = new \Slim\Slim();
session_start();

function execInBackground($cmd) {
    if (substr(php_uname(), 0, 7) == "Windows"){
        pclose(popen("start /B ". $cmd, "r"));
    }
    else {
        exec($cmd . " > /dev/null &");
    }
}

$app->get('/', function() {
    echo "Nothing to see here";
});

$app->group('/rest', function() use ($app) {
    $app->get('/crawl/:clan', function($clan) {
        execInBackground('php app/fetcher/fetch_clan.php ' . $clan);
    });
});

$app->run();