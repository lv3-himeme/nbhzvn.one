<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
if ((!$user || !$user->verification_required) && !(get("username") && get("code"))) redirect_to_home();

const INCORRECT_INFORMATION = "Thông tin xác nhận không đúng. Vui lòng kiểm tra lại email của bạn.";

$error = "";
$fatal_error = "";
$notice = "";

try {
    if (get("resend") == "1") {
        if ($user->check_timeout("verification_email")) $error = "Vui lòng đợi ít nhất 1 phút trước khi yêu cầu gửi lại email.";
        else {
            $user->send_verification_email();
            $user->update_timeout("verification_email", time() + 60);
            $notice = "Đã yêu cầu gửi lại email thành công. Vui lòng kiểm tra lại email mới.";
        }
    }
    else if (get("username") && get("code")) {
        $temp_user = new Nbhzvn_User(get("username"));
        if (!$temp_user->id) $fatal_error = INCORRECT_INFORMATION;
        else {
            if (!$temp_user->verify_account_hash(get("code"))) $fatal_error = INCORRECT_INFORMATION;
            else {
                db_query('UPDATE `nbhzvn_users` SET `verification_required` = 0, `verification_code` = "" WHERE `id` = ?', $temp_user->id);
                $fatal_error = "Xác minh tài khoản thành công. Bạn đã có thể tiếp tục dùng trang web này!";
            }
        }
    }
    else if (post("submit")) {
        if (!check_csrf(post("csrf_token"))) $error = "Mã xác thực CSRF không đúng.";
        else if (!post("verification_code")) $error = "Vui lòng nhập đầy đủ thông tin.";
        else if (!$user->verify_account(post("verification_code"))) $error = "Mã xác minh không đúng. Vui lòng kiểm tra lại email của bạn.";
        else {
            db_query('UPDATE `nbhzvn_users` SET `verification_required` = 0, `verification_code` = "" WHERE `id` = ?', $user->id);
            $fatal_error = "Xác minh tài khoản thành công. Bạn đã có thể tiếp tục dùng trang web này!";
        }
    }
    else if ($user->first_verification()) {
        $user->send_verification_email();
        $user->update_timeout("verification_email", time() + 60);
    }
}
catch (Exception $ex) {
    switch ($ex->getMessage()) {
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
$email_end_pos = strpos($user->email, "@");
$censored_email = substr($user->email, 0, 2) . "••••••" . substr($user->email, $email_end_pos - 2, strlen($user->email) - $email_end_pos + 2);
refresh_csrf();
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Xác Minh Tài Khoản";
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
                <h3>Xác Minh Tài Khoản</h3>
                <?php if ($fatal_error): ?>
                <p><?php echo $fatal_error ?></p>
                <p><a href="/"><button class="site-btn">Về Trang Chủ</button></p>
                <?php else: ?>
                <p>Một email yêu cầu xác minh đã được gửi đến địa chỉ email bạn đã nhập (<b><?php echo $censored_email ?></b>) khi đăng ký tài khoản.</p>
                <p>Hãy kiểm tra email của bạn (cả hộp thư đến và thư rác) được gửi từ <b><?php echo $_ENV["EMAIL_FROM"] ?></b> và nhập mã, hoặc nhấn vào liên kết đã được đính kèm trong email để tiếp tục.</p>
                <p>• Bạn chưa nhận được email? <a href="/verify?resend=1">Gửi lại</a></p>
                <p>• Bạn muốn thay đổi địa chỉ email? <a href="/change_info">Thay đổi tại đây</a></p>
                <form action="/verify" method="POST">
                    <div class="input__item" style="width: 100%">
                        <input type="code" name="verification_code" placeholder="Mã Xác Minh" required>
                        <span class="icon_lock"></span>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="submit" class="site-btn" value="Submit">Xác Minh</button>
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
    <script src="/js/mixitup.min.js"></script>
    <script src="/js/jquery.slicknav.js"></script>
    <script src="/js/owl.carousel.min.js"></script>
    <script src="/js/main.js"></script>

</body>

</html>