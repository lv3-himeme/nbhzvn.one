<?php
class Nbhzvn_User {
    public $id;
    public $username;
    public $email;
    public $type;
    protected $passphrase;
    public $display_name;
    public $description;
    public $discord_id;
    protected $verification_code;
    protected $login_token;

    function __construct($id) {
        if (gettype($id) == "string") $result = db_query('SELECT * FROM `nbhzvn_users` WHERE username = ?', $id);
        else $result = db_query('SELECT * FROM `nbhzvn_users` WHERE id = ?', $id);
        $this->id = null;
        while ($row = $result->fetch_object()) {
            $this->id = $row->id;
            $this->username = $row->username;
            $this->email = decrypt_string($row->email);
            $this->passphrase = decrypt_string($row->passphrase);
            $this->display_name = $row->display_name;
            $this->description = $row->description;
            $this->discord_id = decrypt_string($row->discord_id);
            $this->verification_code = decrypt_string($row->verification_code);
            $this->login_token = decrypt_string($row->login_token);
        }
    }

    function verify_passphrase($passphrase) {
        return password_verify($passphrase, $this->passphrase);
    }

    function verify_account($code) {
        return password_verify($code, $this->verification_code, PASSWORD_BCRYPT);
    }

    function verify_account_hash($hash) {
        return ($hash == $this->verification_code);
    }

    function update_login_token() {
        global $conn;
        $hash = password_hash(random_string(64), PASSWORD_DEFAULT);
        $token = encrypt_string($hash);
        db_query('UPDATE `nbhzvn_users` SET `login_token` = ? WHERE `id` = ?', $token, $this->id);
        if (!$conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->login_token = $hash;
    }

    function check_login_token($token) {
        return password_verify($token, $this->login_token);
    }
}
?>