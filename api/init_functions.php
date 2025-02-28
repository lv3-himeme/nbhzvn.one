<?php
$init_start = time();
require __DIR__.'/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "../../");
$dotenv->load();
function cookie($name) {
    global $_COOKIE;
    return $_COOKIE[$name];
}

function get($name) {
    global $_GET;
    return $_GET[$name];
}

function post($name) {
    global $_POST;
    return $_POST[$name];
}

function encrypt_string($string) {
    global $encryption_password;
    $ciphering = "AES-256-CTR";
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;
    $encryption_iv = random_bytes($iv_length);
    $encryption_key = openssl_digest($encryption_password, "MD5", true);
    $encrypted_string = openssl_encrypt($string, $ciphering, $encryption_key, $options, $encryption_iv);
    return base64_encode(base64_encode($encryption_iv) . "/.lh/." . $encrypted_string);
}

function decrypt_string($hash) {
    global $encryption_password;
    $hash_tmp = base64_decode($hash);
    $hash_part = explode("/.lh/.", $hash_tmp);
    $ciphering = "AES-256-CTR";
    $options = 0;
    $encryption_iv = base64_decode($hash_part[0]);
    $encryption_key = openssl_digest($encryption_password, "MD5", true);
    $encrypted_string = $hash_part[1];
    return openssl_decrypt($encrypted_string, $ciphering, $encryption_key, $options, $encryption_iv);
}

function random_string(
    int $length = 64,
    string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string {
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

function special_chars($str) {
    return preg_match('/[^a-zA-Z0-9_]/', $str) > 0;
}

function timestamp_to_string($timestamp, $short = false) {
    $utcPlus7 = new DateTimeZone('Asia/Ho_Chi_Minh');
    $dateTime = new DateTime();
    $dateTime->setTimestamp($timestamp);
    $dateTime->setTimezone($utcPlus7);
    return $dateTime->format($short ? "j \\t\\há\\n\\g n, Y" : "d/m/Y H:i:s");
}

function redirect_to_home() {
    header("Location: /");
    die();
}

function bytes_to_string($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function comment_time($timestamp) {
    $diff = time() - $timestamp;
    $time_diff = [315360000, 2592000, 604800, 86400, 3600, 60];
    $time_diff_text = ["năm", "tháng", "tuần", "ngày", "giờ", "phút"];
    for ($i = 0; $i < count($time_diff); $i++) {
        if ($diff > $time_diff[$i]) return strval(floor($diff / $time_diff[$i])) . " " . $time_diff_text[$i] . " trước";
    }
    return $diff . " giây trước";
}

function remove_special_chars($str) {
	return preg_replace('/[^a-zA-Z0-9_ -]/s', '', $str);
}
?>