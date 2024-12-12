<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
require "api/users/discord.php";
$error = "";
$fatal_error = "";
if (post("submit")) {
    if ($user) {
        header("Location: /");
        die();
    }
    $discord_user = get_discord_info($_SESSION["discord_token_type"], $_SESSION["discord_access_token"]);
    if (!$discord_user->id) $error = "Có lỗi xảy ra khi yêu cầu thông tin từ Discord. Vui lòng đăng nhập lại.";
    else {
        $temp_user = get_user_from_discord_id($discord_user->id);
        if ($temp_user) {
            $temp_user->apply_cookie();
            header("Location: /");
            die();
        }
        else {
            $username = (post("type") == "1") ? $discord_user->username : post("username"); $password = post("password");
            if (!check_csrf(post("csrf_token"))) $error = "Mã xác thực CSRF không đúng.";
            else if (!$username || !$password) $error = "Vui lòng nhập đầy đủ thông tin.";
            else if (strlen($username) < 6 || special_chars($username)) $error = "Tên đăng nhập không hơp lệ.";
            else if (strlen($password) < 8) $error = "Mật khẩu phải trên 8 kí tự.";
            else {
                try {
                    $result = register($username, $discord_user->email, $password, 0, $discord_user->id);
                    if ($result == SUCCESS) {
                        $temp_user = new Nbhzvn_User($username);
                        $temp_user->apply_cookie();
                        header("Location: /");   
                        die();
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
                        case USERNAME_ALREADY_EXISTS: {
                            $error = "Tên đăng nhập đã tồn tại.";
                            break;
                        }
                        case EMAIL_ALREADY_EXISTS: {
                            $error = "Email này đã tồn tại.";
                            break;
                        }
                        default: {
                            $error = "Có lỗi không xác định xảy ra. Vui lòng báo cáo cho nhà phát triển của website.";
                            break;
                        }
                    }
                }
            }
        }
    }
}
else if (get("code")) {
    try {
        if (!check_csrf(get("state"))) $fatal_error = "Mã xác thực CSRF không đúng.";
        $result = request_token(get("code"));
        if (!$result->access_token) {
            switch ($result->error) {
                case "invalid_grant": {
                    $fatal_error = "Yêu cầu đăng nhập đã hết hạn. Vui lòng đăng nhập lại.";
                    break;
                }
                default: {
                    $fatal_error = "Có lỗi xảy ra khi yêu cầu thông tin từ Discord. Vui lòng đăng nhập lại.";
                    break;
                }
            }
        }
        else {
            $discord_user = get_discord_info($result->token_type, $result->access_token);
            if (!$discord_user->id) $fatal_error = "Có lỗi xảy ra khi yêu cầu thông tin từ Discord. Vui lòng đăng nhập lại.";
            else {
                $temp_user = get_user_from_discord_id($discord_user->id);
                if ($temp_user) {
                    if (!$user || !$user->id) {
                        $temp_user->apply_cookie();
                        header("Location: /");
                        die();
                    }
                    else $fatal_error = "Tài khoản Discord này đã được liên kết với một tài khoản khác rồi.";
                }
                else if ($user && $user->id) {
                    if ($user->discord_id) $fatal_error = "Tài khoản này đã được liên kết với một tài khoản Discord rồi. Hãy bỏ liên kết tài khoản hiện tại trước khi liên kết lại.";
                    else {
                        $user->update_discord_id($discord_user->id);
                        $fatal_error = "Đã liên kết tài khoản Discord <b>" . $discord_user->username . "</b> với tài khoản hiện tại của bạn (<b>" . $user->username . "</b>).";
                    }
                }
                else {
                    $_SESSION["discord_token_type"] = $result->token_type;
                    $_SESSION["discord_access_token"] = $result->access_token;
                }
            }
        }
    }
    catch (Exception $ex) {
        switch ($ex->getMessage()) {
            case DB_CONNECTION_ERROR: {
                $fatal_error = "Lỗi kết nối tới máy chủ. Vui lòng thử lại.";
                break;
            }
            case MISSING_INFORMATION: {
                $fatal_error = "Vui lòng nhập đầy đủ thông tin.";
                break;
            }
            default: {
                $fatal_error = "Có lỗi không xác định xảy ra. Vui lòng báo cáo cho nhà phát triển của website.";
                break;
            }
        }
    }
}
else {
    refresh_csrf();
    header('Location: https://discord.com/oauth2/authorize?client_id=' . $client_id . '&response_type=code&redirect_uri=' . $http . '%3A%2F%2F' . $host . '%2Fdiscord&scope=identify+email&state=' . get_csrf());
    die();
}
refresh_csrf();
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Kết Nối Với Discord";
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
                <h3>Kết Nối Với Discord</h3>
                <?php if ($fatal_error): ?>
                <p><?php echo $fatal_error ?></p>
                <p><a href="/"><button class="site-btn">Về Trang Chủ</button></p>
                <?php else: ?>
                <p>Tài khoản Discord của bạn chưa liên kết với tài khoản nào của trang web. Tuy nhiên thì bạn có thể đăng ký một tài khoản mới bằng biểu mẫu ở bên dưới, và liên kết với tài khoản Discord của bạn ngay sau đó.</p>
                <form action="" method="POST">
                    <p><input type="radio" name="type" value="1" id="radio1" onclick="updateRadio()"> <label>Đặt theo tên đăng nhập trên Discord của bạn (<b><?php echo $discord_user->username ?></b>)</label></input></p>
                    <p><input type="radio" name="type" value="2" id="radio2" onclick="updateRadio()" checked> <label>Chọn tên đăng nhập khác:</label></input></p>
                    <div class="input__item" style="width: 100%">
                        <input type="username" name="username" placeholder="Tên Đăng Nhập" id="username" onclick="setRadio()">
                        <span class="icon_profile"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <input type="password" name="password" placeholder="Mật Khẩu" required>
                        <span class="icon_lock"></span>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="submit" class="site-btn" value="Submit">Đăng Ký</button>
                </form><br>
                <p><i>Để liên kết tài khoản Discord này với tài khoản có sẵn, hãy đăng nhập vào tài khoản đó trước và vào phần <b>Thay đổi thông tin -> Liên kết tài khoản Discord</b>.</i></p>
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

    <script>
        function setRadio() {
            document.getElementById("radio1").checked = false;
            document.getElementById("radio2").checked = true;
            updateRadio();
        }

        function updateRadio() {
            if (document.getElementById("radio1").checked) document.getElementById("username").disabled = true;
            else document.getElementById("username").disabled = false;
        }
    </script>

</body>

</html>