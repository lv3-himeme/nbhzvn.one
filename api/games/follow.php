<?php
$is_api = true;
require __DIR__ . "/../functions.php";
require __DIR__ . "/../users/functions.php";
require __DIR__ . "/../users/cookies.php";
require __DIR__ . "/functions.php";

try {
    if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể theo dõi game này.", 401);
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST": {
            if ($user->check_timeout("follow")) api_response(null, "Bạn đang theo dõi quá nhanh, hãy chậm lại một chút.", 429);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->id) api_response(null, "Vui lòng nhập ID của một game.", 400);
            $game = new Nbhzvn_Game($json->id);
            if (!$game->id) api_response(null, "Không tìm thấy game có ID này.", 404);
            $result = $game->toggle_follow($user->id);
            $response = new stdClass();
            $response->type = $result == ACTION_FOLLOW ? "follow" : "unfollow";
            $response->followers = $game->follow_count();
            $user->update_timeout("follow", time() + 5);
            api_response($response, "Đã " . ($result == ACTION_FOLLOW ? "thêm game này vào danh sách theo dõi" : "bỏ game này ra khỏi danh sách theo dõi") . ".");
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