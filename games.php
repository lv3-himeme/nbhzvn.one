<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
require "api/games/functions.php";
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
$parsedown->setMarkupEscaped(true);

if (is_numeric(get("id"))) {
    $game = new Nbhzvn_Game(intval(get("id")));
    if (!$game->id) redirect_to_home();
    if (!$game->approved && $game->uploader != $user->id && $user->type < 3) redirect_to_home();
    $game->add_views();
    $comments = $game->comments();
    $follows = $game->follows();
    $ratings = $game->ratings();
    $rated = ($user && $user->id) ? $game->check_rating($user->id) : false;
}
else if (get("category")) {
    switch (get("category")) {
        case "trending": {
            $title = "Game Thịnh Hành";
            $repo = trending_games();
            break;
        }
        case "popular": {
            $title = "Game Phổ Biến";
            $repo = popular_games();
            break;
        }
        case "recent": {
            $title = "Game Mới Tải Lên";
            $repo = recent_games();
            break;
        }
        case "mobile": {
            $title = "Game Dành Cho Điện Thoại";
            $repo = mobile_games();
            break;
        }
        default: {
            $repo = all_games();
            break;
        }
    }
}
else $repo = all_games(20);
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php require "head.php" ?>
</head>

<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

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
                        <span>Thông Tin Game</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Anime Section Begin -->
    <section class="anime-details spad">
        <div class="container">
            <?php if ($game && $game->id): ?>
            <div class="anime__details__content">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="anime__details__pic set-bg" data-setbg="/uploads/<?php echo $game->image ?>">
                            <div class="comment"><i class="fa fa-comments"></i> <?php echo count($comments) ?></div>
                            <div class="view"><i class="fa fa-eye"></i> <?php echo number_format($game->views, 0, ",", ".") ?></div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="anime__details__text">
                            <div class="anime__details__title">
                                <h3><?php echo $game->name ?></h3>
                            </div>
                            <div class="anime__details__rating">
                                <div class="rating" id="rating">
                                    <?php
                                        $average = $ratings->average;
                                        $full = floor($average); $remain = $average - $full; $index = 0; $ostar = 4 - $full;
                                        for ($i = 0; $i < $full; $i++) {
                                            $index++;
                                            echo '<a href="javascript:void(0)" onclick="rate(' . $game->id . ', ' . $index . ')"><i ' . ($rated ? 'data-rated="true"' : '') . ' id="star-' . $index . '" class="fa fa-star"></i></a> ';
                                        }
                                        if ($index < 5) {
                                            $index++;
                                            echo '<a href="javascript:void(0)" onclick="rate(' . $game->id . ', ' . $index . ')"><i ' . ($rated ? 'data-rated="true"' : '') . ' id="star-' . $index . '" class="fa fa-star' . (($remain >= 0.5) ? '-half' : '') . '-o"></i></a> ';
                                        }
                                        for ($i = 0; $i < $ostar; $i++) {
                                            $index++;
                                            echo '<a href="javascript:void(0)" onclick="rate(' . $game->id . ', ' . $index . ')"><i ' . ($rated ? 'data-rated="true"' : '') . ' id="star-' . $index . '" class="fa fa-star-o"></i></a> ';
                                        }
                                    ?>
                                </div>
                                <span id="ratingText"><?php echo number_format($ratings->total, 0, ",", ".") ?> lượt đánh giá</span>
                            </div>
                            <div class="anime__details__widget">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6">
                                        <ul>
                                            <li><span>Phần mềm làm game:</span> <a href="/search?engine=<?php echo $game->engine ?>"><?php echo $engine_vocab[$game->engine] ?></a></li>
                                            <li><span>Năm ra mắt:</span> <a href="/search?release_year=<?php echo $game->release_year ?>"><?php echo $game->release_year ?></a></li>
                                            <li><span>Nhà phát triển:</span> <a href="/search?author=<?php echo urlencode($game->author) ?>"><?php echo $game->author ?></a></li>
                                            <?php if ($game->translator): ?>
                                            <li><span>Dịch giả:</span> <a href="/search?translator=<?php echo urlencode($game->translator) ?>"><?php echo $game->translator ?></a></li>
                                            <?php endif ?>
                                            <li><span>Trạng thái:</span> <a href="/search?status=<?php echo $game->status ?>"><?php echo $status_vocab[$game->status] ?></a></li>
                                        </ul>
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <ul>
                                            <li><span>Người tải lên:</span> <a href="/profile/<?php echo $game->uploader ?>"><?php $uploader = new Nbhzvn_User($game->uploader); echo $uploader->display_name ? $uploader->display_name : $uploader->username ?></a></li>
                                            <li><span>Ngôn ngữ:</span> <a href="/search?language=<?php echo $game->language ?>"><?php echo $language_vocab[$game->language] ?></a></li>
                                            <li><span>Hỗ trợ:</span> <?php
                                                $oses = explode(",", $game->supported_os); $elements = [];
                                                foreach ($oses as $os) array_push($elements, '<a href="/search?supported_os=' . $os . '">' . $os_vocab[$os] . '</a>');
                                                echo implode(", ", $elements);
                                            ?></li>
                                            <?php if ($game->tags): ?>
                                            <li><span>Thẻ:</span> <?php
                                                $tags = explode(",", $game->tags); $elements = [];
                                                foreach ($tags as $tag) array_push($elements, '<a href="/search?tag=' . $tag . '">' . $tag . '</a>');
                                                echo implode(", ", $elements);
                                            ?></li>
                                            <?php endif ?>
                                            <li><span>Lượt tải xuống:</span> <?php echo number_format($game->downloads, 0, ",", ".") ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="anime__details__btn">
                                <a href="#downloadSection" class="download-btn"><i class="fa fa-download"></i>&nbsp;&nbsp;Tải xuống</a>
                                <?php if ($game->approved): ?>
                                <a href="javascript:void(0)" onclick="toggleFollow(<?php echo $game->id ?>)" class="follow-btn"><span id="followText"><?php echo ($user && $user->id && $game->check_follow($user->id)) ? "Bỏ theo dõi" : "Theo dõi" ?></span> <label id="followCount"><?php echo number_format($follows, 0, ",", ".") ?></label></a>
                                <?php elseif ($user->type == 3): ?>
                                <a href="/approve/<?php echo $game->id ?>" class="download-btn"><i class="fa fa-check"></i>&nbsp;&nbsp;Phê duyệt</a>
                                <?php endif ?>
                                <?php if ($user->id == $game->uploader): ?>
                                <a href="/edit_game/<?php echo $game->id ?>" class="download-btn" style="margin-left: 12px"><i class="fa fa-pencil"></i>&nbsp;&nbsp;Chỉnh sửa</a>
                                <?php endif ?>
                                <?php if ($user->type == 3): ?>
                                <a href="/feature/<?php echo $game->id ?>" class="download-btn"><?php if (!$game->is_featured) echo 'Thêm vào'; else echo "Loại bỏ khỏi" ?> mục Tiêu Điểm</a>
                                <?php endif ?>
                                <?php if ($user->id == $game->uploader || $user->type == 3): ?>
                                <a href="/delete_game/<?php echo $game->id ?>" class="download-btn"><i class="fa fa-trash"></i>&nbsp;&nbsp;Xoá</a>
                                <?php endif ?>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8 col-md-8">
                        <div>
                            <div class="section-title">
                                <h5>Mô tả game</h5>
                            </div>
                            <?php echo $parsedown->text($game->description) ?>
                        </div><br>
                        <div>
                            <div class="section-title">
                                <h5>Ảnh chụp màn hình</h5>
                            </div>
                            <div class="game_screenshots_container">
                                <?php
                                    foreach ($game->screenshots as $screenshot) {
                                        $path = "/uploads/" . $screenshot;
                                        echo '<a href="' . $path . '" target="_blank"><img src="' . $path . '" /></a>';
                                    }
                                ?>
                            </div>
                        </div><br>
                        <div id="downloadSection">
                            <div class="section-title">
                                <h5>Tải game xuống</h5>
                            </div>
                            <?php
                                $index = 0;
                                foreach ($game->links as $link) {
                                    $index++;
                                    $path = "./uploads/" . $link->path;
                                    echo '
                                        <div class="game_file">
                                            <div class="row">
                                                <div class="col-sm-9 game_file_name"><a href="/download/' . $game->id . '/' . $index . '">' . $link->name . '</a></div>
                                                <div class="col-sm-3 game_file_size">' . bytes_to_string(filesize($path)) .  '</div>
                                            </div>
                                        </div>
                                    ';
                                }
                            ?>
                        </div><br>
                        <div class="anime__details__review">
                            <div class="section-title">
                                <h5>Bình luận (<?php echo count($comments) ?>)</h5>
                            </div>
                            <div id="comments">
                                <?php
                                    $highlighted_comment_id = is_numeric(get("highlighted_comment")) ? intval(get("highlighted_comment")) : 0;
                                    if ($highlighted_comment_id) {
                                        $highlighted_comment = new Nbhzvn_Comment($highlighted_comment_id);
                                        if ($highlighted_comment->id) echo $highlighted_comment->to_html(!!$highlighted_comment->replied_to, $user, false, is_numeric(get("reply_comment")) ? intval(get("reply_comment")) : $highlighted_comment_id);
                                    }
                                    foreach ($comments as $comment) if ($comment->id != $highlighted_comment_id) echo $comment->to_html(!!$comment->replied_to, $user);
                                ?>
                            </div>
                            <?php echo pagination(count($comments)); ?>
                        </div>
                        <div class="anime__details__form">
                            <div class="section-title">
                                <h5>Viết bình luận mới</h5>
                            </div>
                            <textarea id="commentContent" placeholder="Nội dung bình luận" required></textarea>
                            <button id="commentBtn" onclick="comment()"><i class="fa fa-location-arrow"></i> Bình luận</button>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="anime__details__sidebar">
                            <div class="section-title">
                                <h5>Các game khác</h5>
                            </div>
                            <?php
                                foreach (random_games($game->id) as $tmp_game) echo echo_tiled_game($tmp_game);
                            ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <h3 class="nbhzvn_title"><?php echo $title ? $title : "Danh Sách Game" ?></h3>
                <div class="row" id="games">
                    <?php
                        $limit = 0;
                        foreach ($repo as $tmp_game) {
                            echo echo_search_game($tmp_game, true);
                            $limit++;
                            if ($limit == 20) break;
                        }
                    ?>
                </div>
                <?php echo pagination(count($repo)) ?>
                <?php endif ?>
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
        <?php if ($game && $game->id): ?>
        <script>gameId = <?php echo $game->id ?></script>
        <script src="/js/game.js?time=<?php echo time() ?>"></script>
        <?php else: ?>
        <script>repo = "<?php echo addslashes(get("category")) ?>"</script>
        <script src="/js/game_list.js?time=<?php echo time() ?>"></script>
        <?php endif ?>

    </body>

    </html>