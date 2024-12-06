<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Soundasleep\Html2Text;

$http = (empty($_SERVER["HTTPS"]) ? "http" : "https");
$host = $_SERVER["HTTP_HOST"];
$email_header = '
    <p><img src="' . $http . '//' . $host . '/img/logo.png" style="width: auto; height: 200px" /></p>
    <br>
';
$email_footer = '
    <br>
    <p>Nobihaza Vietnam Community Collection</p>
    <p><a href="' . $http . '//' . $host . '">' . $host . '</a></p>
    <p style="color: #333"><b><i><small>Đây là email được gửi tự động. Vui lòng không trả lời email này.</small></i></b></p>
';

function send_mail($email, $subject, $body) {
    try {
        global $email_header;
        global $email_footer;
        $body = $email_header . $body . $email_footer;
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV["EMAIL_HOST"];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV["EMAIL_USERNAME"];
        $mail->Password = $_ENV["EMAIL_PASSWORD"];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;  
        $mail->setFrom($_ENV["EMAIL_FROM"], $_ENV["EMAIL_NAME"]);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = Html2Text::convert($body);
        $mail->CharSet = "UTF-8";
        $mail->Encoding = "base64";
        $mail->send();
        return true;
    }
    catch (Exception $ex) {
        return false;
    }
}
?>