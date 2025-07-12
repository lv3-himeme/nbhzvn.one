<?php
$is_api = true;
require __DIR__ . "/../../api/functions.php";
require __DIR__ . "/../../api/users/functions.php";
require __DIR__ . "/../../api/users/cookies.php";
require __DIR__ . "/init.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            authenticate();
            if (!((time() >= REGISTRATION_CLOSING_TIME && time() < TEST_CLOSING_TIME) || (time() >= ONGOING_TIME && time() < RANKING_TIME))) api_response(null, "Đã hết thời gian diễn ra sự kiện. Cảm ơn bạn đã tham gia!", 403);
            if (!get("username") || !get("token")) api_response(null, "Bạn đã bị đăng xuất, vui lòng đăng nhập lại.", 403);
            $username = get("username"); $token = get("token");
            $user = new Nbhzvn_User($username, true);
            if (!$user->id || !$user->check_login_token($token)) api_response(null, "Dữ liệu đăng nhập không chính xác, vui lòng đăng nhập lại.", 403);
            $speedrun_data = new Nbhzvn_Speedrunner($user->id);
            if ($speedrun_data->ban_reason) api_response(null, "Bạn đã bị Ban Tổ Chức tạm dừng tham gia sự kiện với lý do:\n" . $speedrun_data->ban_reason . "\n\nNếu bạn cho rằng đây là sự nhầm lẫn, hãy liên hệ với Ban Tổ Chức để được xử lý.", 403);
            api_response(null, "Thực hiện thành công.");
            break;
        }
        default: {
            api_response(null, "Yêu cầu HTTP không hợp lệ.", 405);
            break;
        }
    }
}
catch (Exception $ex) {
    switch ($ex->getMessage()) {
        default: {
            $error = "Có lỗi không xác định xảy ra. Vui lòng báo cáo cho nhà phát triển của website.";
            break;
        }
    }
    api_response($ex->getMessage(), $error, 500);
}
?>