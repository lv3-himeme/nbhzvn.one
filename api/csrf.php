<?php
session_start();
function refresh_csrf() {
    $_SESSION["nbhz_csrf_token_" . remove_special_chars($_SERVER["REQUEST_URI"])] = random_string(64);
}
function get_csrf() {
    return $_SESSION["nbhz_csrf_token_" . remove_special_chars($_SERVER["REQUEST_URI"])];
}
function clear_csrf() {
    unset($_SESSION["nbhz_csrf_token_" . remove_special_chars($_SERVER["REQUEST_URI"])]);
}
function check_csrf($token) {
    if (!$token || !$_SESSION["nbhz_csrf_token_" . remove_special_chars($_SERVER["REQUEST_URI"])]) return false;
    return ($_SESSION["nbhz_csrf_token_" . remove_special_chars($_SERVER["REQUEST_URI"])] == $token);
}
?>