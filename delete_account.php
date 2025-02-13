<?php
require "api/functions.php";
require "api/users/functions.php";
require "api/users/cookies.php";
$fatal_error = "";

if (get("completed")) $fatal_error = "<b>Toàn bộ</b> dữ liệu trên tài khoản của bạn đã bị xóa vĩnh viễn và không thể khôi phục lại được nữa.<br>Cảm ơn bạn đã sử dụng các tính năng của <b>Nobihaza Vietnam Community Collection</b> trong suốt thời gian qua.";
else if (!$user) redirect_to_home();

$error = "";
$verification_string = "Tôi xác nhận việc muốn xóa tài khoản " . $user->username . " vĩnh viễn, cùng với những dữ liệu trong tài khoản này. Tôi hiểu rằng dữ liệu của tài khoản này sẽ hoàn toàn không thể khôi phục lại được sau khi đã xóa, dù chỉ là một phần nhỏ. Tất cả mọi người, kể cả Quản trị viên và Nhà phát triển, sẽ không chịu trách nhiệm bồi thường hay khôi phục tài khoản của tôi trong mọi trường hợp, kể cả khi đó là trường hợp xóa nhầm ngoài ý muốn.";

if (!get("completed")) {
    try {
        if (post("submit")) {
            if (!check_csrf(post("csrf_token"))) $error = "Mã xác thực CSRF không đúng.";
            else if (!post("password")) $error = "Vui lòng nhập mật khẩu.";
            else if (!$user->verify_passphrase(post("password"))) $error = "Mật khẩu không đúng.";
            else if (post("confirm") != $verification_string) $error = "Vui lòng sao chép toàn bộ câu xác nhận vào ô Xác Nhận (đúng từng kí tự và phân biệt hoa thường).";
            else {
                // Delete all game data related to that user
                db_query('DELETE c FROM `nbhzvn_comments` c JOIN `nbhzvn_games` g ON c.game_id = g.id WHERE g.uploader = ?', $user->id);
                db_query('DELETE r FROM `nbhzvn_gameratings` r JOIN `nbhzvn_games` g ON r.game_id = g.id WHERE g.uploader = ?', $user->id);
                db_query('DELETE f FROM `nbhzvn_gamefollows` f JOIN `nbhzvn_games` g ON f.game_id = g.id WHERE g.uploader = ?', $user->id);
                // Delete all games uploaded by that user
                db_query('DELETE FROM `nbhzvn_games` WHERE `uploader` = ?', $user->id);
                // Delete all comments made by that user
                db_query('DELETE FROM `nbhzvn_comments` WHERE `author` = ?', $user->id);
                // Delete all follows made by that user
                db_query('DELETE FROM `nbhzvn_gamefollows` WHERE `author` = ?', $user->id);
                // Delete all ratings made by that user
                db_query('DELETE FROM `nbhzvn_gameratings` WHERE `author` = ?', $user->id);
                // Delete all notifications sent to that user
                db_query('DELETE FROM `nbhzvn_notifications` WHERE `user_id` = ?', $user->id);
                // Delete all timeouts for that user
                db_query('DELETE FROM `nbhzvn_timeouts` WHERE `user_id` = ?', $user->id);
                // Finally, delete the user data
                db_query('DELETE FROM `nbhzvn_users` WHERE `id` = ?', $user->id);
                // Delete the cookie data
                setcookie("nbhzvn_username", "", time() - 3600);
                setcookie("nbhzvn_login_token", "", time() - 3600);
                header("Location: /delete_account?completed=1");
                die();
            }
        }
    }
    catch (Exception $ex) {
        switch ($ex->getMessage()) {
            case MISSING_INFORMATION: {
                $error = "Vui lòng nhập đầy đủ thông tin.";
                break;
            }
            default: {
                $error = "Có lỗi không xác định xảy ra. Vui lòng báo cáo cho nhà phát triển của website.";
                break;
            }
        }
    }
}
refresh_csrf();
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
        $title = "Xóa Tài Khoản";
        require __DIR__ . "/head.php";
    ?>
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
                <h3>Xóa Tài Khoản</h3>
                <?php if ($fatal_error): ?>
                <p><?php echo $fatal_error ?></p>
                <p><a href="/"><button class="site-btn">Về Trang Chủ</button></p>
                <?php else: ?>
                <div class="blog__details__text faq">
                    <p>Bạn chuẩn bị <b>xóa vĩnh viễn tài khoản của bạn</b> ra khỏi cơ sở dữ liệu của <b>Nobihaza Vietnam Collection</b>.</p>
                    <p>Để đảm bảo quyền riêng tư về dữ liệu của bạn, yêu cầu xóa vĩnh viễn sẽ được thực hiện ngay lập tức sau khi bạn đã xác nhận muốn xóa tài khoản mà không có thời gian chờ. Tuy nhiên cần phải cân nhắc thật kĩ trước khi nhấn nút xóa, vì tất cả những thứ sau đây sẽ bị xóa khỏi trang web vĩnh viễn:</p>
                    <ul>
                        <li><b>Các game bạn đã tải lên</b> (nếu bạn là Uploader, hoặc đã từng là Uploader) và các thông tin khác (như đánh giá, bình luận và lượt theo dõi) liên quan đến các game đó</li>
                        <li><b>Các bình luận, đánh giá và các lượt theo dõi bạn đã thực hiện</b></li>
                        <?php if ($user->discord_id): ?>
                        <li>Bạn đang liên kết với tài khoản Discord. Việc xóa tài khoản sẽ bỏ liên kết với tài khoản Discord của bạn, tuy nhiên <b>bạn sẽ cần phải xóa thủ công các quyền được cấp cho "NbhzVN Community Collection" trong phần Cài Đặt -> Ứng Dụng Được Cho Phép của tài khoản Discord để có thể xóa hoàn toàn việc liên kết</b>.</li>
                        <?php endif ?>
                        <?php if ($user->type > 1): ?>
                        <li>Bạn cũng đang là <b><?php echo $type_vocab[$user->type] ?></b> của trang web này. <b>Việc xóa tài khoản cũng sẽ xóa chức vụ này của bạn, và sẽ không được tự động cấp lại khi bạn tạo tài khoản mới.</b></li>
                        <?php endif ?>
                    </ul><br>
                    <p>Một khi đã xóa vĩnh viễn thì <b>không ai sẽ có thể khôi phục lại tài khoản của bạn dù chỉ là một phần nhỏ</b>! Vì vậy đây là cảnh báo cuối cùng, hãy cân nhắc thật kĩ trước khi tiếp tục thực hiện!</p>
                    <p>Bạn cũng có thể xem <a href="https://github.com/Serena1432/NobihazaVietnamCollection/blob/main/delete_account.php" target="_blank">mã nguồn của phần xóa tài khoản</a> để có thể xác nhận kĩ hơn rằng <b>tất cả dữ liệu của bạn sẽ bị xóa vĩnh viễn sau khi đã nhấn nút "Tiến Hành Xóa Tài Khoản".</b>
                    <hr>
                    <p>Để xác nhận xóa vĩnh viễn tài khoản, hãy nhập mật khẩu của bạn và sao chép câu sau đây vào ô Xác Nhận bên dưới (không bao gồm dấu ngoặc kép):</p>
                    <p>"<b><?php echo $verification_string ?></b>"</p>
                </div>
                <form action="" method="POST">
                    <div class="input__item" style="width: 100%">
                        <input type="password" name="password" placeholder="Mật Khẩu" required>
                        <span class="icon_lock"></span>
                    </div>
                    <div class="input__item" style="width: 100%">
                        <input type="text" name="confirm" placeholder="Xác Nhận" required>
                        <span class="icon_pencil"></span>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf(); ?>" />
                    <p style="color: #e36666"><i><?php echo $error ?></i></p>
                    <button type="submit" name="submit" class="site-btn" value="Submit">Tiến Hành Xóa Tài Khoản</button>
                </form>
                <?php endif ?>
            </div>
        </div>
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

</body>

</html>