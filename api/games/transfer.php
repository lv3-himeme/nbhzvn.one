<?php
$is_api = true;
require __DIR__ . "/../functions.php";
require __DIR__ . "/../users/functions.php";
require __DIR__ . "/../users/cookies.php";
require __DIR__ . "/functions.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể bình luận.", 401);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->id || !$json->owner_id || !$json->password) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            if (!$user->verify_passphrase($json->password)) api_response(null, "Mật khẩu xác thực không chính xác.", 401);
            $game = new Nbhzvn_Game(intval($json->id));
            if (!$game->id) api_response(null, "Không tìm thấy game có ID này.", 404);
            $target = new Nbhzvn_User(intval($json->owner_id));
            if (!$target->id) api_response(null, "Không tìm thấy thành viên có ID này.", 404);
            if ($target->id == $user->id) api_response(null, "Bạn đang là người quản lý game này rồi.", 403);
            if ($game->uploader != $user->id) api_response(null, "Bạn không đủ quyền để thực hiện yêu cầu này.", 403);
            $game->change_owner(intval($json->owner_id));
            $target->send_notification('/games/' . $game->id, '**' . $user->display_name() . '** đã chuyển quyền quản lý game **' . $game->name . '** cho bạn. Nhấn vào thông báo để xem chi tiết.');
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