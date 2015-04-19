<?php
require(dirname(dirname(__DIR__)) . "/vendor/autoload.php");

define("APP_DIR", __DIR__);
define("CLASS_DIR", APP_DIR . "/lib");
define("ROUTE_DIR", APP_DIR . "/routes");
define("TEMPLATE_DIR", APP_DIR . "/templates");

foreach (glob(CLASS_DIR . "/*.php") as $filename) {
    require_once($filename);
}

\LWM\DB::$conn = mysqli_connect("localhost", "root", "rootpass", "lwm_crawl");