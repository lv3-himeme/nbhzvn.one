<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
if (!$user || !get("id")) redirect_to_home();

$error = "";
$fatal_error = "";

try {
    $game = new Nbhzvn_Game(intval(get("id")));
    if (get("success")) $fatal_error = 'Đã chuyển quyền quản lý game cho <b>' . get("display_name") . '</b> thành công. Bạn đã có thể quay về trang chủ.';
    else if (!$game->id || $game->uploader != $user->id) redirect_to_home();
}
catch (Exception $ex) {
    switch ($ex->getMessage()) {
        default: {
            $error = "Có lỗi không xác định xảy ra. Vui lòng báo cáo cho nhà phát triển của website.";
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Chuyển Quyền Quản Lý Game";
        require __DIR__ . "/head.php";
    ?>
    <link rel="stylesheet" href="/css/toastr.css" />
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
                <h3>Chuyển Quyền Quản Lý Game</h3>
                <?php if ($fatal_error): ?>
                <p><?php echo $fatal_error ?></p>
                <p><a href="/"><button class="site-btn">Về Trang Chủ</button></p>
                <?php else: ?>
                <p>Bạn đang chuẩn bị chuyển quyền quản lý game <b><?php echo $game->name ?></b> (ID: <?php echo $game->id ?>) cho một thành viên khác.</p>
                <p><i>Khi việc chuyển đổi đã thành công, người nhận sẽ có toàn quyền quản lý game này, và bạn cũng sẽ không còn quyền quản lý game này nữa.<br>Đồng thời, tên của người tải lên ở phần thông tin game cũng sẽ được thay đổi thành tên của người đó.</i></p>
                <hr>
                <p>Hãy tìm kiếm tên của thành viên bạn muốn chuyển:</p>
                <form action="" onsubmit="search(); return false">
                    <div class="input__item" style="width: 100%">
                        <input type="text" id="query" placeholder="Tìm Kiếm Thành Viên">
                        <span class="icon_profile"></span>
                    </div>
                    <button type="button" class="site-btn" onclick="search()">Tìm kiếm</button>
                </form>
                <div style="padding: 10px; margin-top: 10px" id="members"></div>
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
    <script>gameId = <?php echo $game->id ?></script>
    <script src="/js/jquery-3.3.1.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/mixitup.min.js"></script>
    <script src="/js/jquery.slicknav.js"></script>
    <script src="/js/owl.carousel.min.js"></script>
    <script src="/js/toastr.js"></script>
    <script src="/js/main.js?v=<?=$res_version?>"></script>
    <script src="/js/api.js?v=<?=$res_version?>"></script>
    <script src="/js/modal.js?v=<?=$res_version?>"></script>
    <script src="/js/transfer.js?v=<?=$res_version?>"></script>

</body>

</html>