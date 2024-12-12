<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
if (!$user || !$user->ban_information) redirect_to_home();
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Tài Khoản Bị Vô Hiệu";
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
            <h3><b>Tài Khoản Bị Vô Hiệu</b></h3>
            <p>Tài khoản này của bạn đã bị vô hiệu hoá bởi Quản Trị Viên của trang web này vào <b><?php echo timestamp_to_string($user->ban_information->timestamp) ?></b> với lý do: <?php echo $user->ban_information->reason ?></p>
            <p>Vui lòng liên hệ với đội ngũ Quản Trị Viên của trang web để biết thêm chi tiết.</p>
            <p><a href="/logout"><button class="site-btn">Đăng Xuất</button></p>
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

</body>

</html>