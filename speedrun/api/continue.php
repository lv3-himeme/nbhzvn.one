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
            if (!((time() >= REGISTRATION_CLOSING_TIME && time() < TEST_CLOSING_TIME) || (time() >= ONGOING_TIME && time() < RANKING_TIME))) api_response(null, "Chưa đến thời gian tham gia sự kiện. Bạn hãy quay lại sau nhé!", 403);
            if (!get("username") || !get("token")) api_response(null, "Bạn đã bị đăng xuất, vui lòng đăng nhập lại.", 400);
            $username = get("username"); $token = get("token");
            $user = new Nbhzvn_User($username, true);
            if (!$user->id || !$user->check_login_token($token)) api_response(null, "Dữ liệu đăng nhập không chính xác, vui lòng đăng nhập lại.", 403);
            $speedrun_data = new Nbhzvn_Speedrunner($user->id);
            if (!$speedrun_data->id) api_response(null, "Bạn không có trong danh sách đăng ký tham gia sự kiện.", 404);
            if (time() >= ONGOING_TIME && !check_stream($speedrun_data->discord_id)->pass) api_response(null, "Bạn chưa stream màn hình của mình lên kênh Discord đã được chỉ định.", 403);
            if ($speedrun_data->ban_reason) api_response(null, "Bạn đã bị Ban Tổ Chức tạm dừng tham gia sự kiện với lý do:\n" . $speedrun_data->ban_reason . "\n\nNếu bạn cho rằng đây là sự nhầm lẫn, hãy liên hệ với Ban Tổ Chức để được xử lý.", 403);
            if (!$speedrun_data->start_timestamp) api_response(null, "Bạn hãy bắt đầu phần chơi của bạn bằng nút New Game trước.", 403);
            if ($speedrun_data->playtime) api_response(null, "Bạn đã hoàn thành phần chơi của mình rồi.", 403);
            $res = new stdClass();
            $res->current_time = time();
            $res->timestamp = $speedrun_data->start_timestamp;
            $res->display_name = $user->display_name();
            api_response($res, "Đã tiếp tục phần chơi trước đó của bạn.");
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