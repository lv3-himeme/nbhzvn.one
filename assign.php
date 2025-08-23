<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
if (!$user || $user->type < 3 || !get("id")) redirect_to_home();

$error = "";
$fatal_error = "";

try {
    $assign_user = new Nbhzvn_User(intval(get("id")));
    if (!$assign_user->id || $assign_user->id == $user->id || $assign_user->type == 3) redirect_to_home();
    else if (post("submit")) {
        $role = intval(post("role"));
        if (!check_csrf(post("csrf_token"))) $error = "Mã xác thực CSRF không đúng.";
        else if (!$user->verify_passphrase(post("password"))) $error = "Mật khẩu hiện tại không đúng.";
        else if (!$role) $error = "Vui lòng chọn chức vụ bạn muốn thay đổi.";
        else {
            $assign_user->change_type($role);
            $assign_user->send_notification(null, "Một Quản Trị Viên vừa mới thay đổi chức vụ của bạn thành **" . $type_vocab[$role] . "**.");
            $fatal_error = "Đã đổi vai trò cho <b>" . htmlentities($assign_user->username) . "</b> thành công.";
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
        $title = "Thay Đổi Chức Vụ";
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
                <h3>Thay Đổi Chức Vụ</h3>
                <?php if ($fatal_error): ?>
                <p><?php echo $fatal_error ?></p>
                <p><a href="/"><button class="site-btn">Về Trang Chủ</button></p>
                <?php else: ?>
                <p>Chọn vai trò mới của thành viên <b><?php echo $assign_user->username ?></b> (ID: <?php echo $assign_user->id ?>):</p>
                <form action="" method="POST">
                    <div class="input__item" style="width: 100%">
                        <input type="password" name="password" placeholder="Mật Khẩu Hiện Tại" required>
                        <span class="icon_lock"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <select name="role" placeholder="Chức Vụ" required id="role">
                            <option value="1">Tài khoản thường<?php if ($assign_user->type == 1) echo " (Hiện tại)" ?></option>
                            <option value="2">Uploader<?php if ($assign_user->type == 2) echo " (Hiện tại)" ?></option>
                            <option value="3">Quản trị viên<?php if ($assign_user->type == 3) echo " (Hiện tại)" ?></option>
                        </select>
                        <span class="icon_pencil"></span>
                    </div>
                    <p><i><b>CẢNH BÁO!</b> Nếu bạn gán chức vụ Quản trị viên cho thành viên này, bạn sẽ không thể thay đổi chức vụ của thành viên này xuống thấp hơn được nữa.</i></p>
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="submit" class="site-btn" value="Submit">Thay Đổi</button>
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
    <script src="/js/base64.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/mixitup.min.js"></script>
    <script src="/js/jquery.slicknav.js"></script>
    <script src="/js/owl.carousel.min.js"></script>
    <script src="/js/main.js?v=<?=$res_version?>"></script>

    <script>
        document.getElementById("role").value = "<?php echo $assign_user->type ?>";
    </script>

</body>

</html>