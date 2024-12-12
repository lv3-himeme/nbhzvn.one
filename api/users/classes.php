<?php
class Nbhzvn_User {
    public $id;
    public $timestamp;
    public $username;
    public $email;
    public $type;
    private $passphrase;
    public $display_name;
    public $description;
    public $discord_id;
    public $verification_required;
    private $verification_code;
    private $login_token;
    public $ban_information;

    function __construct($id) {
        if (gettype($id) == "string") $result = db_query('SELECT * FROM `nbhzvn_users` WHERE username = ?', $id);
        else $result = db_query('SELECT * FROM `nbhzvn_users` WHERE id = ?', $id);
        $this->id = null;
        while ($row = $result->fetch_object()) {
            $this->id = $row->id;
            $this->timestamp = $row->timestamp;
            $this->username = $row->username;
            $this->email = decrypt_string($row->email);
            $this->type = $row->type;
            $this->passphrase = decrypt_string($row->passphrase);
            $this->display_name = $row->display_name;
            $this->description = $row->description;
            $this->discord_id = $row->discord_id;
            $this->verification_required = $row->verification_required;
            $this->verification_code = decrypt_string($row->verification_code);
            $this->login_token = decrypt_string($row->login_token);
            $this->ban_information = json_decode($row->ban_information);
        }
    }

    function verify_passphrase($passphrase) {
        return password_verify($passphrase, $this->passphrase);
    }

    function verify_account($code) {
        return password_verify($code, $this->verification_code);
    }

    function verify_account_hash($hash) {
        return ($hash == $this->verification_code);
    }

    function change_passphrase($passphrase) {
        global $conn;
        $hash = password_hash($passphrase, PASSWORD_BCRYPT);
        db_query('UPDATE `nbhzvn_users` SET `passphrase` = ? WHERE `id` = ?', encrypt_string($hash), $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->passphrase = $hash;
    }

    function change_display_name($value) {
        global $conn;
        db_query('UPDATE `nbhzvn_users` SET `display_name` = ? WHERE `id` = ?', $value, $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->display_name = $value;
    }

    function change_email($value) {
        global $conn;
        db_query('UPDATE `nbhzvn_users` SET `email` = ?, `verification_required` = 1 WHERE `id` = ?', encrypt_string($value), $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->email = $value;
    }

    function change_description($value) {
        global $conn;
        db_query('UPDATE `nbhzvn_users` SET `description` = ? WHERE `id` = ?',  $value, $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->description = $value;
    }

    function update_verification_code($code) {
        global $conn;
        $hash = password_hash($code, PASSWORD_BCRYPT);
        db_query('UPDATE `nbhzvn_users` SET `verification_code` = ? WHERE `id` = ?', encrypt_string($hash), $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->verification_code = $hash;
        return $hash;
    }

    function update_login_token() {
        global $conn;
        $login_token = random_string(64);
        $hash = password_hash($login_token, PASSWORD_BCRYPT);
        $token = encrypt_string($hash);
        db_query('UPDATE `nbhzvn_users` SET `login_token` = ? WHERE `id` = ?', $token, $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->login_token = $hash;
        return $login_token;
    }

    function update_discord_id($id) {
        global $conn;
        db_query('UPDATE `nbhzvn_users` SET `discord_id` = ? WHERE `id` = ?', $id, $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->discord_id = $id;
        return SUCCESS;
    }

    function ban($reason) {
        global $conn;
        $ban_info = new stdClass();
        $ban_info->timestamp = time();
        $ban_info->reason = $reason;
        db_query('UPDATE `nbhzvn_users` SET `ban_information` = ? WHERE `id` = ?', json_encode($ban_info), $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->ban_information = $ban_info;
        return SUCCESS;
    }

    function unban() {
        global $conn;
        db_query('UPDATE `nbhzvn_users` SET `ban_information` = NULL WHERE `id` = ?', $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->ban_information = null;
        return SUCCESS;
    }

    function change_type($type) {
        global $conn;
        if ($type < 1 || $type > 3) throw new Exception(DISALLOWED_TYPE);
        db_query('UPDATE `nbhzvn_users` SET `type` = ? WHERE `id` = ?', $type, $this->id);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        $this->type = $type;
    }

    function check_login_token($token) {
        return password_verify($token, $this->login_token);
    }

    function apply_cookie() {
        $login_token = $this->update_login_token();
        setcookie("nbhzvn_username", $this->username, time() + 2592000);
        setcookie("nbhzvn_login_token", $login_token, time() + 2592000);
    }

    function first_verification() {
        return !$this->verification_code;
    }

    function send_verification_email() {
        global $http;
        global $host;
        $verification_code = sprintf("%08d", rand(0, 99999999));
        $hash = $this->update_verification_code($verification_code);
        if (!send_mail(
            $this->email,
            'Xác nhận tài khoản tại Nobihaza Vietnam Community Collection',
            '
                <p>Xin chào <b>' . $this->username . '</b>,</p>
                <p>Email của bạn đã được sử dụng để xác minh cho tài khoản trên ở trang web <b>Nobihaza Vietnam Community Collection</b>.</p>
                <p>Nếu bạn không thực hiện hành động này thì hãy vui lòng bỏ qua email này. Còn nếu bạn là người gửi yêu cầu xác minh tài khoản tới email này thì hãy nhập mã xác minh sau vào ô trong trang web đó:</p>
                <h2>' . $verification_code . '</h2>
                <p>Hoặc bạn cũng có thể <a href="' . $http . '://' . $host . '/verify?username=' . $this->username . '&code=' . urlencode($hash) . '">nhấn vào đây</a> để xác nhận, hoặc sao chép liên kết sau vào thanh địa chỉ:</p>
                <p>' . $http . '://' . $host . '/verify?username=' . $this->username . '&code=' . urlencode($hash) . '</p>
                <p>Cảm ơn bạn đã quan tâm tới Nobihaza Vietnam Community Collection của bọn mình, chúc bạn chơi game vui vẻ!</p>
            '
        )) throw new Exception(SEND_MAIL_FAILED);
        return SUCCESS;
    }

    function send_forgot_password_email() {
        global $http;
        global $host;
        $verification_code = sprintf("%08d", rand(0, 99999999));
        $hash = $this->update_verification_code($verification_code);
        if (!send_mail(
            $this->email,
            'Yêu cầu đặt lại mật khẩu tại Nobihaza Vietnam Community Collection',
            '
                <p>Xin chào <b>' . $this->username . '</b>,</p>
                <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản này ở trang web <b>Nobihaza Vietnam Community Collection</b>.</p>
                <p>Nếu bạn không thực hiện hành động này thì hãy vui lòng bỏ qua email này. Còn nếu bạn là người gửi yêu cầu đặt lại mật khẩu thì hãy <a href="' . $http . '://' . $host . '/forgot_password?username=' . $this->username . '&code=' . urlencode($hash) . '">nhấn vào đây</a> để xác nhận, hoặc sao chép liên kết sau vào thanh địa chỉ:</p>
                <p>' . $http . '://' . $host . '/forgot_password?username=' . $this->username . '&code=' . urlencode($hash) . '</p>
                <p>Cảm ơn bạn đã quan tâm tới Nobihaza Vietnam Community Collection của bọn mình, chúc bạn chơi game vui vẻ!</p>
            '
        )) throw new Exception(SEND_MAIL_FAILED);
        return SUCCESS;
    }

    function check_timeout($prop) {
        global $conn;
        $result = db_query('SELECT `timestamp` FROM `nbhzvn_timeouts` WHERE `id` = ? AND `property` = ?', $this->id, $prop);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        while ($row = $result->fetch_object()) {
            return (time() < $row->timestamp);
        }
        return false;
    }

    function update_timeout($prop, $time) {
        global $conn;
        $result = db_query('SELECT `timestamp` FROM `nbhzvn_timeouts` WHERE `id` = ? AND `property` = ?', $this->id, $prop);
        if ($conn->error) throw new Exception(DB_CONNECTION_ERROR);
        if ($result->num_rows > 0) db_query('UPDATE `nbhzvn_timeouts` SET `timestamp` = ? WHERE `id` = ? AND `property` = ?', $time, $this->id, $prop);
        else db_query('INSERT INTO `nbhzvn_timeouts` (`user_id`, `property`, `timestamp`) VALUES (?, ?, ?)', $this->id, $prop, $time);
        return SUCCESS;
    }

    function followed_games() {
        $games = [];
        $result = db_query('SELECT `game_id` FROM `nbhzvn_gamefollows` WHERE `author` = ?', $this->id);
        while ($row = $result->fetch_object()) {
            array_push($games, new Nbhzvn_Game($row->game_id));
        }
        return $games;
    }

    function comments() {
        $comments = [];
        $result = db_query('SELECT `id` FROM `nbhzvn_comments` WHERE `author` = ? ORDER BY TIMESTAMP DESC', $this->id);
        while ($row = $result->fetch_object()) {
            array_push($comments, new Nbhzvn_Comment($row->id));
        }
        return $comments;
    }

    function uploaded_games() {
        $games = [];
        $result = db_query('SELECT `id` FROM `nbhzvn_games` WHERE `uploader` = ?', $this->id);
        while ($row = $result->fetch_object()) {
            array_push($games, new Nbhzvn_Game($row->id));
        }
        return $games;
    }
}
?>