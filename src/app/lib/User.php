<?php
/**
 * Created by PhpStorm.
 * User: Samuel
 * Date: 4/19/2015
 * Time: 9:34 PM
 */

namespace LWM;


class User {
    private $id;
    private $clan;
    private $crawl;
    private $content;

    public function __construct($id, $clan, $crawl) {
        $this->id = $id;
        $this->clan = $clan;
        $this->crawl = $crawl;
        $this->content = (new Request('http://www.lordswm.com/pl_info.php?id=' . $id))->get();
    }

    public function getContent() {
        return $this->content;
    }

    public function getName() {
        preg_match_all("/'sms-create.php\?mailto=(.+?)'/", $this->content, $output_array);
        $name = $output_array[1][0];
        if(preg_match('/[^\x20-\x7f]/', $name)) {
            $name = "Russian Name";
        }
        return $name;
    }

    public function getXP() {
        preg_match_all("/<b>Combat level: [0-9]+<\/b> \(([0-9]+.*?)\)/", $this->content, $output_array);
        return $output_array[1][0];
    }

    public function getLevel() {
        preg_match_all("/<b>Combat level: ([0-9]+)<\/b> \(([0-9]+.*?)\)/", $this->content, $output_array);
        return $output_array[1][0];
    }

    public function getResource($name) {
        preg_match_all("/title=(?:'|\")$name(?:'|\") alt=(?:'|\")(?:'|\")><\/td><td><b>([0-9|,]+?)<\/b>/", $this->content, $output_array);
        return intval(str_replace(',', '', $output_array[1][0]));
    }

    public function getElement($name) {
        preg_match_all("/<b>$name<\/b>: ([0-9]+?)</", $this->content, $output_array);
        if (count($output_array[1]) >= 1) {
            return intval(str_replace(',', '', $output_array[1][0]));
        } else {
            return 0;
        }
    }

    public function elementValue() {
        $elems = array(
            "Meteorite shard" => 1600,
            "Moonstone" => 4200,
            "Windflower" => 2800,
            "Viper venom" => 300,
            "Ice crystal" => 2200,
            "Fern flower" => 1800,
            "Fire crystal" => 1500,
            "Steel" => 750,
            "Abrasive" => 150,
            "Toadstool" => 150,
            "Leather" => 184,
            "Tiger's Claw" => 2800,
            "Nickel" => 1700,
            "Witch Bloom" => 150,
            "Magic powder" => 2078
        );
        $total = 0;
        foreach ($elems as $elem=>$cost) {
            $total += $this->getElement($elem) * $cost;
        }
        return $total;
    }

    public function getNetRoulette() {
        preg_match_all("/Roulette bets total: <b>([0-9]+?)</", $this->content, $output_array);
        $out = intval(str_replace(',', '', $output_array[1][0]));
        preg_match_all("/Roulette winnings total: <b>([0-9]+?)</", $this->content, $output_array);
        $in = intval(str_replace(',', '', $output_array[1][0]));
        return $in - $out;
    }

    public function getBattlesWon() {
        preg_match_all("/<td width=(?:'|\")[0-9]0%(?:'|\")>&nbsp;&nbsp;Victories:.+?<b>([0-9]+.+?)<\/b><\/td>/", $this->content, $output_array);
        return intval(str_replace(',', '', $output_array[1][0]));
    }

    public function getBattlesLost() {
        preg_match_all("/<tr><td width=(?:'|\")[0-9]0%(?:'|\")>&nbsp;&nbsp;Defeats:.+?<b>([0-9]+.+?)<\/b><\/td>/", $this->content, $output_array);
        return intval(str_replace(',', '', $output_array[1][0]));
    }

    public function getTavernLost() {
        preg_match_all("/<\/td><td width=(?:'|\")[0-9]0%(?:'|\")>&nbsp;&nbsp;Defeats:.+?<b>([0-9]+.+?)<\/b><\/td>/", $this->content, $output_array);
        return intval(str_replace(',', '', $output_array[1][0]));
    }

    public function getTavernWon() {
        preg_match_all("/<td>&nbsp;&nbsp;Victories:.+?<b>([0-9]+.+?)<\/b><\/td>/", $this->content, $output_array);
        return intval(str_replace(',', '', $output_array[1][0]));
    }

    public function getFSP($faction) {
        preg_match_all("/(?:<b>)?$faction: [0-9]+(?:<\/b>)? \(([0-9|,|\.]+?)\)/", $this->content, $output_array);
        return floatval(str_replace(',', '', $output_array[1][0]));
    }

    public function getGuild($guild) {
        preg_match_all("/(?:<b>)?$guild [g|G]uild: (?:<a.*?>)?[0-9]+(?:<\/a>)?(?:<\/b>)? \(([0-9|,|\.]+?)\)/", $this->content, $output_array);
        return str_replace(',', '', $output_array[1][0]);
    }

    public function save() {
        $id = $this->id;
        $check = mysqli_query(DB::$conn, "SELECT * FROM `users` WHERE `lwm_id`=$id");
        if (mysqli_num_rows($check) == 0) {
            $clanID = $this->clan;
            $name = $this->getName();
            mysqli_query(DB::$conn, "INSERT INTO `users` (`lwm_id`, `name`, `clan`) VALUES ($id, '$name', $clanID)");
            $playerID = mysqli_insert_id(DB::$conn);
        } else {
            $playerID = mysqli_fetch_assoc($check)["id"];
        }
        $crawl = $this->crawl;

        $XP = $this->getXP();
        $level = $this->getLevel();

        $gold = $this->getResource("Gold");
        $wood = $this->getResource("Wood");
        $ore = $this->getResource("Ore");
        $mercury = $this->getResource("Mercury");
        $sulfur = $this->getResource("Sulfur");
        $crystals = $this->getResource("Crystals");
        $gems = $this->getResource("Gems");

        $wealth = (($gems + $crystals) * 360) + (($wood + $ore + $mercury + $sulfur) * 180) + $gold + $this->elementValue();

        $roulette = $this->getNetRoulette();

        $battlesWon = $this->getBattlesWon();
        $battlesLost = $this->getBattlesLost();
        $battlesTotal = $battlesWon + $battlesLost;

        $tavernLost = $this->getTavernLost();
        $tavernWon = $this->getTavernWon();

        $knight = $this->getFSP("Knight");
        $necro = $this->getFSP("Necromancer");
        $wizard = $this->getFSP("Wizard");
        $elf = $this->getFSP("Elf");
        $barb = $this->getFSP("Barbarian");
        $darkelf = $this->getFSP("Dark elf");
        $demon = $this->getFSP("Demon");
        $dwarf = $this->getFSP("Dwarf");
        $tribal = $this->getFSP("Tribal");
        $totalFSP = $knight + $necro + $wizard + $elf + $barb + $darkelf + $demon + $dwarf + $tribal;

        $hg = $this->getGuild("Hunters'");
        $lg = $this->getGuild("Laborers'");
        $gg = $this->getGuild("Gamblers'");
        $tg = $this->getGuild("Thieves'");
        $rg = $this->getGuild("Rangers'");
        $mg = $this->getGuild("Mercenaries'");
        $cg = $this->getGuild("Commanders'");
        $sg = $this->getGuild("Smiths'");
        $eg = $this->getGuild("Enchanters'");

        $query = "INSERT INTO `crawls`(`user`, `crawl`, `XP`, `Level`, `Gold`, `Wood`, `Ore`, `Mercury`, `Sulfur`, `Crystals`, `Gems`, `Wealth`, `Routlette Winnings`, `Battles Won`," .
            " `Battles Lost`, `Total Battles`, `Tavern Lost`, `Tavern Won`, `Knight`, `Necromancer`, `Wizard`, `Elf`, `Barbarian`, `Dark_Elf`, `Demon`, `Dwarf`, `Tribal`, `Total FSP`, `HG`, `LG`, `GG`, `TG`, `RG`, `MG`, `CG`, `SG`, `EG`)" .
            " VALUES " .
            "($playerID, $crawl, $XP, $level, $gold, $wood,$ore,$mercury,$sulfur,$crystals,$gems, $wealth,$roulette,$battlesWon,$battlesLost,$battlesTotal,$tavernLost,$tavernWon," .
            "$knight,$necro,$wizard,$elf,$barb,$darkelf,$demon,$dwarf,$tribal,$totalFSP,$hg,$lg,$gg,$tg,$rg,$mg,$cg,$sg,$eg)";
        mysqli_query(DB::$conn, $query);
    }
}