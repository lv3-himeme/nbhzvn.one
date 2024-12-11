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
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->id || !$json->rating) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $game = new Nbhzvn_Game($json->id);
            if (!$game->id) api_response(null, "Không tìm thấy game có ID này.", 404);
            if ($user->id == $game->uploader) api_response(null, "Bạn không thể đánh giá game do chính mình tải lên.", 403);
            $result = $game->add_rating($user->id, intval($json->rating));
            api_response($result, "Đã đánh giá game thành công.");
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
        case DB_CONNECTION_ERROR: {
            $error = "Lỗi kết nối tới máy chủ. Vui lòng thử lại.";
            break;
        }
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