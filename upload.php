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
    $inputs = ["name", "image", "links", "beta_links", "beta_users", "screenshots", "description", "engine", "release_year", "author", "language", "status", "supported_os"];
    $data = array();
    foreach ($inputs as $input) {
        if (!post($input)) return $error = "Vui lòng nhập đầy đủ thông tin.";
        $data[$input] = post($input);
    }
    $data["links"] = base64_decode($data["links"]);
    $data["beta_links"] = base64_decode($data["beta_links"]);
    $data["beta_users"] = base64_decode($data["beta_users"]);
    $data["screenshots"] = base64_decode($data["screenshots"]);
    $links = json_decode($data["links"]); $beta_links = json_decode($data["beta_links"]);
    if (count($links) < 1 && count($beta_links) < 1) return $error = "Vui lòng tải ít nhất một tệp tin game lên.";
    foreach ($links as $link) {
        if (!$link || !$link->path) return $error = "Có ít nhất một tệp tin bị lỗi trong quá trình tải lên, vui lòng kiểm tra lại các tệp tin đã tải lên và thử lại.";
    }
    foreach ($beta_links as $beta_link) {
        if (!$beta_link || !$beta_link->path) return $error = "Có ít nhất một tệp tin bị lỗi trong quá trình tải lên, vui lòng kiểm tra lại các tệp tin đã tải lên và thử lại.";
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
        window.localStorage.removeItem("nbhzvn_upload_autosave");
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
                        <input type="name" name="name" placeholder="Tên Game" required value="<?php echo base64_encode(post("name")) ?>">
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
                    <div style="border: 1px solid #aaa; border-radius: 5px; padding: 15px">
                        <ul class="nav nav-tabs mb-3" id="tabList" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="gameFilesTab" data-toggle="pill" data-target="#gameFilesTabContent" type="button" role="tab" aria-controls="gameFilesTabContent" aria-selected="true">Chính Thức</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="betaGameFilesTab" data-toggle="pill" data-target="#betaGameFilesTabContent" type="button" role="tab" aria-controls="betaGameFilesTabContent" aria-selected="false">Thử Nghiệm (Beta)</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="tabContent">
                            <div class="tab-pane fade show active" id="gameFilesTabContent" role="tabpanel" aria-labelledby="gameFilesTab">
                                <p style="text-align: right">
                                    <button type="button" onclick="addGameFile()" class="nbhzvn_btn"><span class="icon_plus"></span>&nbsp;&nbsp;<span>Thêm tệp tin</span></button>
                                </p>
                                <div id="gameFiles"></div>
                            </div>
                            <div class="tab-pane fade" id="betaGameFilesTabContent" role="tabpanel" aria-labelledby="betaGameFilesTab">
                                <p>Bạn có bản beta của game mà chỉ muốn cho một số thành viên nhất định tải xuống? Bạn có thể thêm nó vào đây!</p>
                                <p style="text-align: right">
                                    <button type="button" onclick="addBetaGameFile()" class="nbhzvn_btn"><span class="icon_plus"></span>&nbsp;&nbsp;<span>Thêm tệp tin</span></button>
                                </p>
                                <div id="betaGameFiles"></div><br>
                                <p style="font-size: 16pt"><b>Danh Sách Tester</b></p>
                                <p style="text-align: right">
                                    <button type="button" onclick="addBetaUser()" class="nbhzvn_btn"><span class="icon_plus"></span>&nbsp;&nbsp;<span>Thêm Tester</span></button>
                                </p>
                                <div id="betaUsers"></div>
                            </div>
                        </div>
                    </div><br>
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
                    <input type="hidden" name="links" value='<?php echo post("links") ?>' id="linksInput" />
                    <input type="hidden" name="beta_links" value='<?php echo post("beta_links") ?>' id="betaLinksInput" />
                    <input type="hidden" name="beta_users" value='<?php echo post("beta_users") ?>' id="betaUsersInput" />
                    <input type="hidden" name="screenshots" value='<?php echo post("screenshots") ?>' id="screenshotsInput" />
                    <input type="hidden" name="supported_os" value='<?php echo post("supported_os") ?>' id="supportedOSInput" />
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="submit" class="site-btn" value="Submit">Thêm Game Mới</button>
                    <button type="button" class="site-btn" style="background-color: #666" onclick="AutoSave.delete()" id="deleteDraftBtn">Xoá bản nháp</button>
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
    <script src="/js/main.js?v=<?=$res_version?>"></script>
    <script src="/js/toastr.js"></script>
    <script src="/js/api.js?v=<?=$res_version?>"></script>
    <script src="/js/modal.js?v=<?=$res_version?>"></script>
    <script src="/js/uploader.js?v=<?=$res_version?>"></script>

</body>

</html>