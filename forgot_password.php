<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
if ($user) redirect_to_home();

const INCORRECT_INFORMATION = "Thông tin xác nhận không đúng. Vui lòng kiểm tra lại email của bạn.";

$error = "";
$fatal_error = "";
$notice = "";

try {
    if (post("submit")) {
        $username = post("username"); $email = post("email");
        if (!check_csrf(post("csrf_token"))) $error = "Mã xác thực CSRF không đúng.";
        else if (!$username || !$email) $error = "Vui lòng nhập đầy đủ thông tin.";
        else if (!check_email_validity($email)) $error = "Email này không được hỗ trợ.";
        else if (strlen($username) < 6 || special_chars($username)) $error = "Tên đăng nhập không hơp lệ.";
        else {
            $temp_user = new Nbhzvn_User($username);
            if ($temp_user->id && $temp_user->email == $email) $temp_user->send_forgot_password_email();
            else sleep(5);
            $fatal_error = "Nếu tên đăng nhập và email bạn đã nhập trùng với cơ sở dữ liệu ở trang web, thì một email chứa liên kết đặt lại mật khẩu đã được gửi tới email đó. Hãy kiểm tra email của bạn (cả hộp thư đến và thư rác) được gửi từ <b>" . $_ENV["EMAIL_FROM"] . "</b> và nhấn vào liên kết đã được đính kèm trong email để tiếp tục.";
        }
    }
    else if (get("username") && get("code")) {
        $temp_user = new Nbhzvn_User(get("username"));
        if (!$temp_user->id) $fatal_error = INCORRECT_INFORMATION;
        else {
            if (!$temp_user->verify_account_hash(get("code"))) $fatal_error = INCORRECT_INFORMATION;
            else {
                $verify_success = true;
                if (post("password_submit")) {
                    $password = post("password"); $confirm_password = post("confirm_password");
                    if (!check_csrf(post("csrf_token"))) $error = "Mã xác thực CSRF không đúng.";
                    else if (!$password || !$confirm_password) $error = "Vui lòng nhập đầy đủ thông tin.";
                    else if (strlen($password) < 8) $error = "Mật khẩu phải trên 8 kí tự.";
                    else if ($password != $confirm_password) $error = "Mật khẩu ở 2 ô không giống nhau.";
                    else {
                        $temp_user->change_passphrase($password);
                        db_query('UPDATE `nbhzvn_users` SET `verification_code` = "", `login_token` = "" WHERE `id` = ?', $temp_user->id);
                        $fatal_error = "Đã đổi mật khẩu thành công, bạn đã có thể đăng nhập lại bằng mật khẩu mới.";
                    }
                }
            }
        }
    }
}
catch (Exception $ex) {
    switch ($ex->getMessage()) {
        case DB_CONNECTION_ERROR: {
            $error = "Lỗi kết nối tới máy chủ. Vui lòng thử lại.";
            break;
        }
        case MISSING_INFORMATION: {
            $error = "Vui lòng nhập đầy đủ thông tin.";
            break;
        }
        default: {
            $error = "Có lỗi không xác định xảy ra. Vui lòng báo cáo cho nhà phát triển của website.";
            break;
        }
    }
}
if ($notice) die('
    <script>
        alert("' . $notice . '");
        document.location.href = "/verify";
    </script>
    <p>' . $notice . ' <a href="/verify">Tiếp tục</a></p>
');
refresh_csrf();
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Đặt Lại Mật Khẩu";
        require __DIR__ . "/head.php";
    ?>
</head>

<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    <!-- Header Section Begin -->
    <header class="header">
        <?php require "header.php"; ?>
    </header>
    <!-- Header End -->

    <!-- Normal Breadcrumb Begin -->
    <section class="normal-breadcrumb set-bg" data-setbg="/img/normal-breadcrumb.jpg">
    </section>
    <!-- Normal Breadcrumb End -->

    <!-- Signup Section Begin -->
    <section class="signup spad">
        <div class="container">
            <div class="login__form page">
                <h3>Yêu Cầu Đặt Lại Mật Khẩu</h3>
                <?php if ($fatal_error): ?>
                <p><?php echo $fatal_error ?></p>
                <p><a href="/"><button class="site-btn">Về Trang Chủ</button></p>
                <?php elseif ($verify_success): ?>
                <p>Xác minh tài khoản <b><?php echo $temp_user->username ?></b> thành công.</p>
                <p>Bạn đã có thể nhập mật khẩu mới cho tài khoản của bạn:</p>
                <form action="" method="POST">
                    <div class="input__item" style="width: 100%">
                        <input type="password" name="password" placeholder="Mật Khẩu" required>
                        <span class="icon_lock"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <input type="password" name="confirm_password" placeholder="Nhập Lại Mật Khẩu" required>
                        <span class="icon_lock"></span>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="password_submit" class="site-btn" value="Submit">Thay Đổi Mật Khẩu</button>
                </form>
                <?php else: ?>
                <form action="" method="POST">
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="username" placeholder="Tên Người Dùng" required>
                        <span class="icon_profile"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <input type="email" name="email" placeholder="Địa Chỉ Email" required>
                        <span class="icon_mail"></span>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="submit" class="site-btn" value="Submit">Gửi Yêu Cầu</button>
                </form>
                <?php endif ?>
            </div>
        </div>
    </section>
    <!-- Signup Section End -->

    <!-- Footer Section Begin -->
    <footer class="footer">
        <?php require "footer.php" ?>
      </footer>
      <!-- Footer Section End -->

    <!-- Js Plugins -->
    <script src="/js/jquery-3.3.1.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/player.js"></script>
    <script src="/js/jquery.nice-select.min.js"></script>
    <script src="/js/mixitup.min.js"></script>
    <script src="/js/jquery.slicknav.js"></script>
    <script src="/js/owl.carousel.min.js"></script>
    <script src="/js/main.js"></script>

</body>

</html>