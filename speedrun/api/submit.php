<?php
$is_api = true;
require __DIR__ . "/../../api/functions.php";
require __DIR__ . "/../../api/users/functions.php";
require __DIR__ . "/../../api/users/cookies.php";
require __DIR__ . "/init.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST": {
            authenticate();
            if (!((time() >= REGISTRATION_CLOSING_TIME && time() < TEST_CLOSING_TIME) || (time() >= ONGOING_TIME && time() < RANKING_TIME))) api_response(null, "Chưa đến thời gian tham gia sự kiện. Bạn hãy quay lại sau nhé!", 403);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->user_id || !$json->playtime != null || $json->saves != null || !$json->ranking != null) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $speedrun_data = new Nbhzvn_Speedrunner($json->user_id);
            if (!$speedrun_data->id) api_response(null, "Không tìm thấy thông tin của thành viên này.", 404);
            if ($speedrun_data->ban_reason) api_response(null, "Thành viên này đã bị tạm dừng, không thể gửi phần chơi của mình nữa.", 200);
            if ($speedrun_data->playtime) api_response(null, "Thành viên này đã gửi phần chơi của mình trước đó rồi.", 403);
            $speedrun_data->submit($json->playtime, time() - $speedrun_data->start_timestamp, $json->saves, $json->ranking);
            api_response($res, "Đã ghi nhận phần chơi của bạn. Cảm ơn bạn đã tham gia!");
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