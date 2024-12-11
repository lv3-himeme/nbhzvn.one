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
?>