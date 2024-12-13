        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="header__logo">
                        <a href="/">
                            <img src="/img/logo.png" alt="">
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="header__nav">
                        <nav class="header__menu mobile-menu">
                            <ul>
                                <li class="active"><a href="/">Trang Chủ</a></li>
                                <li><a href="/games">Danh Sách Game <span class="arrow_carrot-down"></span></a>
                                    <ul class="dropdown">
                                        <li><a href="/games?category=popular">Game Phổ Biến</a></li>
                                        <li><a href="/games?category=mobile">Game Dành Cho Điện Thoại</a></li>
                                        <li><a href="/search?language=1">Game Tiếng Việt</a></li>
                                        <li><a href="/search?engine=1">Game RPG Maker 2000/2003</a></li>
                                        <li><a href="/search?engine=2">Game RPG Maker XP/VX/VX Ace</a></li>
                                        <li><a href="/search?engine=3">Game RPG Maker MV/MZ</a></li>
                                    </ul>
                                </li>
                                <li><a href="/faq">FAQ</a></li>
                                <li><a href="https://www.facebook.com/groups/nobihazavietnam">Facebook</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="header__right">
                        <a href="/search" title="Tìm kiếm" class="search-switch"><span class="icon_search"></span></a>
                        <?php if ($user && $user->type >= 2): ?>
                        <a href="/upload" title="Thêm game mới"><span class="icon_upload"></span></a>
                        <?php endif ?>
                        <?php if ($user): ?>
                            <?php $unread_notifications = $user->unread_notifications() ?>
                            <a href="/notifications" class="nbhzvn_notification<?php if (count($unread_notifications)) echo ' unread' ?>"><span class="fa fa-bell"></span><?php if (count($unread_notifications)) echo ' <span class="number">(' . count($unread_notifications) . ')</span>' ?></a>
                            <a href="/profile"><span class="icon_profile"></span> <span class="nbhzvn_username"><?php echo $user->display_name ? $user->display_name : $user->username; ?></span></a>
                        <?php else: ?>
                            <a href="/login"><span class="icon_profile"></span></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div id="mobile-menu-wrap"></div>
        </div>
