<?php
require __DIR__ . "/games/classes.php";
require __DIR__ . "/users/classes.php";
require __DIR__ . "/discord/classes.php";

class Nbhzvn_WebData {
    public array $data;
    public int $views_interval = 30;
    public int $downloads_interval = 300;

    function __construct() {
        $this->data = array();
        $this->data["views_timeout"] = array();
        $this->data["downloads_timeout"] = array();
        if ($_COOKIE["nbhzvn_web_data"]) $this->data = json_decode(base64_decode($_COOKIE["nbhzvn_web_data"]), true);
        else $this->save();
    }

    function save() {
        setcookie("nbhzvn_web_data", base64_encode(json_encode($this->data)), time() + 86400);
    }

    function views_timeout(Nbhzvn_Game $game) {
        return $this->data["views_timeout"][$game->id] ? $this->data["views_timeout"][$game->id] : 0;
    }

    function update_views_timeout(Nbhzvn_Game $game) {
        $this->data["views_timeout"][$game->id] = time() + $this->views_interval;
        $this->save();
    }

    function downloads_timeout(Nbhzvn_Game $game) {
        return $this->data["downloads_timeout"][$game->id] ? $this->data["downloads_timeout"][$game->id] : 0;
    }

    function update_downloads_timeout(Nbhzvn_Game $game) {
        $this->data["downloads_timeout"][$game->id] = time() + $this->downloads_interval;
        $this->save();
    }
}
?>