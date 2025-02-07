<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
if (!$user || !$user->id) redirect_to_home();

$notifications = $user->notifications();
db_query('UPDATE `nbhzvn_notifications` SET `is_unread` = 0 WHERE `user_id` = ?', $user->id);
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Thông Báo";
        require "head.php";
    ?>
</head>

<body>
    <!-- Header Section Begin -->
    <header class="header">
        <?php require "header.php" ?>
        <link rel="stylesheet" href="/css/toastr.css" />
    </header>
    <!-- Header End -->

    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="/"><i class="fa fa-home"></i> Trang Chủ</a>
                        <span>Thông Báo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Anime Section Begin -->
    <section class="anime-details spad">
        <div class="container">
            <div class="login__form page">
                <h3>Thông Báo</h3>
                <div style="text-align: right">
                    <a href="javascript:void(0)" onclick="deleteNotification()" class="nbhzvn_btn"><span>Xoá Tất Cả Thông Báo</span></a>
                </div><br>
                <div id="notifications">
                    <?php
                        if (count($notifications)) {
                            $limit = 0;
                            foreach ($notifications as $notification) {
                                echo $notification->to_html();
                                $limit++;
                                if ($limit == 20) break;
                            }
                        }
                        else echo '<p>Bạn chưa có thông báo nào.</p>' ?>
                    ?>
                </div>
                <?php if (count($notifications)) echo pagination(count($notifications)) ?>
            </div>
            </div>
        </section>
        <!-- Anime Section End -->

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
        <script src="/js/toastr.js"></script>
        <script src="/js/api.js?v=<?=$res_version?>"></script>
        <script src="/js/notifications.js?v=<?=$res_version?>"></script>

    </body>

    </html>