<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
$parsedown->setMarkupEscaped(true);
if (!$user && !get("id")) {
    header("Location: /");
    die();
}
$profile_user = $user;
if (get("id") && is_numeric(get("id"))) $profile_user = new Nbhzvn_User(intval(get("id")));
$followed_games = $profile_user->get_followed_games();
$comments = $profile_user->get_comments();
if ($profile_user->id == $user->id) {
    $email_end_pos = strpos($user->email, "@");
    $censored_email = substr($user->email, 0, 2) . "••••••" . substr($user->email, $email_end_pos - 2, strlen($user->email) - $email_end_pos + 2);
}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Thông Tin Tài Khoản";
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

    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="/"><i class="fa fa-home"></i> Trang Chủ</a>
                        <span>Thông Tin Tài Khoản</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Anime Section Begin -->
    <section class="anime-details spad">
        <div class="container">
            <?php if ($user->id == $profile_user->id): ?>
            <div style="text-align: right">
                <a href="/logout" class="nbhzvn_btn"><span>Đăng Xuất</span></a>
            </div>
            <?php endif ?>
            <div class="anime__details__content">
                <div class="anime__details__text">
                    <div class="anime__details__title">
                        <h3><?php echo $profile_user->display_name ? $profile_user->display_name : $profile_user->username ?></h3>
                        <span><?php echo $profile_user->username ?> (ID: <?php echo $profile_user->id ?>)</span>
                    </div>
                    <div class="anime__details__widget">
                        <div class="row">
                            <div class="col-lg-6 col-md-6">
                                <ul>
                                    <li><span>Chức vụ:</span> <?php
                                        $account_type = array(
                                            1 => "Tài khoản thường",
                                            2 => "Uploader",
                                            3 => "Quản trị viên"
                                        );
                                        echo $account_type[$profile_user->type]
                                    ?></li>
                                    <li><span>Game đã theo dõi:</span> <?php echo count($followed_games) ?></li>
                                    <?php if ($profile_user->id == $user->id): ?>
                                        <li><span>Email:</span> <?php echo $censored_email ?></li>
                                    <?php endif ?>
                                </ul>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <ul>
                                    <li><span>Ngày tạo tài khoản:</span> <?php echo timestamp_to_string($profile_user->timestamp) ?></li>
                                    <li><span>Số bình luận đã gửi:</span> <?php echo count($comments) ?></li>
                                    <?php if ($profile_user->id == $user->id): ?>
                                        <li><span>ID Discord:</span> <?php echo $user->discord_id ? $user->discord_id : "Chưa liên kết" ?></li>
                                    <?php endif ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <p><?php if ($profile_user->description) echo $parsedown->text($profile_user->description); else echo '<i>Tài khoản này chưa đặt mô tả.</i>' ?></p>
                    <div class="anime__details__btn"><p>
                        <?php if ($profile_user->id == $user->id): ?>
                        <a href="/change_info" class="nbhzvn_btn"><span><i class="fa fa-pencil-square-o" aria-hidden="true"></i>&nbsp;&nbsp;Thay đổi thông tin</span></a>
                        <?php else: ?>
                        <?php endif ?>
                    </p></div><br>
                </div>
                <div class="row">
                    <div class="col-lg-8 col-md-8">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8">
                                <div class="section-title">
                                    <h4>Danh Sách Theo Dõi</h4>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4">
                                <div class="btn__all">
                                    <a href="/follows" class="primary-btn">Xem tất cả <span class="arrow_right"></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="anime__details__sidebar">
                            <div class="section-title">
                                <h5>Bình luận gần đây</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Anime Section End -->

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