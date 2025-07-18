<?php
function add_game(stdClass $data, bool $pre_approved = false) {
    db_query('INSERT INTO `nbhzvn_games`
        (`timestamp`, `name`, `links`, `beta_links`, `beta_users`, `image`, `screenshots`, `description`, `engine`, `tags`, `release_year`, `author`, `language`, `translator`, `uploader`, `status`, `views`, `views_today`, `downloads_today`, `updated_date`, `file_updated_time`, `downloads`, `supported_os`, `is_featured`, `approved`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ', time(), $data->name, $data->links, $data->beta_links, $data->beta_users, $data->image, $data->screenshots, $data->description, $data->engine, $data->tags, $data->release_year, $data->author, $data->language, $data->translator, $data->uploader, $data->status, 0, 0, 0, date('Y-m-d'), time(), 0, $data->supported_os, 0, $pre_approved ? 1 : 0);
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

function unapproved_games($user) {
    $games = [];
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `approved` = 0' . (($user->type == 2) ? (" AND `uploader` = " . $user->id) : ""));
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function trending_games($limit = 6) {
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `approved` = 1 ORDER BY `downloads_today` DESC, `views_today` DESC' . $limit_query, ...$limit_args);
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

function recently_updated_games($limit = 0) {
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE `approved` = 1 AND `file_updated_time` > ' . strval(time() - 604800) . ' AND `timestamp` < ' . strval(time() - 604800) . ' ORDER BY `file_updated_time` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function mobile_games($limit = 0) {
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE (`supported_os` LIKE "%android%" OR `supported_os` LIKE "%ios%") AND `approved` = 1 ORDER BY `downloads` DESC' . $limit_query, ...$limit_args);
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
    $result = db_query('SELECT * FROM `nbhzvn_games` WHERE ' . implode(" AND ", $queries_arr), ...$arguments);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row));
    return $games;
}

function process_mentions($content) {
    $content = nl2br(htmlentities($content)); $new_content = [];
    foreach (explode(" ", str_replace("\n", " ", $content)) as $part) {
        if (str_starts_with($part, "@")) {
            $username = substr($part, 1);
            $mention_user = new Nbhzvn_User($username);
            if ($mention_user->id) $part = '<a href="/profile/' . $mention_user->id . '">@' . $mention_user->display_name() . '</a>';
        }
        array_push($new_content, $part);
    }
    return implode(" ", $new_content);
}

function get_mention_users($content) {
    $content = str_replace("\n", " ", htmlentities($content)); $users = [];
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
                    <div class="comment"><i class="fa fa-comments"></i> ' . number_format(count($tmp_game->comments()), 0, ",", ".") . '</div>
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

function beta_users_notifications(Nbhzvn_Game $game = new Nbhzvn_Game(0), $old_testers = [], $new_testers = []) {
    global $user;
    $removed_testers = array_filter($old_testers, function($a) use ($new_testers) {
        return !in_array($a, $new_testers);
    });
    $added_testers = array_filter($new_testers, function($a) use ($old_testers) {
        return !in_array($a, $old_testers);
    });
    foreach ($removed_testers as $user_id) {
        $tmp_tester = new Nbhzvn_User($user_id);
        if ($tmp_tester->id) $tmp_tester->send_notification("/games/" . $game->id, "**" . $user->display_name() . "** đã xóa bạn khỏi danh sách thành viên thử nghiệm bản Beta của game **" . $game->name . "**.");
    }
    foreach ($added_testers as $user_id) {
        $tmp_tester = new Nbhzvn_User($user_id);
        if ($tmp_tester->id) $tmp_tester->send_notification("/games/" . $game->id . "#betaDownloadSection", "**" . $user->display_name() . "** đã mời bạn tham gia thử nghiệm bản Beta của game **" . $game->name . "**.");
    }
}

function migrate_1() {
    $folder = __DIR__ . "/../../uploads";
    if (!file_exists($folder)) {
        if (!mkdir($folder, 0775, true)) return -1;
    }
    if (file_exists($folder . "/" . ".migrate_1")) return 0;
    $games = all_games();
    $count = 0;
    foreach ($games as $game) {
        $count2 = 0;
        for ($i = 0; $i < count($game->links); $i++) {
            $link = $game->links[$i];
            $old_name = $folder . "/" . $link->path;
            $new_folder = $folder . "/" . pathinfo($link->path, PATHINFO_FILENAME);
            $new_name = $new_folder . "/" . $link->name;
            if (file_exists($old_name) && !file_exists($new_name)) {
                if (!mkdir($new_folder, 0755, true)) return $new_folder;
                rename($old_name, $new_name);
                $count++; $count2++;
                $game->links[$i]->path = pathinfo($link->path, PATHINFO_FILENAME);
            }
        }
        for ($i = 0; $i < count($game->beta_links); $i++) {
            $link = $game->beta_links[$i];
            $old_name = $folder . "/" . $link->path;
            $new_folder = $folder . "/" . pathinfo($link->path, PATHINFO_FILENAME);
            $new_name = $new_folder . "/" . $link->name;
            if (file_exists($old_name) && !file_exists($new_name)) {
                if (!mkdir($new_folder, 0755, true)) return $new_folder;
                rename($old_name, $new_name);
                $count++; $count2++;
                $game->beta_links[$i]->path = pathinfo($link->path, PATHINFO_FILENAME);
            }
        }
        if ($count2 > 0) $game->update_links($game->links, $game->beta_links);
    }
    touch($folder . "/" . ".migrate_1");
    return $count;
}

migrate_1();
?>