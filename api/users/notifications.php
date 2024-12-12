<?php
$is_api = true;
require __DIR__ . "/../functions.php";
require __DIR__ . "/functions.php";
require __DIR__ . "/cookies.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể xem thông báo.", 401);
            $result = $user->notifications(); $notifications = [];
            $page = is_numeric(get("page")) ? intval(get("page")) : 1;
            $limit = is_numeric(get("limit")) ? intval(get("limit")) : 20;
            for ($i = ($page - 1) * $limit; $i < min(count($result), $page * $limit); $i++) {
                if ($result[$i]) array_push($notifications, $result[$i]);
            }
            if (get("html")) {
                $html = "";
                foreach ($notifications as $notification) $html .= $notification->to_html();
                $notifications = $html;
            }
            api_response($notifications, "Thực hiện thành công.");
        }
        case "DELETE": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể thực hiện hành động này.", 401);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->id) {
                db_query("DELETE FROM `nbhzvn_notifications` WHERE `user_id` = ?", $user->id);
                api_response(null, "Đã xoá tất cả thông báo của bạn.");
            }
            $notification = new Nbhzvn_Notification($json->id);
            if (!$notification->id || $notification->user_id != $user->id) api_response(null, "Không tìm thấy thông báo có ID này.", 404);
            $notification->delete();
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