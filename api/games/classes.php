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
        if (is_object($id)) $data = $id;
        else {
            $result = db_query('SELECT * FROM `nbhzvn_games` WHERE id = ?', $id);
            while ($row = $result->fetch_object()) $data = $row;
        }
        $this->id = $data->id;
        $this->timestamp = $data->timestamp;
        $this->name = $data->name;
        $this->links = json_decode($data->links);
        $this->image = $data->image;
        $this->screenshots = json_decode($data->screenshots);
        $this->description = $data->description;
        $this->engine = $data->engine;
        $this->tags = $data->tags;
        $this->release_year = $data->release_year;
        $this->author = $data->author;
        $this->language = $data->language;
        $this->translator = $data->translator;
        $this->uploader = $data->uploader;
        $this->status = $data->status;
        $this->views = $data->views;
        $this->views_today = $data->views_today;
        $this->updated_date = $data->updated_date;
        $this->downloads = $data->downloads;
        $this->supported_os = $data->supported_os;
        $this->is_featured = $data->is_featured;
        $this->approved = ($data->approved == 1);
    }

    function add_views() {
        $this->views = intval($this->views) + 1;
        $this->views_today = intval($this->views_today) + 1;
        db_query('UPDATE `nbhzvn_games` SET `views` = ?, `views_today` = ? WHERE `id` = ?', $this->views, $this->views_today, $this->id);
        return SUCCESS;
    }

    function add_downloads_count() {
        $this->downloads = intval($this->downloads) + 1;
        db_query('UPDATE `nbhzvn_games` SET `downloads` = ? WHERE `id` = ?', $this->downloads, $this->id);
        return SUCCESS;
    }

    function approve() {
        db_query('UPDATE `nbhzvn_games` SET `approved` = 1 WHERE `id` = ?', $this->id);
        $this->approved = true;
        return SUCCESS;
    }

    function delete() {
        db_query('DELETE FROM `nbhzvn_comments` WHERE `game_id` = ?', $this->id);
        db_query('DELETE FROM `nbhzvn_gamefollows` WHERE `game_id` = ?', $this->id);
        db_query('DELETE FROM `nbhzvn_gameratings` WHERE `game_id` = ?', $this->id);
        db_query('DELETE FROM `nbhzvn_games` WHERE `id` = ?', $this->id);
        $this->id = null;
        return SUCCESS;
    }

    function ratings() {
        $result = db_query('SELECT g.`id`, COUNT(r.`game_id`) AS total, AVG(r.`rating`) AS average FROM `nbhzvn_gameratings` r LEFT JOIN `nbhzvn_games` g ON r.`game_id` = g.`id` WHERE g.`id` = ?', $this->id);
        while ($row = $result->fetch_object()) {
            if (!$row->average) $row->average = 0;
            $row->average = floatval($row->average);
            return $row;
        }
        return new stdClass();
    }

    function follows() {
        $result = db_query('SELECT g.`id`, COUNT(f.`game_id`) AS follow_count FROM `nbhzvn_gamefollows` f LEFT JOIN `nbhzvn_games` g ON f.`game_id` = g.`id` WHERE g.`id` = ?', $this->id);
        while ($row = $result->fetch_object()) return $row->follow_count;
        return 0;
    }

    function followers() {
        $followers = [];
        $result = db_query('SELECT `author` FROM `nbhzvn_gamefollows` WHERE `game_id` = ?', $this->id);
        while ($row = $result->fetch_object()) array_push($followers, new Nbhzvn_User($row->author));
        return $followers;
    }

    function check_follow($user_id) {
        $result = db_query('SELECT `author` FROM `nbhzvn_gamefollows` WHERE `game_id` = ? AND `author` = ?', $this->id, $user_id);
        return !!$result->num_rows;
    }

    function check_rating($user_id) {
        $result = db_query('SELECT `author` FROM `nbhzvn_gameratings` WHERE `game_id` = ? AND `author` = ?', $this->id, $user_id);
        return !!$result->num_rows;
    }

    function toggle_follow($user_id) {
        $result = db_query('SELECT `author` FROM `nbhzvn_gamefollows` WHERE `game_id` = ? AND `author` = ?', $this->id, $user_id);
        if ($result->num_rows > 0) {
            db_query('DELETE FROM `nbhzvn_gamefollows` WHERE `game_id` = ? AND `author` = ?', $this->id, $user_id);
            return ACTION_UNFOLLOW;
        }
        db_query('INSERT INTO `nbhzvn_gamefollows` (`author`, `game_id`) VALUES (?, ?)', $user_id, $this->id);
        return ACTION_FOLLOW;
    }

    function toggle_featured() {
        $new_value = ($this->is_featured ? 0 : 1);
        db_query('UPDATE `nbhzvn_games` SET `is_featured` = ? WHERE `id` = ?', $new_value, $this->id);
        $this->is_featured = ($new_value == 1);
        return $new_value;
    }

    function add_rating($user_id, $rating) {
        $result = db_query('SELECT `rating` FROM `nbhzvn_gameratings` WHERE `game_id` = ? AND `author` = ?', $this->id, $user_id);
        if ($result->num_rows > 0) throw new Exception(ALREADY_RATED);
        db_query('INSERT INTO `nbhzvn_gameratings` (`author`, `timestamp`, `game_id`, `rating`) VALUES (?, ?, ?, ?)', $user_id, time(), $this->id, $rating);
        return $this->ratings();
    }

    function comments() {
        $comments = [];
        $result = db_query('SELECT * FROM `nbhzvn_comments` WHERE `game_id` = ? AND `replied_to` IS NULL ORDER BY `timestamp` DESC', $this->id);
        while ($row = $result->fetch_object()) array_push($comments, new Nbhzvn_Comment($row));
        return $comments;
    }

    function comment_count() {
        $result = db_query('SELECT COUNT(*) AS item_count FROM `nbhzvn_comments` WHERE `game_id` = ? AND `replied_to` IS NULL ORDER BY `timestamp` DESC', $this->id);
        while ($row = $result->fetch_object()) return $row->item_count;
        return null;
    }

    function edit($data) {
        $query = []; $values = [];
        foreach ($data as $key => $value) {
            array_push($query, '`' . $key . '` = ?');
            array_push($values, $value);
        }
        array_push($values, $this->id);
        db_query('UPDATE `nbhzvn_games` SET ' . implode(", ", $query) . ' WHERE `id` = ?', ...$values);
        return SUCCESS;
    }

    function add_comment($user_id, $content, $replied_to) {
        global $conn;
        db_query('INSERT INTO `nbhzvn_comments` (`author`, `timestamp`, `game_id`, `content`, `replied_to`, `edited`) VALUES (?, ?, ?, ?, ?, 0)', $user_id, time(), $this->id, $content, $replied_to);
        if ($conn->insert_id) {
            $comment_id = $conn->insert_id;
            $check = array();
            if (!$replied_to) {
                $author = new Nbhzvn_User($this->uploader);
                if ($author->id && $author->id != $user_id) {
                    $author->send_comment_notification($this, $user_id, $comment_id);
                    $check[$author->id] = true;
                }
            }
            else {
                $replying_comment = new Nbhzvn_Comment($replied_to);
                $author = new Nbhzvn_User($replying_comment->author);
                if ($author->id && $author->id != $user_id) {
                    $author->send_comment_notification($this, $user_id, $replied_to, COMMENT_REPLY, $comment_id);
                    $check[$author->id] = true;
                }
            }
            $mentions = get_mention_users($content);
            foreach ($mentions as $author) {
                if ($author->id && $author->id != $user_id && !$check[$author->id]) {
                    $author->send_comment_notification($this, $user_id, $replied_to ? $replied_to : $comment_id, COMMENT_MENTION, $replied_to ? $comment_id : null);
                    $check[$author->id] = true;
                }
            }
            return new Nbhzvn_Comment($comment_id);
        }
        return FAILED;
    }
}

class Nbhzvn_Comment {
    public $id;
    public $author;
    public $timestamp;
    public $game_id;
    public $content;
    public $replied_to;
    public $edited;

    function __construct($id) {
        if (is_object($id)) $data = $id;
        else {
            $result = db_query('SELECT * FROM `nbhzvn_comments` WHERE id = ?', $id);
            while ($row = $result->fetch_object()) $data = $row;
        }
        $this->id = $data->id;
        $this->author = $data->author;
        $this->timestamp = $data->timestamp;
        $this->game_id = $data->game_id;
        $this->content = $data->content;
        $this->replied_to = $data->replied_to;
        $this->edited = ($data->edited == 1);
    }

    function fetch_replies() {
        $replies = [];
        if (!$this->replied_to) {
            $result = db_query('SELECT * FROM `nbhzvn_comments` WHERE `replied_to` = ? ORDER BY `timestamp` ASC', $this->id);
            while ($row = $result->fetch_object()) array_push($replies, new Nbhzvn_Comment($row));
        }
        return $replies;
    }

    function reply_count() {
        if (!$this->replied_to) {
            $result = db_query('SELECT COUNT(*) AS item_count FROM `nbhzvn_comments` WHERE `replied_to` = ?', $this->id);
            while ($row = $result->fetch_object()) return $row->item_count;
        }
        return 0;
    }

    function delete() {
        db_query('DELETE FROM `nbhzvn_comments` WHERE `id` = ?', $this->id);
        db_query('DELETE FROM `nbhzvn_comments` WHERE `replied_to` = ?', $this->id);
        return SUCCESS;
    }

    function edit($content) {
        db_query('UPDATE `nbhzvn_comments` SET `content` = ?, `edited` = 1 WHERE `id` = ?', $content, $this->id);
        $this->content = $content;
        $this->edited = true;
        return SUCCESS;
    }
}
?>