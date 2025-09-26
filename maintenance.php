<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Website Đang Bảo Trì";
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
            <h3 class="nbhzvn_title">Website Đang Bảo Trì</h3>
            <p>Website đang trong quá trình bảo trì để sửa lỗi nghiêm trọng từ 8:00 - 23:00 ngày 26/9/2025. Bạn hãy quay lại sau nhé!</p>
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

</body>

</html>