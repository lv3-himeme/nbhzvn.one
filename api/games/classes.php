<?php
class Nbhzvn_Game {
    public $id;
    public $timestamp;
    public $name;
    public $links;
    public $image;
    public $screenshots;
    public $description;
    public $engine;
    public $tags;
    public $release_year;
    public $author;
    public $language;
    public $translator;
    public $uploader;
    public $status;
    public $views;
    public $views_today;
    public $updated_date;
    public $downloads;
    public $supported_os;
    public $is_featured;
    public $approved;

    function __construct($id) {
        $result = db_query('SELECT * FROM `nbhzvn_games` WHERE id = ?', $id);
        while ($row = $result->fetch_object()) {
            $this->id = $row->id;
            $this->timestamp = $row->timestamp;
            $this->name = $row->name;
            $this->links = json_decode($row->links);
            $this->image = $row->image;
            $this->screenshots = json_decode($row->screenshots);
            $this->description = $row->description;
            $this->engine = $row->engine;
            $this->tags = $row->tags;
            $this->release_year = $row->release_year;
            $this->author = $row->author;
            $this->translator = $row->translator;
            $this->uploader = $row->uploader;
            $this->status = $row->status;
            $this->views = $row->views;
            $this->views_today = $row->views_today;
            $this->updated_date = $row->updated_date;
            $this->downloads = $row->downloads;
            $this->supported_os = $row->supported_os;
            $this->is_featured = $row->is_featured;
            $this->approved = $row->approved;
        }
    }

    function add_views() {
        global $conn;
        $this->views = intval($this->views) + 1;
        $this->views_today = intval($this->views_today) + 1;
        db_query('UPDATE `nbhzvn_games` SET `views` = ?, `views_today` = ? WHERE `id` = ?', $this->views, $this->views_today, $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        return SUCCESS;
    }

    function add_downloads_count() {
        global $conn;
        $this->downloads = intval($this->downloads) + 1;
        db_query('UPDATE `nbhzvn_games` SET `downloads` = ? WHERE `id` = ?', $this->downloads, $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        return SUCCESS;
    }

    function approve() {
        global $conn;
        db_query('UPDATE `nbhzvn_games` SET `approved` = 1 WHERE `id` = ?', $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->approved = true;
        return SUCCESS;
    }

    function delete() {
        global $conn;
        db_query('DELETE FROM `nbhzvn_comments` WHERE `game_id` = ?', $this->id);
        db_query('DELETE FROM `nbhzvn_gamefollows` WHERE `game_id` = ?', $this->id);
        db_query('DELETE FROM `nbhzvn_gameratings` WHERE `game_id` = ?', $this->id);
        db_query('DELETE FROM `nbhzvn_games` WHERE `id` = ?', $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->id = null;
        return SUCCESS;
    }

    function ratings() {
        global $conn;
        $total = 0;
        $result = db_query('SELECT `rating` FROM `nbhzvn_gameratings` WHERE `game_id` = ?', $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        while ($row = $result->fetch_object()) $total += intval($row->rating);
        $response = new stdClass();
        $response->count = $result->num_rows;
        $response->average = $total / (($result->num_rows > 0) ? $result->num_rows : 1);
        return $response;
    }

    function follows() {
        global $conn;
        $result = db_query('SELECT `author` FROM `nbhzvn_gamefollows` WHERE `game_id` = ?', $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        return $result->num_rows;
    }

    function check_follow($user_id) {
        global $conn;
        $result = db_query('SELECT `author` FROM `nbhzvn_gamefollows` WHERE `game_id` = ? AND `author` = ?', $this->id, $user_id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        return !!$result->num_rows;
    }

    function check_rating($user_id) {
        global $conn;
        $result = db_query('SELECT `author` FROM `nbhzvn_gameratings` WHERE `game_id` = ? AND `author` = ?', $this->id, $user_id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        return !!$result->num_rows;
    }

    function toggle_follow($user_id) {
        global $conn;
        $result = db_query('SELECT `author` FROM `nbhzvn_gamefollows` WHERE `game_id` = ? AND `author` = ?', $this->id, $user_id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        if ($result->num_rows > 0) {
            db_query('DELETE FROM `nbhzvn_gamefollows` WHERE `game_id` = ? AND `author` = ?', $this->id, $user_id);
            return ACTION_UNFOLLOW;
        }
        db_query('INSERT INTO `nbhzvn_gamefollows` (`author`, `game_id`) VALUES (?, ?)', $user_id, $this->id);
        return ACTION_FOLLOW;
    }

    function toggle_featured() {
        global $conn;
        $new_value = ($this->is_featured ? 0 : 1);
        db_query('UPDATE `nbhzvn_games` SET `is_featured` = ? WHERE `id` = ?', $new_value, $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->is_featured = ($new_value == 1);
        return $new_value;
    }

    function add_rating($user_id, $rating) {
        global $conn;
        $result = db_query('SELECT `rating` FROM `nbhzvn_gameratings` WHERE `game_id` = ? AND `author` = ?', $this->id, $user_id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        if ($result->num_rows > 0) throw new Exception(ALREADY_RATED);
        db_query('INSERT INTO `nbhzvn_gameratings` (`author`, `timestamp`, `game_id`, `rating`) VALUES (?, ?, ?, ?)', $user_id, time(), $this->id, $rating);
        return $this->ratings();
    }

    function comments() {
        global $conn;
        $comments = [];
        $result = db_query('SELECT * FROM `nbhzvn_comments` WHERE `game_id` = ?', $this->id);
        while ($row = $result->fetch_object()) array_push($comments, $row);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        return $comments;
    }

    function edit($data) {
        global $conn;
        $query = []; $values = [];
        foreach ($data as $key => $value) {
            array_push($query, '`' . $key . '` = ?');
            array_push($values, $value);
        }
        array_push($values, $this->id);
        db_query('UPDATE `nbhzvn_games` SET ' . implode(", ", $query) . ' WHERE `id` = ?', ...$values);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        return SUCCESS;
    }
}
?>