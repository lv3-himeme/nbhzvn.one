<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
if (!$user || $user->type < 3 || !get("id")) redirect_to_home();

$error = "";
$fatal_error = "";

try {
    $target = new Nbhzvn_User(intval(get("id")));
    if (!$target->id || $target->id == $user->id) redirect_to_home();
    else if (post("submit")) {
        $content = post("content"); $link = post("link");
        if (!check_csrf(post("csrf_token"))) $error = "Mã xác thực CSRF không đúng.";
        else if (!$user->verify_passphrase(post("password"))) $error = "Mật khẩu hiện tại không đúng.";
        else if (!$content) $error = "Vui lòng nhập nội dung của thông báo.";
        else {
            $target->send_notification($link, $content);
            $fatal_error = "Đã gửi thông báo đến tài khoản <b>" . htmlentities($target->display_name()) . "</b> thành công.";
        }
    }
}
catch (Exception $ex) {
    switch ($ex->getMessage()) {
        case DISALLOWED_TYPE: {
            $error = "Chức vụ này không được chấp nhận.";
            break;
        }
        default: {
            $error = "Có lỗi không xác định xảy ra. Vui lòng báo cáo cho nhà phát triển của website.";
            break;
        }
    }
}
refresh_csrf();
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Gửi Thông Báo";
        require __DIR__ . "/head.php";
    ?>
</head>

<body>
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
                <h3>Gửi Thông Báo</h3>
                <?php if ($fatal_error): ?>
                <p><?php echo $fatal_error ?></p>
                <p><a href="/"><button class="site-btn">Về Trang Chủ</button></p>
                <?php else: ?>
                <p>Bạn có thể gửi thông báo với nội dung và liên kết bất kì tới thành viên <b><?php echo $target->display_name() ?></b>.</p>
                <form action="" method="POST">
                    <div class="input__item" style="width: 100%">
                        <input type="password" name="password" placeholder="Mật Khẩu Hiện Tại" required>
                        <span class="icon_lock"></span>
                    </div>
                    <div class="input__item input__item__textarea">
                        <textarea placeholder="Nội Dung Thông Báo" name="content"></textarea>
                        <span class="icon_pencil"></span>
                    </div>
                    <p><i>Nội dung thông báo có hỗ trợ Markdown.</i></p>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="link" placeholder="Liên Kết (không bắt buộc)">
                        <span class="icon_link"></span>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="submit" class="site-btn" value="Submit">Gửi Thông Báo</button>
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
    <script src="/js/main.js?v=<?=$res_version?>"></script>

    <script>
        document.getElementById("role").value = "<?php echo $target->type ?>";
    </script>

</body>

</html>