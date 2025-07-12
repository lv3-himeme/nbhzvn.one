<?php
$is_api = true;
require __DIR__ . "/../../api/functions.php";
require __DIR__ . "/../../api/users/functions.php";
require __DIR__ . "/../../api/users/cookies.php";
require __DIR__ . "/init.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            web_authenticate();
            $res = get_username();
            if ($res->error) api_response(null, "Máy chủ đã trả về lỗi: " . $res->error, 500);
            foreach ($res->data as $info) {
                db_query('UPDATE `nbhzvn_speedrunners` SET `discord_username` = ? WHERE `discord_id` = ?', $info->username, $info->id);
            }
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