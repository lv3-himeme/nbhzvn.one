<?php
require "api/functions.php";
require "api/users/cookies.php";
require "api/games/functions.php";
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
$parsedown->setMarkupEscaped(true);
use Soundasleep\Html2Text;

$featured_games = featured_games();

function echo_game($tmp_game) {
    global $status_vocab;
    global $engine_vocab;
    echo '
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="product__item">
                <a href="/games/' . $tmp_game->id . '"><div class="product__item__pic set-bg" data-setbg="/uploads/' . $tmp_game->image . '">
                    <div class="ep">' . $status_vocab[$tmp_game->status] . '</div>
                    <div class="comment"><i class="fa fa-comments"></i> ' . number_format($tmp_game->comments, 0, ",", ".") . '</div>
                    <div class="view"><i class="fa fa-eye"></i> ' . number_format($tmp_game->views, 0, ",", ".") . '</div>
                </div></a>
                <div class="product__item__text">
                    <ul>
                        <li>' . $engine_vocab[$tmp_game->engine] . '</li>
                    </ul>
                    <h5><a href="/games/' . $tmp_game->id . '">' . htmlentities($tmp_game->name) . '</a></h5>
                </div>
            </div>
        </div>
    ';
}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        require("head.php");
    ?>
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

    <?php if (count($featured_games)): ?>
    <!-- Hero Section Begin -->
    <section class="hero">
        <div class="container">
            <div class="hero__slider owl-carousel">
                <?php
                    foreach ($featured_games as $tmp_game) {
                        echo '
                            <div class="hero__items set-bg" data-setbg="/uploads/' . $tmp_game->image . '">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="hero__text">
                                            <div class="label">' . htmlentities($engine_vocab[$tmp_game->engine]) . '</div>
                                            <h2>' . htmlentities($tmp_game->name) . '</h2>
                                            <p>' . explode("\n", Html2Text::convert($parsedown->text($tmp_game->description)))[0] . '</p>
                                            <a href="/games/' . $tmp_game->id . '"><span>Xem thông tin game</span> <i class="fa fa-angle-right"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ';
                    }
                ?>
            </div>
        </div>
    </section>
    <!-- Hero Section End -->
    <?php endif ?>

    <!-- Product Section Begin -->
    <section class="product spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="trending__product">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8">
                                <div class="section-title">
                                    <h4>Thịnh Hành</h4>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4">
                                <div class="btn__all">
                                    <a href="#" class="primary-btn">Xem Tất Cả <span class="arrow_right"></span></a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php
                                foreach (trending_games(6) as $tmp_game) echo_game($tmp_game);
                            ?>
                        </div>
                    </div>
                    <div class="popular__product">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8">
                                <div class="section-title">
                                    <h4>Game Phổ Biến</h4>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4">
                                <div class="btn__all">
                                    <a href="#" class="primary-btn">Xem Tất Cả <span class="arrow_right"></span></a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php
                                foreach (popular_games(6) as $tmp_game) echo_game($tmp_game);
                            ?>
                        </div>
                    </div>
                    <div class="recent__product">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8">
                                <div class="section-title">
                                    <h4>Game Mới Tải Lên</h4>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4">
                                <div class="btn__all">
                                    <a href="#" class="primary-btn">Xem Tất Cả <span class="arrow_right"></span></a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php
                                foreach (recent_games(6) as $tmp_game) echo_game($tmp_game);
                            ?>
                        </div>
                    </div>
                    <div class="live__product">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8">
                                <div class="section-title">
                                    <h4>Game Dành Cho Điện Thoại</h4>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4">
                                <div class="btn__all">
                                    <a href="#" class="primary-btn">Xem Tất Cả <span class="arrow_right"></span></a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php
                                foreach (mobile_games(6) as $tmp_game) echo_game($tmp_game);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-8">
                    <div class="product__sidebar">
                        <div class="product__sidebar__view">
                            <div class="section-title">
                                <h5>Top Theo Dõi</h5>
                            </div>
                            <?php
                                foreach (most_followed_games(6) as $tmp_game_info) {
                                    $tmp_game = $tmp_game_info->data; $follows = $tmp_game_info->follow_count;
                                    echo '
                                    <div class="product__sidebar__view__item set-bg mix month week"
                                    data-setbg="/uploads/' . $tmp_game->image . '">
                                        <div class="ep">' . $engine_vocab[$tmp_game->engine] . '</div>
                                        <div class="view"><i class="fa fa-heart"></i> ' . number_format($follows, 0, ",", ".") . '</div>
                                        <h5><a href="/games/' . $tmp_game->id . '">' . htmlentities($tmp_game->name) . '</a></h5>
                                    </div>
                                    ';
                                }
                            ?>
        </div>
    </div>
    <div class="product__sidebar__comment">
        <div class="section-title">
            <h5>Game Ngẫu Nhiên</h5>
        </div>
        <?php
            foreach (random_games(0, 10) as $tmp_game) {
                echo '
                    <div class="product__sidebar__comment__item">
                        <a href="/games/' . $tmp_game->id . '"><div class="product__sidebar__comment__item__pic">
                            <img src="/uploads/' . $tmp_game->image . '" alt="">
                        </div></a>
                        <div class="product__sidebar__comment__item__text">
                            <ul>
                                <li>' . $engine_vocab[$tmp_game->engine] . '</li>
                            </ul>
                            <h5><a href="/games/' . $tmp_game->id . '">' . htmlentities($tmp_game->name) . '</a></h5>
                            <span><i class="fa fa-eye"></i> ' . number_format($tmp_game->views, 0, ",", ".") . ' lượt xem</span>
                        </div>
                    </div>
                ';
            }
        ?>
    </div>
</div>
</div>
</div>
</div>
</section>
<!-- Product Section End -->

<!-- Footer Section Begin -->
<footer class="footer">
    <?php require "footer.php"; ?>
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


</body>

</html>