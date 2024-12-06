<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
if ($user) {
    header("Location: /");
    die();
}
$error = "";
if (post("submit")) {
    $username = post("username"); $password = post("password");
    if (!check_csrf(post("csrf_token"))) $error = "Mã xác thực CSRF không đúng.";
    else if (!$username || !$password) $error = "Vui lòng nhập đầy đủ thông tin.";
    else if (strlen($username) < 6 || special_chars($username)) $error = "Tên đăng nhập không hơp lệ.";
    else if (strlen($password) < 8) $error = "Mật khẩu phải trên 8 kí tự.";
    else {
        try {
            $result = login($username, $password);
            if ($result == SUCCESS) {
                $user = new Nbhzvn_User($username);
                $user->apply_cookie();
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
                case INCORRECT_CREDENTIALS: {
                    $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
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
refresh_csrf();
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Đăng Nhập";
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
        <div class="container">
            <div class="row">
                <div class="col-lg-2">
                    <div class="header__logo">
                        <a href="./index.html">
                            <img src="img/logo.png" alt="">
                        </a>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="header__nav">
                        <nav class="header__menu mobile-menu">
                            <ul>
                                <li><a href="./index.html">Homepage</a></li>
                                <li><a href="./categories.html">Categories <span class="arrow_carrot-down"></span></a>
                                    <ul class="dropdown">
                                        <li><a href="./categories.html">Categories</a></li>
                                        <li><a href="./anime-details.html">Anime Details</a></li>
                                        <li><a href="./anime-watching.html">Anime Watching</a></li>
                                        <li><a href="./blog-details.html">Blog Details</a></li>
                                        <li><a href="./signup.html">Sign Up</a></li>
                                        <li><a href="./login.html">Login</a></li>
                                    </ul>
                                </li>
                                <li><a href="./blog.html">Our Blog</a></li>
                                <li><a href="#">Contacts</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="header__right">
                        <a href="#" class="search-switch"><span class="icon_search"></span></a>
                        <a href="./login.html"><span class="icon_profile"></span></a>
                    </div>
                </div>
            </div>
            <div id="mobile-menu-wrap"></div>
        </div>
    </header>
    <!-- Header End -->

    <!-- Normal Breadcrumb Begin -->
    <section class="normal-breadcrumb set-bg" data-setbg="img/normal-breadcrumb.jpg">
    </section>
    <!-- Normal Breadcrumb End -->

    <!-- Login Section Begin -->
    <section class="login spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="login__form">
                        <h3>Đăng Nhập</h3>
                        <form action="" method="POST">
                            <div class="input__item">
                                <input type="text" name="username" placeholder="Tên Người Dùng" required>
                                <span class="icon_profile"></span>
                            </div>
                            <div class="input__item">
                                <input type="password" name="password" placeholder="Mật Khẩu" required>
                                <span class="icon_lock"></span>
                            </div>
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                            <p style="color: #e36666"><i><?php echo $error ?></i></p>
                            <button type="submit" name="submit" class="site-btn" value="Submit">Đăng Nhập</button>
                        </form>
                        <a href="/forgot_password" class="forget_pass">Quên mật khẩu?</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="login__register">
                        <h3>Chưa Có Tài Khoản?</h3>
                        <a href="/register" class="primary-btn">Đăng Ký</a>
                    </div>
                </div>
            </div>
            <div class="login__social">
                <div class="row d-flex justify-content-center">
                    <div class="col-lg-6">
                        <div class="login__social__links">
                            <span>hoặc là</span>
                            <ul>
                                <li><a href="#" class="discord">Đăng Nhập Bằng Discord</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Login Section End -->

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
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/player.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/mixitup.min.js"></script>
    <script src="js/jquery.slicknav.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>


</body>

</html>