<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";

$code = "Lỗi";
$text = "Đã có lỗi không xác định xảy ra. Vui lòng thử lại.";

if (is_numeric(get("code"))) {
    $code = get("code");
    http_response_code(intval(get("code")));
}
switch (get("code")) {
    case "403": {
        $text = "Bạn không có quyền truy cập vào trang này.";
        break;
    }
    case "404": {
        $text = "Thử kiểm tra lại địa chỉ URL bạn đã nhập. Không biết là bạn có nhập sai chỗ nào không?";
        break;
    }
    case "500": {
        $text = "Đã có lỗi không xác định xảy ra. Vui lòng liên hệ với nhà phát triển của website.";
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = $code;
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
            <h3 class="nbhzvn_title"><?php echo $code ?></h3>
            <p><?php echo $text ?></p>
            <p><a href="/"><button class="site-btn">Về Trang Chủ</button></p>
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