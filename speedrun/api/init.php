<?php
db_query("CREATE TABLE IF NOT EXISTS `nbhzvn_speedrunners` (`id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `discord_id` TEXT NOT NULL , `os` TEXT NOT NULL , `start_timestamp` BIGINT NULL , `playtime` BIGINT NULL , `real_playtime` BIGINT NULL , `saves` INT NULL , `ranking` INT NULL , `ban_reason` TEXT NULL , PRIMARY KEY (`id`) , FOREIGN KEY (user_id) REFERENCES nbhzvn_users(id)) ENGINE = InnoDB");

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
    public $ban_reason;

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
        $this->ban_reason = $data->ban_reason;
    }

    function start_game() {
        $time = time();
        $this->start_timestamp = $time;
        db_query("UPDATE `nbhzvn_speedrunners` SET `start_timestamp` = ? WHERE `id` = ?", $time, $this->id);
        return $time;
    }

    function ban($reason) {
        $this->ban_reason = $reason;
        db_query("UPDATE `nbhzvn_speedrunners` SET `ban_reason` = ? WHERE `id` = ?", $reason, $this->id);
    }

    function unban() {
        $this->ban_reason = null;
        db_query("UPDATE `nbhzvn_speedrunners` SET `ban_reason` = NULL WHERE `id` = ?", $this->id);
    }

    function submit($playtime, $real_playtime, $saves, $ranking) {
        $this->playtime = $playtime;
        $this->real_playtime = $real_playtime;
        $this->saves = $saves;
        $this->ranking = $ranking;
        db_query("UPDATE `nbhzvn_speedrunners` SET `playtime` = ?, `real_playtime` = ?, `saves` = ?, `ranking` = ? WHERE `id` = ?", $playtime, $real_playtime, $saves, $ranking, $this->id);
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

function authenticate() {
    $headers = getallheaders();
    if ($_ENV["SPEEDRUN_TOKEN"] != $headers["Authorization"]) api_response(null, "Mã xác thực không đúng.", 401);
}
?>