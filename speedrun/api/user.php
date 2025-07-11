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
            if (!((time() >= 1752253200 && time() < 1752361200) || (time() >= 1752386400 && time() < 1752399000))) api_response(null, "Chưa đến thời gian tham gia sự kiện. Bạn hãy quay lại sau nhé!", 403);
            if (!get("username") || !get("token")) api_response(null, "Bạn đã bị đăng xuất, vui lòng đăng nhập lại.", 400);
            $username = get("username"); $token = get("token");
            $user = new Nbhzvn_User($username, true);
            if (!$user->id || !$user->check_login_token($token)) api_response(null, "Dữ liệu đăng nhập không chính xác, vui lòng đăng nhập lại.", 403);
            $speedrun_data = new Nbhzvn_Speedrunner($user->id);
            if (!$speedrun_data->id) api_response(null, "Bạn không có trong danh sách đăng ký tham gia sự kiện.", 404);
            if ($speedrun_data->playtime) api_response(null, "Bạn đã hoàn thành phần chơi của mình rồi.", 403);
            $res = new stdClass();
            $res->user = $user;
            $res->speedrun_data = $speedrun_data;
            api_response($res, "Thực hiện thành công.");
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