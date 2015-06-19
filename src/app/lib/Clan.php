<?php
namespace LWM;

class Clan {
    private $id;
    private $content;

    public function __construct($id, $req = true) {
        $this->id = $id;
        if ($req) {
            $this->content = (new Request('http://www.lordswm.com/clan_info.php?id=' . $id))->get();
        }
    }

    public function getID() {
        $id = $this->id;
        $check = mysqli_query(\LWM\DB::$conn, "SELECT * FROM `clans` WHERE `lwm_id`=$id");
        if (mysqli_num_rows($check) == 1) {
            $row = mysqli_fetch_assoc($check);
            return $row["id"];
        } else {
            throw new \Exception("Something went wrong");
        }
    }

    public function getContent() {
        return $this->content;
    }

    public function getMemberIds() {
        preg_match_all("/<td class=(?:wblight|wbwhite) width=150>&nbsp<a href='pl_info\.php\?id=([0-9]+)/", $this->content, $output_array);
        return $output_array[1];
    }

    public function getMemberIdNamePairs() {
        $pairs = [];
        foreach ($this->getMemberIds() as $id) {
            preg_match_all("/<a href='pl_info\.php\?id=$id' class=pi>(.+?)<\/a>/", $this->content, $output_array);
            $pairs[$id] = $output_array[1][0];
        }
        return $pairs;
    }

    public function getName() {
        $id = $this->id;
        preg_match_all("/<b>#$id (.+?)<\/b>/", $this->content, $output_array);
        return $output_array[1][0];
    }

    public function getDBName() {
        $id = $this->getID();
        $check = mysqli_query(DB::$conn, "SELECT `name` FROM `clans` WHERE `id`=$id");
        $row = mysqli_fetch_assoc($check);
        return $row["name"];
    }

    public function latestScan() {
        $id = $this->getID();
        $check = mysqli_query(DB::$conn, "SELECT * FROM `mass_crawls` WHERE `clan` = $id ORDER BY `timestamp` DESC");
        if (mysqli_num_rows($check) > 0) {
            $row = mysqli_fetch_assoc($check);
            return $this->getScan($row["id"]);
        } else {
            return [];
        }
    }

    public function getScan($scan) {
        $id = $this->getID();
        $ret = [];
        $check = mysqli_query(DB::$conn, "SELECT `timestamp`, `users`.`name`, `crawls`.* FROM `clans` JOIN `mass_crawls` ON `clan`=`clans`.`id` JOIN `crawls` ON `crawls`.`crawl` = `mass_crawls`.`id` JOIN `users` ON `users`.`id` = `crawls`.`user` WHERE `clans`.`id` = $id AND `mass_crawls`.`id` = $scan ORDER BY `timestamp` DESC, `user` ASC");
        if (mysqli_num_rows($check) > 0) {
            $temp = null;
            while ($row = mysqli_fetch_assoc($check)) {
                if ($temp == null) {
                    $temp = $row["timestamp"];
                }
                if ($row["timestamp"] == $temp) {
                    unset($row["timestamp"]);
                    unset($row["id"]);
                    unset($row["user"]);
                    unset($row["crawl"]);
                    $ret[] = $row;
                }
            }
        }
        return $ret;
    }

    public function generateDifference($n1, $n2) {
        $scan1 = $this->getScan($n1);
        $scan2 = $this->getScan($n2);
        $finalScans = [];
        foreach ($scan2 as $scan2Indiv) {
            foreach ($scan1 as $scan1Indiv) {
                if ($scan2Indiv["name"] == $scan1Indiv["name"]) {
                    $tempScan = [];
                    foreach ($scan2Indiv as $key=>$value) {
                        if (is_numeric($value)) {
                            $tempScan[$key] = $scan2Indiv[$key] - $scan1Indiv[$key];
                        } else {
                            $tempScan[$key] = $value;
                        }
                    }
                    $finalScans[] = $tempScan;
                }
            }
        }
        return $finalScans;
    }

    public function getScanDate($scan) {
        $check = mysqli_query(DB::$conn, "SELECT DATE(`timestamp`) as 'date' FROM `mass_crawls` WHERE `id`=$scan");
        $row = mysqli_fetch_assoc($check);
        return $row["date"];
    }

    public function getAllScans() {
        $id = $this->getID();
        $ret = [];
        $check = mysqli_query(DB::$conn, "SELECT `id`, DATE(`timestamp`) as 'date' FROM `mass_crawls` WHERE `clan` = $id");
        while ($row = mysqli_fetch_assoc($check)) {
            $ret[] = $row;
        }
        return $ret;
    }

    public function scanProgress() {
        $id = $this->id;
        $check = mysqli_query(DB::$conn, "SELECT `timestamp`, count(`crawls`.`id`) as 'count', `mass_crawls`.`members` FROM `clans` JOIN `mass_crawls` ON `clan`=`clans`.`id` JOIN `crawls` ON `crawls`.`crawl` = `mass_crawls`.`id` WHERE `clans`.`lwm_id` = $id GROUP BY `mass_crawls`.`id` ORDER BY `timestamp` DESC");
        if (mysqli_num_rows($check) == 0) {
            return 0;
        }
        $row = mysqli_fetch_assoc($check);
        if ($row["count"] == $row["members"]) {
            return 0;
        } else {
            return json_encode([$row["count"], $row["members"]]);
        }
    }

    public function newSave() {
        $conn = DB::$conn;
        $id = $this->id;
        $check = mysqli_query($conn, "SELECT * FROM `clans` WHERE `lwm_id`='$id'");
        if (mysqli_num_rows($check) == 0) {
            $name = $this->getName();
            mysqli_query($conn, "INSERT INTO `clans` (`lwm_id`, `name`) VALUES ($id, '$name')");
            $clanID = mysqli_insert_id($conn);
        } else {
            $clanID = mysqli_fetch_assoc($check)["id"];
        }
        $mems = count($this->getMemberIds());
        mysqli_query($conn, "INSERT INTO `mass_crawls` (`clan`, `members`) VALUES ($clanID, $mems)");

        $insert = mysqli_insert_id($conn);
        $path = dirname(dirname(__FILE__));
        foreach ($this->getMemberIds() as $id) {
            while (mysqli_num_rows(mysqli_query(DB::$conn, "SELECT * FROM `crawls` JOIN `users` ON `users`.`id`=`crawls`.`user` WHERE `crawl`=$insert AND `users`.`lwm_id`=$id")) == 0) {
                //shell_exec("php app/fetcher/fetch_user.php " . $id . " " . $clanID . " " . $insert);
                echo shell_exec("php $path/fetcher/fetch_user.php " . $id . " " . $clanID . " " . $insert);
            }
        }
    }
}