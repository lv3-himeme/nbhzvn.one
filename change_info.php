<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
$error = "";
if (!$user) redirect_to_home();
if (post("submit")) {
    $current_password = post("current_password"); $password = post("password"); $confirm_password = post("confirm_password");
    $display_name = post("display_name"); $email = post("email"); $description = post("description");
    if (!check_csrf(post("csrf_token"))) $error = "Mã xác thực CSRF không đúng.";
    else if (!$current_password) $error = "Vui lòng nhập mật khẩu hiện tại.";
    else if (!$user->verify_passphrase($current_password)) $error = "Mật khẩu hiện tại không đúng.";
    else {
        if ($password && $confirm_password) {
            if (strlen($password) < 8) $error = "Mật khẩu phải trên 8 kí tự.";
            else if ($password != $confirm_password) $error = "Mật khẩu ở 2 ô không giống nhau.";
            else $change_password = $password;
        }
        if ($display_name) {
            if (strlen($display_name) < 6) $error = "Tên hiển thị phải trên 6 kí tự.";
            else if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $display_name)) $error = "Tên hiển thị không được chứa kí tự đặc biệt.";
            else $change_display_name = $display_name;
        }
        if ($email) {
            if (!check_email_validity($email)) $error = "Email này không được hỗ trợ.";
            if (email_exists($email)) $error = "Email này đã được sử dụng.";
            else $change_email = $email;
        }
        if (!$error) {
            $changed = false;
            if ($change_password) {
                $user->change_passphrase($change_password);
                $changed = true;
            }
            if ($change_display_name) {
                $user->change_display_name($change_display_name);
                $changed = true;
            }
            if ($change_email) {
                $user->change_email($change_email);
                $changed = true;
            }
            if ($description) {
                $user->change_description($description);
                $changed = true;
            }
            if ($changed) {
                setcookie("nbhzvn_username", "", time() - 3600);
                setcookie("nbhzvn_login_token", "", time() - 3600);
                db_query('UPDATE `nbhzvn_users` SET `login_token` = "" WHERE `id` = ?', $user->id);
                $notice = "Hoàn tất đổi thông tin, vui lòng đăng nhập lại với thông tin mới để tiếp tục.";
            }
            else $error = "Không có thông tin nào được đổi.";
        }
    }
}
if ($notice) die('
    <script>
        alert("' . $notice . '");
        document.location.href = "/";
    </script>
    <p>' . $notice . ' <a href="/">Tiếp tục</a></p>
');
refresh_csrf();
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Thay Đổi Thông Tin";
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
        <?php require "header.php" ?>
    </header>
    <!-- Header End -->

    <!-- Normal Breadcrumb Begin -->
    <section class="normal-breadcrumb set-bg" data-setbg="/img/normal-breadcrumb.jpg">
    </section>
    <!-- Normal Breadcrumb End -->

    <!-- Login Section Begin -->
    <section class="signup spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="login__form">
                        <h3>Thay Đổi Thông Tin</h3>
                        <form action="" method="POST">
                            <p>Nhập mật khẩu hiện tại trước khi muốn thay đổi thông tin.</p>
                            <div class="input__item">
                                <input type="password" name="current_password" placeholder="Mật Khẩu Hiện Tại" required>
                                <span class="icon_lock"></span>
                            </div>
                            <p>Bỏ trống một thông tin nếu bạn không muốn thay đổi thông tin đó.</p>
                            <?php if (!$user->verification_required): ?>
                            <div class="input__item">
                                <input type="name" name="display_name" placeholder="Tên Hiển Thị" value="<?php echo $user->display_name ?>">
                                <span class="icon_profile"></span>
                            </div>
                            <?php endif ?>
                            <div class="input__item">
                                <input type="email" name="email" placeholder="Email">
                                <span class="icon_mail"></span>
                            </div>
                            <?php if (!$user->verification_required): ?>
                            <div class="input__item input__item__textarea">
                                <textarea placeholder="Mô Tả" name="description"><?php echo $user->description ?></textarea>
                                <span class="icon_pencil"></span>
                            </div>
                            <p><i>Mô tả có hỗ trợ Markdown.</i></p>
                            <?php endif ?>
                            <div class="input__item">
                                <input type="password" name="password" placeholder="Mật Khẩu Mới">
                                <span class="icon_lock"></span>
                            </div>
                            <div class="input__item">
                                <input type="password" name="confirm_password" placeholder="Nhập Lại Mật Khẩu Mới">
                                <span class="icon_lock"></span>
                            </div>
                            <?php if ($user->verification_required): ?>
                            <p>Một số tuỳ chọn có thể bị ẩn đi đối với những tài khoản chưa được xác minh.</p>
                            <?php endif ?>
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                            <p style="color: #e36666"><i><?php echo $error ?></i></p>
                            <button type="submit" name="submit" class="site-btn" value="Submit">Thay Đổi</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="login__social__links">
                        <h3>Liên Kết Với Mạng Xã Hội</h3>
                        <?php if (!$user->verification_required): ?>
                        <ul>
                            <?php if ($user->discord_id): ?>
                            <li><a href="/discord_unlink" class="discord">Bỏ Liên Kết Discord</a></li>
                            <?php else: ?>
                            <li><a href="/discord" class="discord">Liên Kết Với Discord</a></li>
                            <?php endif ?>
                        </ul>
                        <?php else: ?>
                        <p>Vui lòng xác minh tài khoản trước khi liên kết với mạng xã hội khác.</p>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Login Section End -->

    <!-- Footer Section Begin -->
    <footer class="footer">
        <?php require "footer.php" ?>
      </footer>
      <!-- Footer Section End -->

      <!-- Search model Begin -->
      <div class="search-model">
        <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="search-close-switch"><i class="icon_close"></i></div>
            <form class="search-model-form">
                <input type="text" id="search-input" placeholder="Search here.....">
            </form>
        </div>
    </div>
    <!-- Search model end -->

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