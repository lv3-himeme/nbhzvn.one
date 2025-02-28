<?php
$is_api = true;
require __DIR__ . "/../functions.php";
require __DIR__ . "/../users/functions.php";
require __DIR__ . "/../users/cookies.php";
require __DIR__ . "/functions.php";

try {
    if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể đánh giá game này.", 401);
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST": {
        }
        default: {
            api_response(null, "Yêu cầu HTTP không hợp lệ.", 405);
            break;
        }
    }
}
catch (Exception $ex) {
    switch ($ex->getMessage()) {
        case ALREADY_RATED: {
            $error = "Bạn đã đánh giá cho game này rồi.";
            break;
        }
        default: {
            $error = "Có lỗi không xác định xảy ra. Vui lòng báo cáo cho nhà phát triển của website.";
            break;
        }
    }
    api_response($ex->getMessage(), $error, 500);
}
?>