<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
require "api/games/functions.php";
if (!$user || $user->type < 3) redirect_to_home();

$error = "";
$notice = "";
$games = all_games();

try {
    foreach ($games as $game) {
        $time = 1;
        foreach ($game->links as $link) $time = max($time, filemtime("./uploads/" . $link->path));
        db_query('UPDATE `nbhzvn_games` SET `file_updated_time` = ? WHERE `id` = ?', $time, $game->id);
    }
    $notice = "Cập nhật lại thời gian cập nhật cuối cùng của các tệp tin trong toàn bộ game thành công.";
}
catch (Exception $ex) {
    $notice = "Có lỗi xảy ra khi cập nhật lại thời gian cập nhật cuối cùng của các tệp tin trong toàn bộ game.<br>" . $ex->getMessage();
}

?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Thông Báo";
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
                <h3>Thông Báo</h3>
                <p><?php echo $notice ?></p>
                <p><a href="/"><button class="site-btn">Về Trang Chủ</button></p>
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