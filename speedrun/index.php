<?php
require __DIR__ . "/../api/functions.php";
require __DIR__ . "/../api/users/functions.php";
require __DIR__ . "/../api/users/cookies.php";
require __DIR__ . "/api/init.php";

/*
============================================================
Stage
============================================================
0: Not Opened
1: Preparing (8:00 7/7 - 23:59 11/7)
2: Closing Registration (0:00 12/7 - 12:59 13/7)
3: Ongoing (13:00 13/7 - 16:29 13/7)
4: Ranking (16:30 13/7 - 18:00 13/7)
5: End
*/

$stage = 0; $current_time = time(); $ending_time = 1751850000;
if ($current_time >= 1751850000 && $current_time < 1752253200) {
    $stage = 1;
    $ending_time = 1752253200;
}
else if ($current_time >= 1752253200 && $current_time < 1752386400) {
    $stage = 2;
    $ending_time = 1752386400;
}
else if ($current_time >= 1752386400 && $current_time < 1752399000) {
    $stage = 3;
    $ending_time = 1752399000;
}
else if ($current_time >= 1752399000 && $current_time < 1752404400) {
    $stage = 4;
    $ending_time = 1752404400;
}
else if ($current_time >= 1752404400) $stage = 5;

$stage_text = [
    "Đợt đăng ký sắp mở rồi, bạn hãy đợi một chút nhé!",
    "Đợt đăng ký đang được mở!",
    "Quá trình tiếp nhận người tham gia đã kết thúc, bạn hãy đợi đến lúc sự kiện được bắt đầu nhé!",
    "Sự kiện đã được bắt đầu rồi, bạn hãy gia nhập máy chủ Discord của cộng đồng để theo dõi nhé!",
    "Quá trình xếp hạng và xét giải đã bắt đầu!",
    "Sự kiện đã chính thức kết thúc, cảm ơn bạn đã quan tâm!"
];

if ($user) $speedrun_user = new Nbhzvn_Speedrunner($user->id);
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
                            <h2>Sự kiện Speedrun của Cộng đồng Nobihaza Việt Nam (Nobihaza Vietnam Community)</h2>
                            <h3><?php echo $stage_text[$stage] ?></h3>
                            <?php if ($stage < 3): ?>
                            <p>Hiện tại đã có <b><?php echo db_query("SELECT `id` FROM `nbhzvn_speedrunners` WHERE 1")->num_rows ?></b> người đã đăng ký tham gia sự kiện.</p>
                            <?php endif ?>
                            <div class="time-container">
                                <div><div class="time-number" id="countdownDays">--</div><div class="time-text">ngày</div></div>
                                <div><div class="time-number" id="countdownHours">--</div><div class="time-text">giờ</div></div>
                                <div><div class="time-number" id="countdownMinutes">--</div><div class="time-text">phút</div></div>
                                <div><div class="time-number" id="countdownSeconds">--</div><div class="time-text">giây</div></div>
                            </div><br>
                            <?php if ($stage == 1): ?>
                            <?php if ($user): ?>
                            <?php if ($speedrun_user->id): ?>
                            <p style="text-align: center; padding: 20px"><i>Bạn đã đăng ký tham gia sự kiện rồi. Chúc bạn may mắn trong sự kiện sắp tới!</i></p>
                            <?php else: ?>
                            <p style="text-align: center; padding: 20px"><button class="site-btn" id="registerBtn" onclick="check()">Đăng ký</button></p>
                            <?php endif ?>
                            <?php else: ?>
                            <p style="text-align: center; padding: 20px"><i>Bạn cần đăng nhập để có thể đăng ký tham gia.</i></p>
                            <?php endif ?>
                            <?php elseif ($stage > 1 && $stage < 5): ?>
                            <?php if ($user->id && $speedrun_user->id): ?>
                                <?php if ($speedrun_user->playtime): ?>
                                    <p style="text-align: center; padding: 20px"><a href="./completed"><button class="site-btn">Xem Thông Tin Phần Chơi</button></a></p>
                                <?php else: ?>
                                    <?php if ($stage == 2 && time() < 1752361200): ?>
                                        <p style="text-align: center; padding: 20px"><i>Bạn có thể nhấn nút bên dưới để tham gia thử nghiệm hệ thống trước sự kiện chính thức.</i></p>
                                        <p style="text-align: center; padding: 20px"><a href="./play/?language=Vietnamese"><button class="site-btn">Tham Gia Thử Nghiệm</button></a></p>
                                        <p>Thời gian thử nghiệm sẽ bắt đầu từ <b>0:00 ngày 12/7/2025</b> đến <b>12:00 ngày 13/7/2025</b>.</p>
                                    <?php elseif ($stage == 3): ?>
                                        <p style="text-align: center; padding: 20px"><i>Nhấn nút bên dưới để bắt đầu tham gia.</i></p>
                                        <p style="text-align: center; padding: 20px"><a href="./play/?language=Vietnamese"><button class="site-btn">Bắt Đầu</button></a></p>
                                    <?php endif ?>
                                <?php endif ?>
                            <?php endif ?>
                            <p style="text-align: center; padding: 20px"><a href="https://discord.gg/QpMuX3gQ5u"><button class="site-btn">Tham gia máy chủ Discord</button></a></p>
                            <?php elseif ($stage == 5): ?>
                            <p style="text-align: center; padding: 20px"><a href="/ranking"><button class="site-btn">Xem thứ hạng</button></a></p>
                            <?php endif ?>
                            <?php if ($stage < 2): ?>
                            <p>Nếu bạn đang muốn thử nghiệm trước game sẽ được sử dụng trong sự kiện, bạn có thể truy cập nó <a href="https://speedrun.nbhzvn.one/test/?language=Vietnamese">tại đây</a>.</p>
                            <?php endif; ?>
                            <h3>Nội dung</h3>
                            <p>Vẫn giống như năm ngoái với Nobihaza 2 Remake, đây sẽ là một sự kiện nhỏ với mục đích vui là chính, và cũng là để các thành viên trong cộng đồng có thể thử so tài với nhau. Bất kì ai có đủ điều kiện đều có thể tham gia sự kiện này!</p>
                            <h3>Game sẽ được sử dụng</h3>
                            <p>Game sẽ được sử dụng trong sự kiện năm nay là <b><a href="https://nbhzvn.one/games/149">Nobihaza Mudai ni Kaizouban 1 (bản dịch của Quang Hiếu)</a></b>.</p>
                            <h3>Lịch trình</h3>
                            <ul>
                                <li><b>8:00 ngày 7/7/2025 - 23:59 ngày 11/7/2025</b><br>Mở đăng ký cho toàn bộ thành viên có đủ điều kiện trong cộng đồng.</li>
                                <li><b>0:00 ngày 12/7/2025</b><br>Đóng quá trình đăng ký và bắt đầu cho các thành viên tham gia thử nghiệm web trước. Nếu có lỗi thì Ban Tổ Chức sẽ tiến hành sửa lỗi trước thời gian diễn ra sự kiện.</li>
                                <li><b>13:00 ngày 13/7/2025</b><br>Sự kiện chính thức bắt đầu. Ban Tổ Chức bắt đầu tập trung các thành viên.</li>
                                <li><b>13:15 ngày 13/7/2025</b><br>Bắt đầu cuộc thi Speedrun. Các thành viên bắt đầu chơi và thực hiện phần thi của mình.</li>
                                <li><b>16:30 ngày 13/7/2025</b><br>Bắt đầu quá trình xét hạng. Các thành viên chưa hoàn thành phần chơi sẽ bắt buộc phải kết thúc phần chơi của mình.</li>
                                <li><b>18:00 ngày 13/7/2025</b><br>Công bố kết quả và tiến hành trao thưởng.</li>
                            </ul>
                            <h3>Điều kiện tham gia</h3>
                            <p>Nhìn dài dòng thế thôi chứ điều kiện tham gia rất đơn giản:</p>
                            <ul>
                                <li><b>Có máy tính hoặc điện thoại di động có đủ khả năng để chơi game và stream lên Discord cùng một lúc.</b></li>
                                <li><b>Có đường truyền mạng ổn định trong suốt quá trình diễn ra sự kiện.</b></li>
                                <li>Có một tài khoản <b>đã liên kết với Discord</b> trên Nobihaza Vietnam Community Collection (nbhzvn.one).</li>
                                <li>Đã tham gia vào <b><a href="https://discord.gg/QpMuX3gQ5u" target="__blank">máy chủ NobiRE Community</a></b>.
                                <li>Đang không bị cấm (ban) hoặc tắt tiếng (mute) trên máy chủ NobiRE Community.</li>
                                <li>Không phải là thành viên thuộc Ban Tổ Chức.</li>
                            </ul>
                            <p>Một thành viên sẽ thuộc Ban Tổ Chức nếu:</p>
                            <ul>
                                <li>Trực tiếp tham gia đóng góp vào sự kiện, bao gồm (các) lập trình viên tạo ra trang web hoặc sửa lỗi game, và những người trực tiếp giám sát hoạt động của các thành viên tham gia.</li>
                                <li>Đóng góp vật phẩm vào giải thưởng của sự kiện.</li>
                            </ul>
                            <p>Nếu bạn trở thành thành viên của Ban Tổ Chức sau khi đã đăng ký, đơn đăng ký của bạn sẽ bị hủy bỏ.</p>
                            <p>Còn nếu bạn không trở thành thành viên Ban Tổ Chức thì cho dù bạn có là chủ server, hay là thành viên Ban Giám Hiệu thì bạn cũng có thể tham gia nếu đáp ứng đủ các điều kiện còn lại!</p>
                            <h3>Thành viên Ban Tổ Chức</h3>
                            <p><i>Danh sách này vẫn chưa phải là danh sách cuối cùng. Có thể sẽ có thêm người tham gia trước khi đợt đăng ký kết thúc.</i></p>
                            <h4>Trưởng Ban</h4>
                            <ul>
                                <li><b>Serena1432</b> (s1432_nbhz)<br>Hiệu Trưởng / Lập trình viên / Quản lý, giám sát cuộc thi / Người cung cấp giải thưởng phụ</li>
                                <li><b>DuonBui</b> (duon_31)<br>Thành viên Ban Giám Hiệu / Quản lý, giám sát cuộc thi / Người cung cấp giải thưởng phụ</li>
                                <li><b>Shen / Luna</b> (only_lunaaa)<br>Người cung cấp giải thưởng chính</li>
                            </ul>
                            <h4>Bình luận viên</h4>
                            <ul>
                                <li><b>azurihanakawa_</b></li>
                            </ul>
                            <h4>Quản lý, giám sát cuộc thi</h4>
                            <ul>
                                <li><b>ancute1240</b></li>
                            </ul>
                            <h4>Các thành viên đã đóng góp vật phẩm</h4>
                            <ul>
                                <li><b>Ser</b> (knzkser0817) - 1.245.311 Cowoncy + 1 gói Nitro Basic 1 tháng</li>
                                <li><b>Maliss</b> (shado2k9) - 2.000.000 Cowoncy</li>
                                <li><b>Yui</b> (yui_vollerei_030) - 799.999 Cowoncy</li>
                                <li><b>Pucci</b> (pucci3953) - 600.000 Cowoncy</li>
                                <li><b>Leia</b> (smeraldo.leia) - 500.000 Cowoncy</li>
                            </ul>
                            <h3>Giải thưởng</h3>
                            <p><i>Giải thưởng sẽ có thể thay đổi nếu như có thêm thành viên đóng góp trước khi đợt đăng ký kết thúc.</i></p>
                            <p>Sự kiện lần này sẽ chia thành hai bảng: một bảng dành cho người chơi trên máy tính và một bảng dành cho người chơi trên thiết bị di động. Phần thưởng của cả hai bảng sẽ là như nhau.</p>
                            <h4>Giải Nhất</h4>
                            <ul>
                                <li>1 tháng Discord Nitro Boost</li>
                                <li>1.500.000 Cowoncy</li>
                                <li>Vai trò <b>Quán Quân Event Lần 3</b> trên máy chủ NobiRE Community</li>
                                <li>1 vai trò riêng vô thời hạn với tên và màu tùy chỉnh do bạn tự chọn. Có thể thay đổi bất cứ lúc nào.</li>
                            </ul>
                            <h4>Giải Nhì</h4>
                            <ul>
                                <li>1 tháng Discord Nitro Basic (không có boost và một số đặc quyền nhất định)</li>
                                <li>1.000.000 Cowoncy</li>
                                <li>Vai trò <b>Á Quân Event Lần 3</b> trên máy chủ NobiRE Community</li>
                                <li>1 vai trò riêng vô thời hạn với tên và màu tùy chỉnh do bạn tự chọn. Có thể thay đổi bất cứ lúc nào.</li>
                            </ul>
                            <h4>Giải Ba</h4>
                            <p>500.000 Cowoncy. Cảm ơn bạn đã có tinh thần tham gia sự kiện này!</p>
                            <h3>Quy định của sự kiện</h3>
                            <h4>Về cách thức tham gia</h4>
                            <ul>
                                <li>Bạn xác nhận là bạn sẽ có mặt đúng giờ khi sự kiện bắt đầu và có một tinh thần thật tốt khi tham gia. Nếu bạn không có mặt đúng giờ và gây ảnh hưởng đến tinh thần của những người khác, bạn có thể bị nhắc nhở hoặc xử phạt tùy theo mức độ vi phạm.</li>
                                <li>Bạn đảm bảo thiết bị của bạn sẽ có khả năng chạy game trong suốt quá trình tham gia sự kiện. Bạn có thể chơi thử trước bằng cách nhấn vào nút Thi Thử ở đầu website này.</li>
                                <li>Game sẽ được chơi trực tiếp trên trang web này. Khi sự kiện bắt đầu, bạn sẽ nhận được một thông báo trên Discord, và nút để bạn bắt đầu chơi cũng sẽ được mở trên trang chính của trang web. Bản game nội bộ được tải về thiết bị sẽ không được hỗ trợ.</li>
                                <li>Trong suốt quá trình chơi, bạn phải chia sẻ màn hình (screen share) thiết bị đang chơi (máy tính hoặc điện thoại) trên kênh được chỉ định trong máy chủ Discord.</li>
                            </ul>
                            <h4>Về quá trình tham gia</h4>
                            <ul>
                                <li>Thời gian bắt đầu, kết thúc và dữ liệu chơi sẽ được hệ thống tự động ghi nhận trong quá trình tham gia.</li>
                                <li>Khi đến thời gian xét thứ hạng, hệ thống sẽ tự động xếp hạng dựa trên dữ liệu đã ghi. Không có tác động thủ công từ BTC trừ khi có khiếu nại hợp lệ.</li>
                                <li>Trong trường hợp phát hiện vi phạm, BTC có quyền tạm dừng hoặc hủy lượt chơi từ xa thông qua hệ thống.</li>
                                <li>Người chơi bắt buộc phải <b>stream toàn bộ màn hình chơi</b> trong suốt quá trình speedrun.</li>
                                <li>Nếu màn hình không hiển thị rõ ràng, bị gián đoạn hoặc bị che khuất, BTC có quyền tạm dừng cho đến khi màn hình được hiển thị trở lại. Nếu tình hình quá xấu, BTC có thể yêu cầu chơi lại hoặc hủy lượt chơi.</li>
                            </ul>
                            <h4>Quy chế thi</h4>
                            <ul>
                                <li>Người chơi không được phép sử dụng phần mềm hỗ trợ gian lận như auto-click, macro, tool cheat hay giả lập không được cho phép.</li>
                                <li>Nếu phát hiện bug hoặc lỗi hệ thống, người chơi cần có trách nhiệm báo cáo ngay với BTC. Nếu cố tình khai thác lỗi để đạt lợi thế, BTC có quyền huỷ kết quả và cấm tham gia vĩnh viễn.</li>
                                <li>Trong một số trường hợp sự cố bất khả kháng (mất mạng, server lag, crash…), BTC có quyền cho phép người chơi thực hiện lại lượt chơi, hoặc hủy kết quả nếu dữ liệu không thể phục hồi.</li>
                                <li>Mọi hành động bất thường trong dữ liệu hệ thống (như thời gian bất hợp lý, số lần chơi vượt giới hạn, v.v.) sẽ được kiểm tra lại thủ công, và kết quả có thể bị tạm hoãn hoặc huỷ nếu không xác minh được tính hợp lệ.</li>
                                <li>Không được chia sẻ tài khoản hoặc dùng tài khoản người khác để chơi thay. Mỗi người chỉ được phép dùng một tài khoản duy nhất, và một thiết bị duy nhất đã đăng ký trước đó để tham gia sự kiện.</li>
                                <li>Trong suốt lượt chơi, cửa sổ game phải luôn hiển thị ở vị trí chính (foreground). Không được phép thu nhỏ game (minimize), chuyển sang cửa sổ khác, hay giấu cửa sổ game ra sau ứng dụng khác.</li>
                                <li>Việc sử dụng nhiều màn hình (multi-monitor) là không bị cấm, nhưng người chơi phải đảm bảo game vẫn được hiển thị rõ ràng trong stream, và không dùng màn hình phụ để hỗ trợ bất kỳ hình thức gian lận nào.</li>
                                <li>Stream phải bao phủ toàn bộ màn hình đang chơi, không được crop chỉ phần game (trừ khi được BTC cho phép trước). Nếu phát hiện thao tác bất thường ngoài khung stream, BTC có quyền yêu cầu cung cấp thêm bằng chứng hoặc huỷ kết quả.</li>
                                <li>Việc liên tục chuyển cửa sổ hoặc có hành vi đáng ngờ (như alt-tab liên tục, chuyển task nhanh chóng) sẽ bị hệ thống ghi nhận và có thể bị xem xét kỹ hơn.</li>
                                <li>BTC khuyến khích người chơi đóng các ứng dụng không cần thiết trong quá trình chơi để tránh ảnh hưởng hiệu năng, đồng thời tăng tính minh bạch.</li>
                            </ul>
                            <h4>Về phần thưởng</h4>
                            <ul>
                                <li>Phần thưởng sẽ không thể quy đổi thành tiền mặt.</li>
                                <li>BTC có quyền thay đổi, hoãn hoặc huỷ phần thưởng nếu phát hiện gian lận.</li>
                                <li>Trong trường hợp có khiếu nại, BTC sẽ kiểm tra dữ liệu hệ thống và stream để đưa ra quyết định cuối cùng.</li>
                                <li>Quyết định xét hạng và trao thưởng cuối cùng sẽ thuộc về BTC.</li>
                            </ul>
                            <h4>Về cách ứng xử</h4>
                            <ul>
                                <li>Giữ thái độ lịch sự, tôn trọng BTC và người chơi khác.</li>
                                <li>Không spam, gây rối, hoặc dùng từ ngữ kích động trong thời gian diễn ra sự kiện.</li>
                                <li>Nếu bị phát hiện cố tình vi phạm, người chơi sẽ bị loại khỏi sự kiện và có thể bị cấm tham gia các sự kiện sau.</li>
                            </ul>
                            <p><i>Các thông tin khác vẫn sẽ được cập nhật thêm trong tương lai. Cảm ơn các bạn đã quan tâm tới sự kiện!</i></p>
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
    <script>let endingTime = <?php echo $ending_time ?></script>
    <script src="./time_counter.js?v=<?=time()?>"></script>
    <script src="./speedrun.js?v=<?=time()?>"></script>

</body>

</html>