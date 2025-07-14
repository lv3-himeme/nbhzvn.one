<?php
require __DIR__ . "/../api/functions.php";
require __DIR__ . "/../api/users/functions.php";
require __DIR__ . "/../api/users/cookies.php";
require __DIR__ . "/api/init.php";

if (time() < ENDING_TIME && (!$user || $user->type < ADMINISTRATOR_TYPE)) redirect_to_home();

function seconds_to_string($seconds) {
    $hours = floor($seconds / 3600);
    $seconds -= $hours * 3600;
    $minutes = floor($seconds / 60);
    $seconds -= $minutes * 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}

$ranking = array(
    1 => "D",
    2 => "C",
    3 => "B",
    4 => "A",
    5 => "S"
);



$sort = array(
    "real_playtime" => "real_playtime ASC, playtime ASC, saves ASC, ranking DESC",
    "saves" => "saves ASC, playtime ASC, real_playtime ASC, ranking DESC",
    "ranking" => "ranking DESC, playtime ASC, real_playtime ASC, saves ASC",
);
$sort_by = $sort[get("sort_by")] ? $sort[get("sort_by")] : "playtime ASC, real_playtime ASC, saves ASC, ranking DESC";
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
    <style>
        select {
            margin-top: 10px;
            margin-bottom: 5px;
            background-color: #27292a;
            border-radius: 23px;
            border: 1px solid #666;
            color: #ddd;
            font-size: 14px;
            padding: 5px;
        }
        .ranking {
            min-width: 1080px;
            width: 100%;
            color: #eee;
        }
        td, th {
            padding: 20px 10px;
        }
    </style>

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
                                <?php if ($stage < 2): ?>
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
                            <h2>Xếp Hạng</h2>
                            <p>Đây là bảng xếp hạng cuối cùng của sự kiện này. Quá trình trao giải cũng sẽ được diễn ra sớm.</p>
                            <p style="text-align: right" class="line">Sắp xếp theo: 
                                <select id="sortBy" onchange="window.location.href = './ranking?sort_by=' + this.value">
                                    <option value="ranking"<?php if (!get("sort_by") || get("sort_by") == "ranking") echo ' selected' ?>>Thứ hạng chính thức</option>
                                    <option value="playtime"<?php if (get("sort_by") == "playtime") echo ' selected' ?>>Thời gian chơi</option>
                                    <option value="real_playtime"<?php if (get("sort_by") == "real_playtime") echo ' selected' ?>>Thời gian chơi thực</option>
                                    <option value="saves"<?php if (get("sort_by") == "saves") echo ' selected' ?>>Số lần lưu game</option>
                                    <option value="ranking"<?php if (get("sort_by") == "ranking") echo ' selected' ?>>Thứ hạng trong game</option>
                                </select>
                            </p>
                            <div style="overflow-x: auto">
                                <table class="ranking">
                                    <tr>
                                        <th>Hạng</th>
                                        <th>Người chơi</th>
                                        <th>Tên tài khoản Discord</th>
                                        <th>Thời gian chơi</th>
                                        <th>Thời gian chơi thực(*)</th>
                                        <th>Số lần lưu game</th>
                                        <th>Thứ hạng trong game</th>
                                        <th>Điểm cuối cùng</th>
                                    </tr>
                                    <?php
                                        $rank = 1;
                                        $ranking_list = get_ranking($sort_by);
                                        if (!get("sort_by") || get("sort_by") == "ranking") {
                                            usort($ranking_list, function($a, $b) {
                                                return $b->points <=> $a->points;
                                            });
                                        }
                                        foreach ($ranking_list as $speedrun_user_tmp) {
                                            echo '
                                        <tr>
                                            <td>' . $rank . '</td>
                                            <td>' . (new Nbhzvn_User($speedrun_user_tmp->user_id))->display_name() . '</td>
                                            <td>' . ($speedrun_user_tmp->discord_username ? $speedrun_user_tmp->discord_username : "Không xác định") . '</td>
                                            <td>' . seconds_to_string($speedrun_user_tmp->playtime) . '</td>
                                            <td>' . seconds_to_string($speedrun_user_tmp->real_playtime) . '</td>
                                            <td>' . $speedrun_user_tmp->saves . '</td>
                                            <td>' . $ranking[$speedrun_user_tmp->ranking] . '</td>
                                            <td>' . $speedrun_user_tmp->points . '</td>
                                        </tr>
                                            ';
                                            $rank++;
                                        }
                                    ?>

                                </table>
                            </div>
                            <p class="line" style="margin-top: 15px"><i>(*) "Thời gian chơi thực" sẽ bắt đầu tính kể từ khi người chơi bắt đầu nhấn nút "Bắt đầu chơi" lần đầu tiên khi cuộc thi đã được bắt đầu, và sẽ được tính xuyên suốt thời gian speedrun (kể cả khi bị crash game hay tải lại tiến trình), và sẽ là yếu tố thứ 2 để xếp hạng speedrun. Thời gian chơi thực càng gần với thời gian chơi trong game thì kết quả speedrun càng minh bạch.</i></p>
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