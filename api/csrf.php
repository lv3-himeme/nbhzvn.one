<?php
function refresh_csrf() {
    $_SESSION["nbhz_csrf_token"] = random_string(64);
}
function get_csrf() {
    return $_SESSION["nbhz_csrf_token"];
}
function clear_csrf() {
    unset($_SESSION["nbhz_csrf_token"]);
}
function check_csrf($token) {
    if ($token || !$_SESSION["nbhz_csrf_token"]) return false;
    return ($_SESSION["nbhz_csrf_token"] == $token);
}
?>