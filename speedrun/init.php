<?php
db_query("CREATE TABLE IF NOT EXISTS `nbhzvn_speedrunners` (`id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `discord_id` TEXT NOT NULL , `os` TEXT NOT NULL , `start_timestamp` BIGINT NULL , `playtime` BIGINT NULL , `real_playtime` BIGINT NULL , `saves` INT NULL , `ranking` INT NULL , PRIMARY KEY (`id`) , FOREIGN KEY (user_id) REFERENCES nbhzvn_users(id)) ENGINE = InnoDB");

class Nbhzvn_Speedrunner {
    public $id;
    public $user_id;
    public $discord_id;
    public $os;
    public $start_timestamp;
    public $playtime;
    public $real_playtime;
    public $saves;
    public $ranking;

    function __construct($id) {
        $this->id = null; $data = new stdClass();
        switch (gettype($id)) {
            case "integer": {
                $result = db_query('SELECT * FROM `nbhzvn_speedrunners` WHERE `user_id` = ?', $id);
                while ($row = $result->fetch_object()) $data = $row;
                break;
            }
            default: {
                $data = $id;
                break;
            }
        }
        $this->id = $data->id;
        $this->user_id = $data->user_id;
        $this->discord_id = $data->discord_id;
        $this->os = $data->os;
        $this->start_timestamp = $data->start_timestamp;
        $this->playtime = $data->playtime;
        $this->real_playtime = $data->real_playtime;
        $this->saves = $data->saves;
        $this->ranking = $data->ranking;
    }
}

function add_speedrunner($user_id, $discord_id, $os) {
    db_query("INSERT INTO `nbhzvn_speedrunners` (user_id, discord_id, os) VALUES (?, ?, ?)", $user_id, $discord_id, $os);
    return true;
}

function check_speedrun_user($discord_id, $participate = false) {
    $url = "http://52.69.122.165:10108/" . ($participate ? "participate" : "check") . "/" . $discord_id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $data = new stdClass();
        $data->pass = false;
        $data->reason = "SERVER_ERROR";
        return $data;
    }
    else {
        curl_close($ch);
        return json_decode($response);
    }
}
?>