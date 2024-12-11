<?php
$client_id = $_ENV["DISCORD_CLIENT_ID"];
$client_secret = $_ENV["DISCORD_CLIENT_SECRET"];

function get_user_from_discord_id($id) {
    $result = db_query('SELECT `id` FROM `nbhzvn_users` WHERE `discord_id` = ?', $id);
    while ($row = $result->fetch_object()) {
        return new Nbhzvn_User($row->id);
    }
    return null;
}

function request_token($code) {
    global $client_id, $client_secret, $http, $host;
    return json_decode(http_post_request("https://discord.com/api/oauth2/token", http_build_query(array(
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "grant_type" => "authorization_code",
        "code" => $code,
        "redirect_uri" => $http . "://" . $host . "/discord"
    )), [
        "Content-Type: application/x-www-form-urlencoded"
    ]));
}

function get_discord_info($token_type, $access_token) {
    return json_decode(http_get_request("https://discord.com/api/users/@me", [
        "Authorization: " . $token_type . " " . $access_token
    ]));
}
?>