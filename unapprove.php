<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
if (!$user || $user->type < 3 || !get("id")) redirect_to_home();

$error = "";
$fatal_error = "";

try {
    $game = new Nbhzvn_Game(intval(get("id")));
    if (!$game->id) redirect_to_home();
    else if (!$game->approved) $fatal_error = "Game <b>" . htmlentities($game->name) . "</b> đã bị ẩn rồi.";
    else if (post("submit")) {
        if (!check_csrf(post("csrf_token"))) $error = "Mã xác thực CSRF không đúng.";
        else if (!$user->verify_passphrase(post("password"))) $error = "Mật khẩu hiện tại không đúng.";
        else {
            $game->unapprove();
            $author = new Nbhzvn_User($game->uploader);
            if ($author->id && $author->id != $user->id) $author->send_notification("/games/" . $game->id, "Quản Trị Viên đã ẩn game **" . $game->name . "** của bạn. Bạn cần phải liên hệ lại với các Quản Trị Viên để hiển thị lại game của mình.");
            $fatal_error = "Đã ẩn game <b>" . htmlentities($game->name) . "</b> thành công. Game đã được trở lại mục các game cần được phê duyệt.";
        }
    }
}
catch (Exception $ex) {
    switch ($ex->getMessage()) {
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
        $title = "Ẩn Game";
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
                <h3>Ẩn Game</h3>
                <?php if ($fatal_error): ?>
                <p><?php echo $fatal_error ?></p>
                <p><a href="/"><button class="site-btn">Về Trang Chủ</button></p>
                <?php else: ?>
                <p>Nhập mật khẩu hiện tại của bạn để xác nhận ẩn game <b><?php echo $game->name ?></b> (ID: <?php echo $game->id ?>). Game sẽ được trở lại mục các game cần phê duyệt, và các Quản Trị Viên cần phê duyệt lại game này để hiển thị game trở lại.</p>
                <form action="" method="POST">
                    <div class="input__item" style="width: 100%">
                        <input type="password" name="password" placeholder="Mật Khẩu Hiện Tại" required>
                        <span class="icon_lock"></span>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="submit" class="site-btn" value="Submit">Tiến Hành Ẩn</button>
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

</body>

</html>