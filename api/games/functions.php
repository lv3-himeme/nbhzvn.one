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

function random_games($current_id = 0, $limit = 5) {
    global $conn;
    $games = [];
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `id` != ? ORDER BY rand() LIMIT ?', $current_id, $limit);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function featured_games() {
    global $conn;
    $games = [];
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `is_featured` = 1');
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function trending_games($limit) {
    global $conn;
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT `id` FROM `nbhzvn_games` ORDER BY `views_today` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function popular_games($limit) {
    global $conn;
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT `id` FROM `nbhzvn_games` ORDER BY `downloads` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function most_followed_games($limit) {
    global $conn;
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT g.`id`, COUNT(f.`game_id`) AS follow_count FROM `nbhzvn_gamefollows` f LEFT JOIN `nbhzvn_games` g ON f.`game_id` = g.`id` GROUP BY g.`id` ORDER BY follow_count DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) {
        $game = new stdClass();
        $game->data = new Nbhzvn_Game($row->id);
        $game->follow_count = $row->follow_count;
        array_push($games, $game);
    }
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function recent_games($limit) {
    global $conn;
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT `id` FROM `nbhzvn_games` ORDER BY `timestamp` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function mobile_games($limit) {
    global $conn;
    $games = []; $limit_query = ""; $limit_args = [];
    if ($limit) {
        $limit_query = " LIMIT ?";
        $limit_args = [$limit];
    }
    $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `supported_os` LIKE "%android%" OR `supported_os` LIKE "%ios%" ORDER BY `downloads` DESC' . $limit_query, ...$limit_args);
    while ($row = $result->fetch_object()) array_push($games, new Nbhzvn_Game($row->id));
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return $games;
}

function echo_comment($comment, $is_reply) {
    $comment_author = new Nbhzvn_User($comment->author);
    $replies = $comment->reply_count();
    return '<div id="comment-' . $comment->id . '"><div class="anime__review__item"><div class="anime__review__item__text' . ($is_reply ? " reply" : "") . '"><h6><a href="/profile/' . $comment->author . '">' . ($comment_author->display_name ? $comment_author->display_name : $comment_author->username) . '</a> • <a href="#comment-' . $comment->id . '"><span>' . comment_time($comment->timestamp) . ($comment->edited ? " (đã chỉnh sửa)" : "") . '</span></a></h6><p>' . htmlentities($comment->content) . '</p></div><div id="comment-' . $comment->id . '-replies" class="comment_replies"></div>' . (($replies > 0) ? '<div class="view_replies_btn" id="comment-' . $comment->id . '-repliesbtn"><a href="javascript:void(0)" onclick="viewReplies(' . $comment->id . ')">Xem ' . $replies . ' câu trả lời...</a>' : "") . '</div></div>';
}
?>