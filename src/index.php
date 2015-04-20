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
    include TEMPLATE_DIR . "/home.php";
});

$app->group('/clan/:id', function() use ($app) {
    $app->get('(/view/:number)', function($id, $number = null) use ($app) {
        $check = mysqli_query(\LWM\DB::$conn, "SELECT * FROM `clans` WHERE `id`=$id");
        if (mysqli_num_rows($check) == 1) {
            $row = mysqli_fetch_assoc($check);
            $clanID = $row["lwm_id"];
        } else {
            $app->redirect('/');
        }
        $clan = (new \LWM\Clan($clanID, false));
        if ($number == null) {
            $scans = $clan->latestScan();
            $viewing = "Latest Scan";
        } else {
            $scans = $clan->getScan($number);
            $viewing = $clan->getScanDate($number);
        }
        include TEMPLATE_DIR . "/clan.php";
    });

    $app->get('/compare/:number/:number2', function($id, $number1, $number2) use ($app) {
        $check = mysqli_query(\LWM\DB::$conn, "SELECT * FROM `clans` WHERE `id`=$id");
        if (mysqli_num_rows($check) == 1) {
            $row = mysqli_fetch_assoc($check);
            $clanID = $row["lwm_id"];
        } else {
            $app->redirect('/');
        }
        $clan = (new \LWM\Clan($clanID, false));

        $scans = $clan->generateDifference($number1, $number2);
        $viewing = $clan->getScanDate($number1) . " - " . $clan->getScanDate($number2);
        include TEMPLATE_DIR . "/clan.php";
    });
});

$app->group('/rest', function() use ($app) {
    $app->group('/crawl', function() use ($app) {
        $app->get('/:clan', function($clan) {
            $clanID = $clan;
            $clan = new \LWM\Clan($clan, false);
            if ($clan->scanProgress() == "0") {
                $check = mysqli_query(\LWM\DB::$conn, "SELECT * FROM `mass_crawls` WHERE DATE(`timestamp`) = DATE(CURRENT_TIMESTAMP)");
                if (mysqli_num_rows($check) == 0) {
                    execInBackground('php app/fetcher/fetch_clan.php ' . $clanID);
                } else {
                    echo "Already scanned today";
                }
            } else {
                echo "Scan already in progress";
            }
        });

        $app->get('/:clan/status', function($clan) {
            $clan = new \LWM\Clan($clan, false);
            echo $clan->scanProgress();
        });
    });
});

$app->run();