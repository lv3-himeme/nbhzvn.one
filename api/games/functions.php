<?php
function add_game(stdClass $data, bool $pre_approved = false) {
    db_query('INSERT INTO `nbhzvn_games`
        (`timestamp`, `name`, `links`, `image`, `screenshots`, `description`, `engine`, `tags`, `release_year`, `author`, `language`, `translator`, `uploader`, `status`, `views`, `views_today`, `updated_date`, `downloads`, `supported_os`, `is_featured`, `approved`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ', time(), $data->name, $data->links, $data->image, $data->screenshots, $data->description, $data->engine, $data->tags, $data->release_year, $data->author, $data->language, $data->translator, $data->uploader, $data->status, 0, 0, date('Y-m-d'), 0, $data->supported_os, 0, $pre_approved ? 1 : 0);
    return SUCCESS;
}

function all_games($limit = 0) {
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `approved` = 1' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function random_games($current_id = 0, $limit = 5) {
    $games = [];
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `id` != ? AND `approved` = 1 ORDER BY rand() LIMIT ?', $current_id, $limit);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function featured_games() {
    $games = [];
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `is_featured` = 1 AND `approved` = 1');
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function unapproved_games() {
    $games = [];
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `approved` = 0');
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function trending_games($limit = 0) {
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `approved` = 1 ORDER BY `views_today` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function popular_games($limit = 0) {
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `approved` = 1 ORDER BY `downloads` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function most_followed_games($limit = 0) {
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT g.`id`, COUNT(f.`game_id`) AS follow_count FROM `nbhzvn_gamefollows` f LEFT JOIN `nbhzvn_games` g ON f.`game_id` = g.`id` WHERE `approved` = 1 GROUP BY g.`id` ORDER BY follow_count DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) {
        $game = new stdClass();
        $game->data = new Nbhzvn_Game($row->id);
        $game->follow_count = $row->follow_count;
        array_push($games, $game);
    }
    return $games;
}

function recent_games($limit = 0) {
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `approved` = 1 ORDER BY `timestamp` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function mobile_games($limit = 0) {
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `supported_os` LIKE "%android%" OR `supported_os` LIKE "%ios%" AND `approved` = 1 ORDER BY `downloads` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function search_games($queries) {
    $games = []; $queries_arr = []; $arguments = [];
    foreach ($queries as $key => $value) {
        $operand = "=";
        switch ($key) {
            case "name":
            case "tags":
            case "author":
            case "translator":
            case "supported_os": {
                $operand = "LIKE";
                $value = "%" . preg_replace('/[^a-zA-Z0-9_ -]/s', "%", $value) . "%";
                break;
            }
            case "downloads":
            case "views": {
                $operand = ">=";
                break;
            }
        }
        array_push($queries_arr, '`' . $key . '` ' . $operand . ' ?');
        array_push($arguments, $value);
    }
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE ' . implode(", ", $queries_arr), ...$arguments);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function process_mentions($content) {
    $content = htmlentities($content); $new_content = [];
    foreach (explode(" ", $content) as $part) {
        if (str_starts_with($part, "@")) {
            $username = substr($part, 1);
            $mention_user = new Nbhzvn_User($username);
            if ($mention_user->id) $part = '<a href="/profile/' . $mention_user->id . '">@' . ($mention_user->display_name ? $mention_user->display_name : $mention_user->username) . '</a>';
        }
        array_push($new_content, $part);
    }
    return implode(" ", $new_content);
}

function get_mention_users($content) {
    $content = htmlentities($content); $users = [];
    foreach (explode(" ", $content) as $part) {
        if (str_starts_with($part, "@")) {
            $username = substr($part, 1);
            $mention_user = new Nbhzvn_User($username);
            if ($mention_user->id) array_push($users, $mention_user);
        }
    }
    return $users;
}

function echo_homepage_game($tmp_game) {
    global $status_vocab;
    global $engine_vocab;
    return '
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="product__item">
                <a href="/games/' . $tmp_game->id . '"><div class="product__item__pic set-bg" data-setbg="/uploads/' . $tmp_game->image . '">
                    <div class="ep">' . $status_vocab[$tmp_game->status] . '</div>
                    <div class="comment"><i class="fa fa-comments"></i> ' . number_format($tmp_game->comments, 0, ",", ".") . '</div>
                    <div class="view"><i class="fa fa-eye"></i> ' . number_format($tmp_game->views, 0, ",", ".") . '</div>
                </div></a>
                <div class="product__item__text">
                    <ul>
                        <li>' . $engine_vocab[$tmp_game->engine] . '</li>
                    </ul>
                    <h5><a href="/games/' . $tmp_game->id . '">' . htmlentities($tmp_game->name) . '</a></h5>
                </div>
            </div>
        </div>
    ';
}

function echo_tiled_game($tmp_game, $col = false) {
    global $status_vocab;
    return ($col ? '<div class="col-lg-4 col-md-6 col-sm-6">' : "") . '
        <div class="product__sidebar__view__item set-bg" data-setbg="/uploads/' . $tmp_game->image . '">
            <div class="ep">' . $status_vocab[$tmp_game->status] . '</div>
            <div class="view"><i class="fa fa-eye"></i> ' . number_format($tmp_game->views, 0, ",", ".") . '</div>
            <h5><a href="/games/' . $tmp_game->id . '">' . htmlentities($tmp_game->name) . '</a></h5>
        </div>
    ' . ($col ? '</div>' : "");
}

function echo_search_game($tmp_game, $col = false) {
    global $engine_vocab;
    return ($col ? '<div class="col-lg-4 col-md-6 col-sm-6">' : "") . '
    <div class="product__sidebar__comment__item">
        <a href="/games/' . $tmp_game->id . '"><div class="product__sidebar__comment__item__pic">
            <img src="/uploads/' . $tmp_game->image . '" alt="">
        </div></a>
        <div class="product__sidebar__comment__item__text">
            <ul>
                <li>' . $engine_vocab[$tmp_game->engine] . '</li>
            </ul>
            <h5><a href="/games/' . $tmp_game->id . '">' . htmlentities($tmp_game->name) . '</a></h5>
            <span><i class="fa fa-eye"></i> ' . number_format($tmp_game->views, 0, ",", ".") . ' lượt xem</span>
        </div>
    </div>
    ' . ($col ? '</div>' : "");
}

// Update views_today
db_query('UPDATE `nbhzvn_games` SET `views_today` = 0, `updated_date` = ? WHERE `updated_date` != ?', date('Y-m-d'), date('Y-m-d'));
?>