<?php
$is_api = true;
require __DIR__ . "/../../api/functions.php";
require __DIR__ . "/../../api/users/functions.php";
require __DIR__ . "/../../api/users/cookies.php";
require __DIR__ . "/init.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST": {
            if (!(time() >= ONGOING_TIME && time() < RANKING_TIME)) api_response(null, "Chưa thể cấm thành viên này vào lúc này.", 200);
            authenticate();
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->user_id || !$json->ban_reason) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $speedrun_data = new Nbhzvn_Speedrunner($json->user_id);
            if (!$speedrun_data->id) api_response(null, "Không tìm thấy thông tin của thành viên này.", 404);
            if ($speedrun_data->ban_reason) api_response(null, "Thành viên này đã bị tạm dừng từ trước đó rồi.", 200);
            $speedrun_data->ban($json->ban_reason);
            api_response($res, "Thực hiện thành công.");
            break;
        }
        case "DELETE": {
            authenticate();
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->user_id) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $speedrun_data = new Nbhzvn_Speedrunner($json->user_id);
            if (!$speedrun_data->id) api_response(null, "Không tìm thấy thông tin của thành viên này.", 404);
            if (!$speedrun_data->ban_reason) api_response(null, "Thành viên này chưa bị tạm dừng.", 200);
            $speedrun_data->unban();
            api_response($res, "Thực hiện thành công.");
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