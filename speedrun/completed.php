<?php
require __DIR__ . "/../api/functions.php";
require __DIR__ . "/../api/users/functions.php";
require __DIR__ . "/../api/users/cookies.php";
require __DIR__ . "/api/init.php";

function seconds_to_string($seconds) {
    $hours = floor($seconds / 3600);
    $seconds -= $hours * 3600;
    $minutes = floor($seconds / 60);
    $seconds -= $minutes * 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}

if (!$user) redirect_to_home();
if ($user) $speedrun_user = new Nbhzvn_Speedrunner($user->id);
if (!$speedrun_user->playtime) redirect_to_home();

$ranking = array(
    1 => "D",
    2 => "C",
    3 => "B",
    4 => "A",
    5 => "S"
);
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
<?php
$title = "Sự kiện Speedrun của Cộng đồng Nobihaza Việt Nam (Nobihaza Vietnam Community)";
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
$parsedown->setMarkupEscaped(true);
use Soundasleep\Html2Text;
$meta_title = ($profile_user ? ($profile_user->display_name() . " ") : ($game ? ($game->name . " - ") : ($title ? ($title . " - ") : ""))) . "Nobihaza Vietnam Community Collection";
$meta_description = $game ? explode("\n", Html2Text::convert($parsedown->text($game->description)))[0] : META_DESCRIPTION;
?>
    <meta name="description" content="<?php echo $meta_description ?>" />
    <meta name="keywords" content="nobihaza,nobihaza game,nobihaza community collection,nobihaza vietnam,nobita's resident evil,nobihaza tieng viet,tai game nobihaza" />
    <meta name="author" content="Serena1432" />
    <meta name="copyright" content="(C) 2024 Serena1432" />
    <meta name="application-name" content="Nobihaza Vietnam Community Collection" />
    <meta property="og:title" content="<?php echo $meta_title ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:image" content="<?php echo ($game && str_contains($_SERVER["REQUEST_URI"], "games")) ? ("/uploads/" . $game->image) : "https://nbhzvn.one/img/logo.png" ?>" />
    <meta property="og:url" content="<?php echo $http . "://" . $host . $_SERVER["REQUEST_URI"] ?>" />
    <meta property="og:description" content="<?php echo $meta_description ?>" />
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="<?php echo $meta_title ?>" />
    <meta name="twitter:description" content="<?php echo $meta_description ?>" />
    
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="icon" href="/favicon.ico">
    <link rel="apple-touch-icon" type="image/png" sizes="180x180" href="https://nbhzvn.one/img/icon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="https://nbhzvn.one/img/icon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="https://nbhzvn.one/img/icon/android-chrome-512x512.png">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php if (!$title) echo 'Nobihaza Vietnam Collection'; else echo $title; ?></title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Geologica" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="https://nbhzvn.one/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="https://nbhzvn.one/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="https://nbhzvn.one/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="https://nbhzvn.one/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="https://nbhzvn.one/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="https://nbhzvn.one/css/bootstrap-darkly.min.css" type="text/css">
    <link rel="stylesheet" href="https://nbhzvn.one/css/toastr.css" type="text/css">
    <link rel="stylesheet" href="https://nbhzvn.one/css/style.css?v=<?=$res_version?>" type="text/css">
    <link rel="stylesheet" href="./speedrun.css?v=<?=time()?>" type="text/css">
    <link rel="stylesheet" href="./time_counter.css?v=<?=time()?>" type="text/css">

</head>

<body>
    <!-- Header Section Begin -->
    <header class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="header-flex d-flex align-items-center flex-grow-1">
                    <div class="header__logo">
                        <a href="/">
                            <img src="https://nbhzvn.one/img/logo.png" alt="">
                        </a>
                    </div>
                    <div class="header__nav" style="margin-left: 20px">
                        <nav class="header__menu mobile-menu">
                            <ul>
                                <li><a href="https://nbhzvn.one">Về Trang Chính</a></li>
                                <?php if ($stage < 3): ?>
                                <li><a href="https://speedrun.nbhzvn.one/test/?language=Vietnamese">Thi Thử</a></li>
                                <?php endif; ?>
                                <li class="nbhzvn_mobile_user">
                                    <?php if ($user): ?>
                                    <a href="https://nbhzvn.one/profile"><?php echo $user->display_name(); ?></span></a>
                                    <ul class="dropdown">
                                        <li><a href="https://nbhzvn.one/profile">Thông Tin Cá Nhân</a></li>
                                        <li><a href="https://nbhzvn.one/change_info">Thay Đổi Thông Tin</a></li>
                                        <li><a href="https://nbhzvn.one/logout?speedrun=1">Đăng Xuất</a></li>
                                    </ul>
                                    <?php else: ?>
                                    <a href="https://nbhzvn.one/login?speedrun=1">Đăng Nhập</a>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="header__right d-flex align-items-center flex-shrink-0">
                    <nav class="header__menu">
                        <ul>
                            <?php if ($user): ?>
                                <li style="padding: 13px 0"><a href="/profile" class="nbhzvn_user_icon">
                                    <span class="nbhzvn_avatar_container"><span class="nbhzvn_avatar"><span class="nbhzvn_username"><?php echo substr($user->display_name(), 0, 1); ?></span></span> <span class="arrow_carrot-down"></span></span></a>
                                    <ul class="dropdown nbhzvn_user_dropdown" style="left: -180px">
                                        <li><a href="https://nbhzvn.one/profile"><b><?php echo $user->display_name(); ?></b></a></li>
                                        <li><a href="https://nbhzvn.one/change_info">Thay Đổi Thông Tin</a></li>
                                        <li><a href="https://nbhzvn.one/logout?speedrun=1">Đăng Xuất</a></li>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li><a href="https://nbhzvn.one/login?speedrun=1"><span class="icon_profile"></span> <span class="nbhzvn_username" style="margin-left: 10px">Đăng Nhập</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <div id="mobile-menu-wrap"></div>
        </div>
    </header>
    <!-- Header End -->

    <!-- Signup Section Begin -->
    <section class="blog-details spad">
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-lg-8">
                    <div class="blog__details__content">
                        <div class="blog__details__text faq">
                            <h2>Đã Hoàn Thành Phần Chơi</h2>
                            <?php if (time() >= 1752386400): ?>
                                <p>Chúc mừng bạn đã hoàn thành xuất sắc phần chơi của mình. Phần chơi của bạn đã được hệ thống ghi nhận và sẽ được sử dụng để xét hạng.</p>
                                <p>Cho dù kết quả cuối cùng có như thế nào thì bạn cũng đã cố gắng hết sức rồi. Cảm ơn bạn rất nhiều vì đã tham gia sự kiện này!</p>
                                <p>Hẹn gặp lại bạn ở sự kiện tiếp theo!</p>
                                <p>Thứ hạng cuối cùng sẽ được công bố vào <b>18:00 ngày 13/7/2025</b>.</p>
                            <?php else: ?>
                                <p>Bạn đã kết thúc phần chơi thử nghiệm của mình. Cảm ơn bạn vì đã tham gia thử nghiệm!</p>
                                <p>Hãy chuẩn bị tinh thần thật tốt để tham gia sự kiện chính thức vào <b>13:00 ngày 13/7/2025</b> nhé!</p>
                                <p>Phần chơi này của bạn sẽ bị xóa khi sự kiện chính thức bắt đầu. Sau đó thì bạn sẽ tiến hành chơi lại từ đầu.</p>
                            <?php endif ?>
                            <h3>Thông tin phần chơi của bạn</h3>
                            <div style="margin: 20px 0; display: grid; text-align: center; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 8px">
                                <div>
                                    <div style="font-size: 14pt"><b>Thứ hạng</b></div>
                                    <div style="font-size: 20pt; margin-top: 10px;"><?php echo $ranking[$speedrun_user->ranking] ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 14pt"><b>Thời gian chơi</b></div>
                                    <div style="font-size: 20pt; margin-top: 10px;"><?php echo seconds_to_string($speedrun_user->playtime) ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 14pt"><b>Thời gian chơi thực</b></div>
                                    <div style="font-size: 20pt; margin-top: 10px;"><?php echo seconds_to_string($speedrun_user->real_playtime) ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 14pt"><b>Số lần lưu game</b></div>
                                    <div style="font-size: 20pt; margin-top: 10px;"><?php echo $speedrun_user->saves ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section Begin -->
    <footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="footer__logo">
                    <a href="./index.html"><img src="https://nbhzvn.one/img/logo.png" alt=""></a>
                </div>
                <p style="margin-top: 10px">Gia nhập máy chủ Discord của cộng đồng <a href="https://discord.gg/QpMuX3gQ5u" target="__blank">tại đây</a>.</p>
            </div>
            <div class="col-lg-6">
            </div>
            <div class="col-lg-3">
                <p><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                  Developed in 2024 by <a href="https://s1432.org" target="_blank">Serena1432</a> | Designed with <i class="fa fa-heart" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a>.<br>
                  Xem mã nguồn của website này ở trên <a href="https://github.com/Serena1432/NobihazaVietnamCollection" target="_blank">GitHub</a>.
                  <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. --></p>
              </div>
          </div>
      </div>
      </footer>
      <!-- Footer Section End -->

    <!-- Js Plugins -->
    <script src="https://nbhzvn.one/js/jquery-3.3.1.min.js"></script>
    <script src="https://nbhzvn.one/js/bootstrap.min.js"></script>
    <script src="https://nbhzvn.one/js/mixitup.min.js"></script>
    <script src="https://nbhzvn.one/js/jquery.slicknav.js"></script>
    <script src="https://nbhzvn.one/js/owl.carousel.min.js"></script>
    <script src="https://nbhzvn.one/js/toastr.js"></script>
    <script src="https://nbhzvn.one/js/api.js"></script>
    <script src="https://nbhzvn.one/js/modal.js"></script>
    <script src="https://nbhzvn.one/js/main.js?v=<?=$res_version?>"></script>

</body>

</html>