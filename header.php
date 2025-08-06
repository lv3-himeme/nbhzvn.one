        <?php if (true): ?>
        <div style="width: 100%; padding: 10px; background-color: #af1932; color: #ddd; text-align: center">Nobihaza Vietnam Community đã ra mắt một trang hướng dẫn cài đặt và chơi game chi tiết. <a style="color: #b7b7b7" href="https://guides.nbhzvn.one" target="_blank">Bạn có thể đọc hướng dẫn tại đây</a>.</div>
        <?php endif ?>
        <div class="container">
            <div class="row align-items-center">
                <div class="header-flex d-flex align-items-center flex-grow-1">
                    <div class="header__logo">
                        <a href="/">
                            <img src="/img/logo.png" alt="">
                        </a>
                    </div>
                    <div class="header__nav" style="margin-left: 20px">
                        <nav class="header__menu mobile-menu">
                            <ul>
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
                                <li><a href="https://guides.nbhzvn.one" target="_blank">Hướng Dẫn Chơi</a></li>
                                <li><a href="/tools">Phần Mềm Chơi Game <span class="arrow_carrot-down"></span></a>
                                    <ul class="dropdown">
                                        <li><a href="https://github.com/The-Firefly-Project/EasyRPGPlayer-Vietnamese/releases/tag/0.8.1.1">EasyRPG</a></li>
                                        <li><a href="/tools/JoiPlay">JoiPlay</a></li>
                                        <li><a href="/tools/MKXP">MKXP</a></li>
                                    </ul>
                                </li>
                                <li><a href="javascript:void(0)">Liên Kết Nhóm <span class="arrow_carrot-down"></span></a>
                                    <ul class="dropdown">
                                        <li><a href="https://www.facebook.com/groups/nobihazavietnam">Facebook</a></li>
                                        <li><a href="https://discord.gg/QpMuX3gQ5u">Discord</a></li>
                                    </ul>
                                </li>
                                <li class="nbhzvn_mobile_user">
                                    <?php if ($user): ?>
                                    <a href="/profile"><?php echo $user->display_name(); ?></span></a>
                                    <ul class="dropdown">
                                        <li><a href="/profile">Thông Tin Cá Nhân</a></li>
                                        <li><a href="/change_info">Thay Đổi Thông Tin</a></li>
                                        <li><a href="/logout">Đăng Xuất</a></li>
                                    </ul>
                                    <?php else: ?>
                                    <a href="/login">Đăng Nhập</a>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="header__right d-flex align-items-center flex-shrink-0">
                    <a href="/search" title="Tìm kiếm"><span class="icon_search"></span></a>
                    <?php if ($user): ?>
                        <?php if ($user && $user->type >= 2): ?>
                            <a href="/upload" title="Thêm game mới"><span class="icon_upload"></span></a>
                        <?php endif ?>
                        <?php $unread_notifications = $user->unread_notifications() ?>
                        <a href="/notifications" class="nbhzvn_notification<?php if (count($unread_notifications)) echo ' unread' ?>"><span class="fa fa-bell"></span><?php if (count($unread_notifications)) echo ' (' . count($unread_notifications) . ')'; ?></a>
                    <?php endif; ?>
                    <nav class="header__menu">
                        <ul>
                            <?php if ($user): ?>
                                <li style="padding: 13px 0"><a href="/profile" class="nbhzvn_user_icon">
                                    <span class="nbhzvn_avatar_container"><span class="nbhzvn_avatar"><span class="nbhzvn_username"><?php echo substr($user->display_name(), 0, 1); ?></span></span> <span class="arrow_carrot-down"></span></span></a>
                                    <ul class="dropdown nbhzvn_user_dropdown" style="left: -180px">
                                        <li><a href="/profile"><b><?php echo $user->display_name(); ?></b></a></li>
                                        <li><a href="/change_info">Thay Đổi Thông Tin</a></li>
                                        <li><a href="/logout">Đăng Xuất</a></li>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li><a href="/login"><span class="icon_profile"></span> <span class="nbhzvn_username" style="margin-left: 10px">Đăng Nhập</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <div id="mobile-menu-wrap"></div>
        </div>
