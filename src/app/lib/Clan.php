<?php
namespace LWM;

class Clan {
    private $id;
    private $content;

    public function __construct($id) {
        $this->id = $id;
        $this->content = (new Request('http://www.lordswm.com/clan_info.php?id=' . $id))->get();
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

    public function lastCrawl() {

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
        mysqli_query($conn, "INSERT INTO `mass_crawls` (`clan`) VALUES ($clanID)");

        $insert = mysqli_insert_id($conn);
        foreach ($this->getMemberIds() as $id) {
            shell_exec("php app/fetcher/fetch_user.php " . $id . " " . $clanID . " " . $insert);
        }
    }
}