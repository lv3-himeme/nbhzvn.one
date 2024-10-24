<?php
include __DIR__ . "/connection.php";
require __DIR__ . "/db_setup.php";

function db_query($query, ...$args) {
    global $conn;
    $tmp = $conn->prepare($query);
    $type = "";
    for ($i = 0; $i < func_num_args() - 1; $i++) $type .= "s";
    if (func_num_args() >= 2) $tmp->bind_param($type, ...$args);
    $tmp->execute();
    return $tmp->get_result();
}