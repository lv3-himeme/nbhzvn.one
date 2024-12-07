<?php
function user_exists($username) {
    return (db_query('SELECT `username` FROM `nbhzvn_users` WHERE `username` = ?', $username)->num_rows > 0);
}

function email_exists($email) {
    $result = db_query('SELECT `email` FROM `nbhzvn_users` WHERE 1');
    while ($row = $result->fetch_object()) {
        if (strtolower(decrypt_string($row->email)) == strtolower($email)) return true;
    }
    return false;
}

function register($username, $email, $passphrase, $verification = 1, $discord_id = null) {
    global $conn;
    if (!$username || !$email || !$passphrase) throw new Exception(MISSING_INFORMATION);
    $username = strtolower($username);
    if (user_exists($username)) throw new Exception(USERNAME_ALREADY_EXISTS);
    $email = strtolower($email);
    if (email_exists($email)) throw new Exception(EMAIL_ALREADY_EXISTS);
    $email = encrypt_string($email);
    $passphrase = encrypt_string(password_hash($passphrase, PASSWORD_DEFAULT));
    db_query('INSERT INTO `nbhzvn_users` (`username`, `email`, `passphrase`, `type`, `verification_required`, `discord_id`) VALUES (?, ?, ?, 1, ?, ?)', $username, $email, $passphrase, $verification, $discord_id);
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    return SUCCESS;
}

function login($username, $passphrase) {
    global $conn;
    if (!$username || !$passphrase) throw new Exception(MISSING_INFORMATION);
    $result = db_query('SELECT `id`, `passphrase` FROM `nbhzvn_users` WHERE `username` = ?', $username);
    if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
    $passphrase_hash = null; $user_id = null;
    while ($row = $result->fetch_object()) {
        $passphrase_hash = decrypt_string($row->passphrase);
        $user_id = $row->id;
    }
    if (!$passphrase_hash || !$user_id) throw new Exception(INCORRECT_CREDENTIALS);
    if (!password_verify($passphrase, $passphrase_hash)) throw new Exception(INCORRECT_CREDENTIALS);
    return $user_id;
}
?>