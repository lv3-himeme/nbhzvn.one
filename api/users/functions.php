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

function create_login_token($id) {
    global $conn;
    // Multiple hashing and encrypting layers for true randomness!
    $token = encrypt_string(password_hash(random_string(32), PASSWORD_DEFAULT));
    db_query('UPDATE `nbhzvn_users` SET `login_token` = ? WHERE `id` = ?', $token, $id);
    if ($conn->error) return null;
    return $token;
}

function get_user_by_id($id) {
    global $conn;
    $result = db_query('SELECT * FROM `nbhzvn_users` WHERE id = ?', $id);
    if ($conn->error) return null;
    while ($row = $result->fetch_object()) {
        unset($row->passphrase);
        unset($row->verification_code);
        $row->email = decrypt_string($row->email);
        $row->discord_id = decrypt_string($row->discord_id);
        return $row;
    }
}

function register($username, $email, $passphrase) {
    global $conn;
    if (!$username || !$email || !$passphrase) return 0;
    $username = strtolower($username);
    if (user_exists($username)) return -2;
    $email = strtolower($email);
    if (email_exists($email)) return -3;
    $email = encrypt_string($email);
    $passphrase = encrypt_string(password_hash($passphrase, PASSWORD_DEFAULT));
    db_query('INSERT INTO `nbhzvn_users` (`username`, `email`, `passphrase`, `verification_required`) VALUES (?, ?, ?, false)', $username, $email, $passphrase);
    if ($conn->error) return -1;
    return 1;
    /*
    1: Success
    0: Missing information
    -1: Database error
    -2: Username already exists
    -3: Email already exists
    */
}

function login($username, $passphrase) {
    global $conn;
    if (!$username || !$passphrase) return 0;
    $result = db_query('SELECT `id`, `passphrase` FROM `nbhzvn_users` WHERE `username` = ?', $username);
    if ($conn->error) return -1;
    $passphrase_hash = null; $user_id = null;
    while ($row = $result->fetch_object()) {
        $passphrase_hash = decrypt_string($row->passphrase);
        $user_id = $row->id;
    }
    if (!$passphrase_hash || !$user_id) return -2;
    if (!password_verify($passphrase, $passphrase_hash)) return -2;
    return $user_id;
    /*
    >0: User ID when successfully login
    0: Missing information
    -1: Database error
    -2: Incorrect username/password
    */
}
?>