<?php
function add_game(stdClass $data, bool $pre_approved = false) {
    global $conn;
    db_query('INSERT INTO `nbhzvn_games`
        (`timestamp`, `name`, `links`, `image`, `screenshots`, `description`, `engine`, `tags`, `release_year`, `author`, `language`, `translator`, `uploader`, `status`, `views`, `views_today`, `updated_date`, `downloads`, `supported_os`, `is_featured`, `approved`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ', time(), $data->name, $data->links, $data->image, $data->screenshots, $data->description, $data->engine, $data->tags, $data->release_year, $data->author, $data->language, $data->translator, $data->uploader, $data->status, 0, 0, date('Y-m-d'), 0, $data->supported_os, 0, $pre_approved ? 1 : 0);
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return SUCCESS;
}

function all_games($limit = 0) {
    global $conn;
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `approved` = 1' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function random_games($current_id = 0, $limit = 5) {
    global $conn;
    $games = [];
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `id` != ? AND `approved` = 1 ORDER BY rand() LIMIT ?', $current_id, $limit);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function featured_games() {
    global $conn;
    $games = [];
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `is_featured` = 1 AND `approved` = 1');
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function unapproved_games() {
    global $conn;
    $games = [];
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `approved` = 0');
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function trending_games($limit = 0) {
    global $conn;
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `approved` = 1 ORDER BY `views_today` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function popular_games($limit = 0) {
    global $conn;
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `approved` = 1 ORDER BY `downloads` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function most_followed_games($limit = 0) {
    global $conn;
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
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function recent_games($limit = 0) {
    global $conn;
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `approved` = 1 ORDER BY `timestamp` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function mobile_games($limit = 0) {
    global $conn;
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `supported_os` LIKE "%android%" OR `supported_os` LIKE "%ios%" AND `approved` = 1 ORDER BY `downloads` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
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

function echo_comment($comment, $is_reply, $user = new Nbhzvn_User(0), $hide_options = false, $highlighted = 0) {
    $comment_author = new Nbhzvn_User($comment->author);
    $replies = $comment->reply_count();
    $pre_reply_html = "";
    if ($highlighted && $replies > 0) {
        $replies_list = $comment->fetch_replies();
        foreach ($replies_list as $reply) $pre_reply_html .= echo_comment($reply, true, $user, $hide_options, $reply->id == $highlighted);
        $replies = 0;
    }
    $options = [];
    if (!$hide_options) {
        if ($user->id == $comment->author) array_push($options, '<a href="javascript:void(0)" onclick="editComment(' . $comment->id . ')">Chỉnh sửa</a>');
        if ($user->id == $comment->author || $user->type == 3) array_push($options, '<a href="javascript:void(0)" onclick="deleteComment(' . $comment->id . ')">Xoá</a>');
        if ($user->id) array_push($options, '<a href="javascript:void(0)" onclick="replyComment(' . ($comment->replied_to ? $comment->replied_to : $comment->id) . ', ' . ($comment->replied_to ? ('\'' . $comment_author->username . '\'') : "null") . ')">Trả lời</a>');
    }
    return '<div id="comment-' . $comment->id . '" class="comment_container"><div class="anime__review__item"><div class="anime__review__item__text' . ($is_reply ? " reply" : "") . '"><h6><a href="/profile/' . $comment->author . '">' . ($comment_author->display_name ? $comment_author->display_name : $comment_author->username) . '</a> • <a href="/games/' . $comment->game_id . ($comment->replied_to ? ('?highlighted_comment=' . $comment->replied_to . '&reply_comment=' . $comment->id . '#comment-' . $comment->id) : ('?highlighted_comment=' . $comment->id . '#comment-' . $comment->id)) . '"><span>' . comment_time($comment->timestamp) . ($comment->edited ? " (đã chỉnh sửa)" : "") . (($highlighted == $comment->id) ? '<span class="highlighted_comment">Bình luận nổi bật</span>' : "") . '</span></a></h6><p id="comment-' . $comment->id . '-content">' . process_mentions($comment->content) . '</p>' . (count($options) ? ('<p id="comment-' . $comment->id . '-options" class="comment_options">' . implode(" • ", $options) . '</p>') : "") . '</div><div id="comment-' . $comment->id . '-replies" class="comment_replies">' . $pre_reply_html . '</div>' . (($replies > 0 && !$hide_options) ? '<div class="view_replies_btn" id="comment-' . $comment->id . '-repliesbtn"><a href="javascript:void(0)" onclick="viewReplies(' . $comment->id . ')">Xem ' . $replies . ' câu trả lời...</a></div>' : "") . '</div></div>';
}

// Update views_today
db_query('UPDATE `nbhzvn_games` SET `views_today` = 0, `updated_date` = ? WHERE `updated_date` != ?', date('Y-m-d'), date('Y-m-d'));
?>