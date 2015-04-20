<?php
/**
 * Created by PhpStorm.
 * User: Samuel
 * Date: 4/20/2015
 * Time: 12:04 PM
 */

namespace LWM;


class Stats {
    public function __construct() {

    }

    public function getClans() {
        $ret = [];
        $q = mysqli_query(DB::$conn, "SELECT * FROM `clans` ORDER BY `name` DESC");
        while ($row = mysqli_fetch_assoc($q)) {
            $ret[] = $row;
        }
        return $ret;
    }
}