<?php
include __DIR__ . "/connection.php";
require __DIR__ . "/db_setup.php";
require __DIR__ . "/csrf.php";
require __DIR__ . "/mail.php";
require __DIR__ . "/classes.php";

function check_email_validity($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) &&
           (str_ends_with($email, "@gmail.com") || str_ends_with($email, "@yahoo.com") || str_ends_with($email, "@outlook.com"));
}

function db_query($query, ...$args) {
    global $conn;
    $tmp = $conn->prepare($query);
    $type = "";
    for ($i = 0; $i < func_num_args() - 1; $i++) $type .= "s";
    if (func_num_args() >= 2) $tmp->bind_param($type, ...$args);
    $tmp->execute();
    return $tmp->get_result();
}

function api_header() {
    global $api_version;
    header("Content-Type: application/json");
    header("Api-Version: " . $api_version);
}

function api_response($data, $message = "", $status_code = 200) {
    api_header();
    $res = new stdClass();
    $res->success = ($status_code == 200);
    $res->status_code = $status_code;
    $res->message = $message;
    $res->data = $data;
    http_response_code($status_code);
    die(json_encode($res));
}