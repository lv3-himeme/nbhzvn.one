<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
require "api/games/functions.php";
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
$parsedown->setMarkupEscaped(true);

if (!get("id")) redirect_to_home();

$game = new Nbhzvn_Game(intval(get("id")));
if (!$game->id) redirect_to_home();
$game->add_views();
$comments = $game->comments();
$follows = $game->follows();
$ratings = $game->ratings();
$rated = ($user && $user->id) ? $game->check_rating($user->id) : false;
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
                                            echo '<a href="#" onclick="rate(' . $game->id . ', ' . $index . ')"><i ' . ($rated ? 'data-rated="true"' : '') . ' id="star-' . $index . '" class="fa fa-star"></i></a> ';
                                        }
                                        if ($index < 5) {
                                            $index++;
                                            echo '<a href="#" onclick="rate(' . $game->id . ', ' . $index . ')"><i ' . ($rated ? 'data-rated="true"' : '') . ' id="star-' . $index . '" class="fa fa-star' . (($remain >= 0.5) ? '-half' : '') . '-o"></i></a> ';
                                        }
                                        for ($i = 0; $i < $ostar; $i++) {
                                            $index++;
                                            echo '<a href="#" onclick="rate(' . $game->id . ', ' . $index . ')"><i ' . ($rated ? 'data-rated="true"' : '') . ' id="star-' . $index . '" class="fa fa-star-o"></i></a> ';
                                        }
                                    ?>
                                </div>
                                <span id="ratingText"><?php echo number_format($ratings->count, 0, ",", ".") ?> lượt đánh giá</span>
                            </div>
                            <div class="anime__details__widget">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6">
                                        <ul>
                                            <li><span>Phần mềm làm game:</span> <a href="/search?engine=<?php echo $game->engine ?>"><?php echo $engine_vocab[$game->engine] ?></a></li>
                                            <li><span>Năm ra mắt:</span> <a href="/search?release_year=<?php echo $game->release_year ?>"><?php echo $game->release_year ?></a></li>
                                            <li><span>Nhà phát triển:</span> <a href="/search?author=<?php echo urlencode($game->author) ?>"><?php echo $game->author ?></a></li>
                                            <li><span>Dịch giả:</span> <a href="/search?translator=<?php echo urlencode($game->translator) ?>"><?php echo $game->translator ?></a></li>
                                            <li><span>Trạng thái:</span> <a href="/search?status=<?php echo $game->status ?>"><?php echo $status_vocab[$game->status] ?></a></li>
                                        </ul>
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <ul>
                                            <li><span>Người tải lên:</span> <a href="/profile/<?php echo $game->uploader ?>"><?php $uploader = new Nbhzvn_User($game->uploader); echo $uploader->display_name ? $uploader->display_name : $uploader->username ?></a></li>
                                            <li><span>Đánh giá trung bình:</span> <span id="ratingText2"><?php echo number_format($ratings->average, 1, ",", ".") ?> / <?php echo number_format($ratings->count, 0, ",", ".") ?> lượt đánh giá</span></li>
                                            <li><span>Hỗ trợ:</span> <?php
                                                $oses = explode(",", $game->supported_os); $elements = [];
                                                foreach ($oses as $os) array_push($elements, '<a href="/search?os=' . $os . '">' . $os_vocab[$os] . '</a>');
                                                echo implode(", ", $elements);
                                            ?></li>
                                            <li><span>Thẻ:</span> <?php
                                                $tags = explode(",", $game->tags); $elements = [];
                                                foreach ($tags as $tag) array_push($elements, '<a href="/search?tag=' . $tag . '">' . $tag . '</a>');
                                                echo implode(", ", $elements);
                                            ?></li>
                                            <li><span>Lượt tải xuống:</span> <?php echo number_format($game->downloads, 0, ",", ".") ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="anime__details__btn">
                                <a href="#downloadSection" class="download-btn"><i class="fa fa-download"></i>&nbsp;&nbsp;Tải xuống</a>
                                <a href="#" onclick="toggleFollow(<?php echo $game->id ?>)" class="follow-btn"><span id="followText"><?php echo ($user && $user->id && $game->check_follow($user->id)) ? "Bỏ theo dõi" : "Theo dõi" ?></span> <label id="followCount"><?php echo number_format($follows, 0, ",", ".") ?></label></a>
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
                                <h5>Reviews</h5>
                            </div>
                            <div class="anime__review__item">
                                <div class="anime__review__item__pic">
                                    <img src="/img/anime/review-1.jpg" alt="">
                                </div>
                                <div class="anime__review__item__text">
                                    <h6>Chris Curry - <span>1 Hour ago</span></h6>
                                    <p>whachikan Just noticed that someone categorized this as belonging to the genre
                                    "demons" LOL</p>
                                </div>
                            </div>
                            <div class="anime__review__item">
                                <div class="anime__review__item__pic">
                                    <img src="/img/anime/review-2.jpg" alt="">
                                </div>
                                <div class="anime__review__item__text">
                                    <h6>Lewis Mann - <span>5 Hour ago</span></h6>
                                    <p>Finally it came out ages ago</p>
                                </div>
                            </div>
                            <div class="anime__review__item">
                                <div class="anime__review__item__pic">
                                    <img src="/img/anime/review-3.jpg" alt="">
                                </div>
                                <div class="anime__review__item__text">
                                    <h6>Louis Tyler - <span>20 Hour ago</span></h6>
                                    <p>Where is the episode 15 ? Slow update! Tch</p>
                                </div>
                            </div>
                            <div class="anime__review__item">
                                <div class="anime__review__item__pic">
                                    <img src="/img/anime/review-4.jpg" alt="">
                                </div>
                                <div class="anime__review__item__text">
                                    <h6>Chris Curry - <span>1 Hour ago</span></h6>
                                    <p>whachikan Just noticed that someone categorized this as belonging to the genre
                                    "demons" LOL</p>
                                </div>
                            </div>
                            <div class="anime__review__item">
                                <div class="anime__review__item__pic">
                                    <img src="/img/anime/review-5.jpg" alt="">
                                </div>
                                <div class="anime__review__item__text">
                                    <h6>Lewis Mann - <span>5 Hour ago</span></h6>
                                    <p>Finally it came out ages ago</p>
                                </div>
                            </div>
                            <div class="anime__review__item">
                                <div class="anime__review__item__pic">
                                    <img src="/img/anime/review-6.jpg" alt="">
                                </div>
                                <div class="anime__review__item__text">
                                    <h6>Louis Tyler - <span>20 Hour ago</span></h6>
                                    <p>Where is the episode 15 ? Slow update! Tch</p>
                                </div>
                            </div>
                        </div>
                        <div class="anime__details__form">
                            <div class="section-title">
                                <h5>Your Comment</h5>
                            </div>
                            <form action="#">
                                <textarea placeholder="Your Comment"></textarea>
                                <button type="submit"><i class="fa fa-location-arrow"></i> Review</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="anime__details__sidebar">
                            <div class="section-title">
                                <h5>you might like...</h5>
                            </div>
                            <div class="product__sidebar__view__item set-bg" data-setbg="/img/sidebar/tv-1.jpg">
                                <div class="ep">18 / ?</div>
                                <div class="view"><i class="fa fa-eye"></i> 9141</div>
                                <h5><a href="#">Boruto: Naruto next generations</a></h5>
                            </div>
                            <div class="product__sidebar__view__item set-bg" data-setbg="/img/sidebar/tv-2.jpg">
                                <div class="ep">18 / ?</div>
                                <div class="view"><i class="fa fa-eye"></i> 9141</div>
                                <h5><a href="#">The Seven Deadly Sins: Wrath of the Gods</a></h5>
                            </div>
                            <div class="product__sidebar__view__item set-bg" data-setbg="/img/sidebar/tv-3.jpg">
                                <div class="ep">18 / ?</div>
                                <div class="view"><i class="fa fa-eye"></i> 9141</div>
                                <h5><a href="#">Sword art online alicization war of underworld</a></h5>
                            </div>
                            <div class="product__sidebar__view__item set-bg" data-setbg="/img/sidebar/tv-4.jpg">
                                <div class="ep">18 / ?</div>
                                <div class="view"><i class="fa fa-eye"></i> 9141</div>
                                <h5><a href="#">Fate/stay night: Heaven's Feel I. presage flower</a></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Anime Section End -->

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
        <script src="/js/jquery-3.3.1.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
        <script src="/js/player.js"></script>
        <script src="/js/jquery.nice-select.min.js"></script>
        <script src="/js/mixitup.min.js"></script>
        <script src="/js/jquery.slicknav.js"></script>
        <script src="/js/owl.carousel.min.js"></script>
        <script src="/js/main.js"></script>
        <script src="/js/toastr.js"></script>
        <script src="/js/api.js"></script>
        <script src="/js/game.js?time=<?php echo time() ?>"></script>

    </body>

    </html>