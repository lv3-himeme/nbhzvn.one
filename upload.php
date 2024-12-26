<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
require "api/games/functions.php";
require "api/discord/webhook.php";
if (!$user || $user->type < 2) redirect_to_home();

$error = "";
$notice = "";

function process() {
    global $error;
    global $notice;
    global $user;
    global $conn;
    if (!check_csrf(post("csrf_token"))) return $error = "Mã xác thực CSRF không đúng.";
    if ($user->check_timeout("upload") && $user->type < 3) return $error = "Bạn cần đợi ít nhất 10 phút từ lần thêm cuối cùng trước khi thêm một game mới.";
    $inputs = ["name", "image", "links", "screenshots", "description", "engine", "release_year", "author", "language", "status", "supported_os"];
    $data = array();
    foreach ($inputs as $input) {
        if (!post($input)) return $error = "Vui lòng nhập đầy đủ thông tin.";
        $data[$input] = post($input);
    }
    $links = json_decode($data["links"]);
    if (count($links) < 1) return $error = "Vui lòng tải ít nhất một tệp tin game lên.";
    foreach ($links as $link) {
        if (!$link || !$link->path) return $error = "Có ít nhất một tệp tin bị lỗi trong quá trình tải lên, vui lòng kiểm tra lại các tệp tin đã tải lên và thử lại.";
    }
    $screenshots = json_decode($data["screenshots"]);
    if (count($screenshots) < 1) return $error = "Vui lòng tải ít nhất một ảnh chụp màn hình lên.";
    foreach ($screenshots as $screenshot) {
        if (!$screenshot) return $error = "Có ít nhất một ảnh chụp màn hình bị lỗi trong quá trình tải lên, vui lòng kiểm tra lại các ảnh chụp màn hình đã tải lên và thử lại.";
    }
    $data["tags"] = post("tags");
    $data["translator"] = post("translator");
    $data["uploader"] = $user->id;
    $data = json_decode(json_encode($data));
    $result = add_game($data, ($user->type == 3));
    $game_id = $conn->insert_id;
    $user->update_timeout("upload", time() + 600);
    if ($user->type < 3) {
        $admins = get_admins();
        foreach ($admins as $admin) $admin->send_notification("/games/" . $game_id, "Game **" . post("name") . "** vừa mới được tải lên và cần các Quản Trị Viên phê duyệt.");
        send_moderation_webhook(new Nbhzvn_Game($game_id));
    }
    else send_newgame_webhook(new Nbhzvn_Game($game_id));
    if ($result == SUCCESS) $notice = "Đã tải lên game thành công" . ($user->type < 3 ? ", game của bạn sẽ được hiển thị trên trang web sau khi Quản Trị Viên đã duyệt game của bạn." : ".");
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
        document.location.href = "/";
    </script>
    <p>' . $notice . ' <a href="/">Tiếp tục</a></p>
');
refresh_csrf();
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Thêm Game Mới";
        require __DIR__ . "/head.php";
    ?>
    <link rel="stylesheet" href="/css/toastr.css" />
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
            <div class="login__form page">
                <h3>Thêm Game Mới</h3>
                <form action="" method="POST" onsubmit="return processSubmit()">
                    <p style="font-size: 16pt"><b>Tên Game</b></p>
                    <div class="input__item" style="width: 100%">
                        <input type="name" name="name" placeholder="Tên Game" required value="<?php echo post("name") ?>">
                        <span class="icon_document"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Ảnh Đại Diện</b></p>
                    <div class="input__item" style="width: 100%">
                        <input readonly class="readonly" name="image" placeholder="Nhấn vào đây để tải ảnh đại diện" required onclick="uploadThumbnail()" id="thumbnail" value="<?php echo post("image") ?>">
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
                    <div id="screenshots" class="upload_screenshots"></div><br>
                    <p style="font-size: 16pt"><b>Mô Tả</b></p>
                    <div class="input__item input__item__textarea" style="width: 100%">
                        <textarea name="description" placeholder="Mô tả có hỗ trợ Markdown." required><?php echo post("description") ?></textarea>
                        <span class="icon_pencil"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Phần Mềm Làm Game</b></p>
                    <div class="input__item" style="width: 100%">
                        <select name="engine" required placeholder="Phần Mềm Làm Game">
                            <?php
                                foreach ($engine_vocab as $value => $vocab) {
                                    echo '<option value="' . $value . '"' . ((intval(post("engine")) == $value) ? " selected" : "") . '>' . $vocab . '</option>';
                                }
                            ?>
                        </select>
                        <span class="icon_tool"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Thẻ</b></p>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="tags" placeholder="Các thẻ cách nhau bằng dấu phẩy viết liền. Không bắt buộc." value="<?php echo post("tags") ?>">
                        <span class="icon_tag"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Năm Phát Hành</b></p>
                    <div class="input__item" style="width: 100%">
                        <input type="number" name="release_year" placeholder="Năm Phát Hành" value="<?php echo post("release_year") ?>">
                        <span class="icon_calendar"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Tác Giả Gốc</b></p>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="author" placeholder="Nhà phát triển của game gốc (chưa được dịch). Nếu có nhiều tác giả thì bạn có thể tách bằng dấu phẩy viết liền." required value="<?php echo post("author") ?>">
                        <span class="icon_profile"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Ngôn Ngữ</b></p>
                    <div class="input__item" style="width: 100%">
                        <select name="language" required placeholder="Ngôn Ngữ">
                            <?php
                                foreach ($language_vocab as $value => $vocab) {
                                    echo '<option value="' . $value . '"' . ((intval(post("language")) == $value) ? " selected" : "") . '>' . $vocab . '</option>';
                                }
                            ?>
                        </select>
                        <span class="icon_globe-2"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Dịch Giả</b></p>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="translator" placeholder="Bỏ trống nếu game chưa được dịch sang ngôn ngữ nào khác.. Nếu có nhiều dịch giả thì bạn có thể tách bằng dấu phẩy viết liền." value="<?php echo post("translator") ?>">
                        <span class="icon_profile"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Trạng Thái</b></p>
                    <div class="input__item" style="width: 100%">
                        <select name="status" required placeholder="Trạng Thái">
                            <?php
                                foreach ($status_vocab as $value => $vocab) {
                                    echo '<option value="' . $value . '"' . ((intval(post("status")) == $value) ? " selected" : "") . '>' . $vocab . '</option>';
                                }
                            ?>
                        </select>
                        <span class="icon_datareport_alt"></span>
                    </div>
                    <p style="font-size: 16pt"><b>Nền Tảng Được Hỗ Trợ</b></p>
                        <?php
                            $supported_oses = explode(",", post("supported_os"));
                            foreach ($os_vocab as $value => $vocab) {
                                echo '<div><input type="checkbox" class="supported_os_checkbox" value="' . $value . '"' . (in_array($value, $supported_oses) ? " checked" : "") . '> <label style="color: #fff; margin-left: 10px">' . $vocab . '</label></input></div>';
                            }
                        ?>
                    <input type="hidden" name="links" value='<?php echo str_ireplace("'", "\\'", post("links")) ?>' id="linksInput" />
                    <input type="hidden" name="screenshots" value='<?php echo str_ireplace("'", "\\'", post("screenshots")) ?>' id="screenshotsInput" />
                    <input type="hidden" name="supported_os" value='<?php echo str_ireplace("'", "\\'", post("supported_os")) ?>' id="supportedOSInput" />
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="submit" class="site-btn" value="Submit">Thêm Game Mới</button>
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
    <script src="/js/uploader.js"></script>

</body>

</html>