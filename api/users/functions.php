<?php
function create_user($username, $email, $passphrase) {
    global $conn;
    if (!$username || !$email || !$passphrase) return 0;
    $email = encrypt_string($email);
    $passphrase = encrypt_string(password_hash($passphrase, PASSWORD_DEFAULT));
    db_query('INSERT INTO `nbhzvn_users` (`username`, `email`, `passphrase`, `verification_required`) VALUES (?, ?, ?, false)', $username, $email, $passphrase);
    if ($conn->error) return -1;
    return 1;
}
?>