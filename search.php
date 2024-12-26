<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
require "api/games/functions.php";

$error = "";
try {
    $supported_queries = ["name", "engine", "tags", "release_year", "author", "language", "translator", "status", "views", "downloads", "supported_os"]; $queries = array();
    foreach ($supported_queries as $query) {
        if (get($query)) $queries[$query] = get($query);
    }
    if (count($queries)) {
        $search_result = search_games($queries);
        if (!$search_result || !count($search_result)) $not_found = true;
    }
}
catch (Exception $ex) {
    switch ($ex->getMessage()) {
        default: {
            $error = "Có lỗi không xác định xảy ra. Vui lòng báo cáo cho nhà phát triển của website.";
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Tìm Kiếm";
        require "head.php";
    ?>
</head>

<body>
    <!-- Header Section Begin -->
    <header class="header">
        <?php require "header.php" ?>
        <link rel="stylesheet" href="/css/toastr.css" />
    </header>
    <!-- Header End -->

    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="/"><i class="fa fa-home"></i> Trang Chủ</a>
                        <span>Tìm Kiếm</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Anime Section Begin -->
    <section class="anime-details spad">
        <div class="container">
            <div class="login__form page">
                <h3>Tìm Kiếm Game</h3>
                <form action="" method="GET">
                    <div class="input__item" style="width: 100%">
                        <input type="name" name="name" placeholder="Tên Game" value="<?php echo get("name") ?>">
                        <span class="icon_document"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <select name="engine" placeholder="Phần Mềm Làm Game">
                            <option value="0">Phần Mềm Làm Game</option>
                            <?php
                                foreach ($engine_vocab as $value => $vocab) {
                                    echo '<option value="' . $value . '"' . ((intval(get("engine")) == $value) ? " selected" : "") . '>' . $vocab . '</option>';
                                }
                            ?>
                        </select>
                        <span class="icon_tool"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="tags" placeholder="Thẻ" value="<?php echo get("tags") ?>">
                        <span class="icon_tag"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <input type="number" name="release_year" placeholder="Năm Phát Hành" value="<?php echo get("release_year") ?>">
                        <span class="icon_calendar"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="author" placeholder="Tác Giả Gốc" value="<?php echo get("author") ?>">
                        <span class="icon_profile"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <select name="language" placeholder="Ngôn Ngữ">
                            <option value="0">Ngôn Ngữ</option>
                            <?php
                                foreach ($language_vocab as $value => $vocab) {
                                    echo '<option value="' . $value . '"' . ((intval(get("language")) == $value) ? " selected" : "") . '>' . $vocab . '</option>';
                                }
                            ?>
                        </select>
                        <span class="icon_globe-2"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="translator" placeholder="Dịch Giả" value="<?php echo get("translator") ?>">
                        <span class="icon_profile"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <select name="status" placeholder="Trạng Thái">
                            <option value="0">Trạng Thái</option>
                            <?php
                                foreach ($status_vocab as $value => $vocab) {
                                    echo '<option value="' . $value . '"' . ((intval(get("status")) == $value) ? " selected" : "") . '>' . $vocab . '</option>';
                                }
                            ?>
                        </select>
                        <span class="icon_datareport_alt"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <select name="status" placeholder="Nền Tảng Được Hỗ Trợ">
                            <option value="0">Nền Tảng Được Hỗ Trợ</option>
                            <?php
                                foreach ($os_vocab as $value => $vocab) {
                                    echo '<option value="' . $value . '"' . ((get("supported_os") == $value) ? " selected" : "") . '>' . $vocab . '</option>';
                                }
                            ?>
                        </select>
                        <span class="icon_phone"></span>
                    </div>
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" class="site-btn">Tìm Kiếm</button>
                </form><br>
                <?php if ($search_result): ?>
                    <h3 class="nbhzvn_title">Kết Quả Tìm Kiếm</h3>
                <?php endif ?>
                <?php if ($not_found) echo '<p>Không có kết quả nào được tìm thấy. Thử tìm với một tiêu chí khác.</p>' ?>
                <div class="row" id="games">
                    <?php
                        if ($search_result) {
                            if (count($search_result)) {
                                $limit = 0;
                                foreach ($search_result as $tmp_game) {
                                    echo echo_search_game($tmp_game, true);
                                    $limit++;
                                    if ($limit == 20) break;
                                }
                            }
                        }
                    ?>
                </div>
                <?php if($search_result) echo pagination(count($search_result)) ?>
            </div>
            </div>
        </section>
        <!-- Anime Section End -->

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
        <script src="/js/search.js"></script>

    </body>

    </html>