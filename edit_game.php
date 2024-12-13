<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
require "api/games/functions.php";
if (!$user || $user->type < 2 || !get("id")) redirect_to_home();

$game = new Nbhzvn_Game(intval(get("id")));
if (!$game->id || $game->uploader != $user->id) redirect_to_home();

$error = "";
$notice = "";

function clean_files($thumbnail = "none", $links = [], $screenshots = []) {
    $new_thumbnail = post("image"); $new_links = json_decode(post("links")); $new_screenshots = json_decode(post("screenshots"));
    if ($new_thumbnail != $thumbnail) unlink("./uploads/" . $thumbnail);
    foreach ($links as $link) {
        $path = $link->path;
        if (!in_array($link->path, array_map(function($v) {
            return $v->path;
        }, $new_links))) unlink("./uploads/" . $path);
    }
    foreach ($screenshots as $screenshot) {
        if (!in_array($screenshot, $new_screenshots)) unlink("./uploads/" . $screenshot);
    }
}

function process() {
    global $error;
    global $notice;
    global $user;
    global $game;
    if (!check_csrf(post("csrf_token"))) return $error = "Mã xác thực CSRF không đúng.";
    $inputs = ["name", "image", "links", "screenshots", "description", "engine", "release_year", "author", "language", "status", "supported_os"];
    $data = array();
    foreach ($inputs as $input) {
        if (!post($input)) return $error = "Vui lòng nhập đầy đủ thông tin.";
        $data[$input] = post($input);
    }
    if (post("tags")) $data["tags"] = post("tags");
    if (post("translator")) $data["translator"] = post("translator");
    $thumbnail = $game->image; $links = $game->links; $screenshots = $game->screenshots;
    $result = $game->edit($data);
    if ($result == SUCCESS) {
        clean_files($thumbnail, $links, $screenshots);
        foreach ($game->followers() as $follower) {
            if ($follower->id != $user->id) $follower->send_notification("/games/" . $game->id, "**" . ($user->display_name ? $user->display_name : $user->username) . "** vừa chỉnh sửa thông tin game **" . $game->name . "**.");
        }
        $notice = "Đã chỉnh sửa game thành công.";
    }
}

try {
    if (post("submit")) process();
}
catch (Exception $ex) {
    switch ($ex->getMessage()) {
        case MISSING_INFORMATION: {
            $error = "Vui lòng nhập đầy đủ thông tin.";
            break;
        }
        default: {
            $error = "Có lỗi không xác định xảy ra. Vui lòng báo cáo cho nhà phát triển của website.<br>" . htmlentities($ex->getMessage());
            break;
        }
    }
}
if ($notice) die('
    <script>
        alert("' . $notice . '");
        document.location.href = "/games/' . $game->id . '";
    </script>
    <p>' . $notice . ' <a href="/games/' . $game->id . '">Tiếp tục</a></p>
');
refresh_csrf();
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Chỉnh Sửa Thông Tin Game";
        require __DIR__ . "/head.php";
    ?>
    <link rel="stylesheet" href="/css/toastr.css" />
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
            <div class="login__form page">
                <h3>Chỉnh Sửa Thông Tin Game</h3>
                <form action="" method="POST" onsubmit="processSubmit()">
                    <p style="font-size: 16pt"><b>Tên Game</b></p>
                    <div class="input__item" style="width: 100%">
                        <input type="name" name="name" placeholder="Tên Game" required value="<?php echo $game->name ?>">
                        <span class="icon_document"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Ảnh Đại Diện</b></p>
                    <div class="input__item" style="width: 100%">
                        <input readonly class="readonly" name="image" placeholder="Nhấn vào đây để tải ảnh đại diện" required onclick="uploadThumbnail()" id="thumbnail" value="<?php echo $game->image ?>">
                        <span class="icon_image"></span>
                    </div>
                    <div><img class="thumbnail_image" id="thumbnailImage" /></div>
                    <div class="progressbar_container" id="thumbnailProgressBar">
                        <div class="progressbar"></div>
                    </div><br>
                    <p style="font-size: 16pt"><b>Danh Sách Tệp Tin Game</b></p>
                    <p style="text-align: right">
                        <button type="button" onclick="addGameFile()" class="nbhzvn_btn"><span class="icon_plus"></span>&nbsp;&nbsp;<span>Thêm tệp tin</span></button>
                    </p>
                    <div id="gameFiles"></div><br>
                    <p style="font-size: 16pt"><b>Ảnh Chụp Màn Hình Game</b></p>
                    <p style="text-align: right">
                        <button type="button" onclick="addScreenshot()" class="nbhzvn_btn"><span class="icon_plus"></span>&nbsp;&nbsp;<span>Thêm ảnh</span></button>
                    </p>
                    <div id="screenshots" class="upload_screenshots"></div>
                    <p style="font-size: 16pt"><b>Mô Tả</b></p>
                    <div class="input__item input__item__textarea" style="width: 100%">
                        <textarea name="description" placeholder="Mô tả có hỗ trợ Markdown." required><?php echo $game->description ?></textarea>
                        <span class="icon_pencil"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Phần Mềm Làm Game</b></p>
                    <div class="input__item" style="width: 100%">
                        <select name="engine" required placeholder="Phần Mềm Làm Game">
                            <?php
                                foreach ($engine_vocab as $value => $vocab) {
                                    echo '<option value="' . $value . '"' . ($game->engine == $value ? " selected": "") . '>' . $vocab . '</option>';
                                }
                            ?>
                        </select>
                        <span class="icon_tool"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Thẻ</b></p>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="tags" placeholder="Các thẻ cách nhau bằng dấu phẩy viết liền. Không bắt buộc." value="<?php echo $game->tags ?>">
                        <span class="icon_tag"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Năm Phát Hành</b></p>
                    <div class="input__item" style="width: 100%">
                        <input type="number" name="release_year" placeholder="Năm Phát Hành" value="<?php echo $game->release_year ?>">
                        <span class="icon_calendar"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Tác Giả Gốc</b></p>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="author" placeholder="Nhà phát triển của game gốc (chưa được dịch)." required value="<?php echo $game->author ?>">
                        <span class="icon_profile"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Ngôn Ngữ</b></p>
                    <div class="input__item" style="width: 100%">
                        <select name="language" required placeholder="Ngôn Ngữ">
                            <?php
                                foreach ($language_vocab as $value => $vocab) {
                                    echo '<option value="' . $value . '"' . ($game->language == $value ? " selected": "") . '>' . $vocab . '</option>';
                                }
                            ?>
                        </select>
                        <span class="icon_globe-2"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Dịch Giả</b></p>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="translator" placeholder="Bỏ trống nếu game chưa được dịch sang ngôn ngữ nào khác." value="<?php echo $game->translator ?>">
                        <span class="icon_profile"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Trạng Thái</b></p>
                    <div class="input__item" style="width: 100%">
                        <select name="status" required placeholder="Trạng Thái">
                            <?php
                                foreach ($status_vocab as $value => $vocab) {
                                    echo '<option value="' . $value . '"' . ($game->status == $value ? " selected": "") . '>' . $vocab . '</option>';
                                }
                            ?>
                        </select>
                        <span class="icon_datareport_alt"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Nền Tảng Được Hỗ Trợ</b></p>
                        <?php
                            $supported_oses = explode(",", $game->supported_os);
                            foreach ($os_vocab as $value => $vocab) {
                                echo '<div><input type="checkbox" class="supported_os_checkbox" value="' . $value . '"' . (in_array($value, $supported_oses) ? " checked" : "") . '> <label style="color: #fff; margin-left: 10px">' . $vocab . '</label></input></div>';
                            }
                        ?>
                    <input type="hidden" name="links" id="linksInput" value='<?php echo str_ireplace("'", "\\'", json_encode($game->links)) ?>' />
                    <input type="hidden" name="screenshots" id="screenshotsInput" value='<?php echo str_ireplace("'", "\\'", json_encode($game->screenshots)) ?>' />
                    <input type="hidden" name="supported_os" value="" id="supportedOSInput" />
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="submit" class="site-btn" value="Submit">Chỉnh Sửa</button>
                </form>
            </div>
        </div>
        <input type="file" id="thumbnailFile" class="hidden" accept=".jpg, .png, .jpeg, .webp|image/*" />
        <div id="files"></div>
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
    <script src="/js/mixitup.min.js"></script>
    <script src="/js/jquery.slicknav.js"></script>
    <script src="/js/owl.carousel.min.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/toastr.js"></script>
    <script src="/js/api.js"></script>
    <script src="/js/uploader.js?time=<?php echo time() ?>"></script>

</body>

</html>