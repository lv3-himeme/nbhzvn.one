<?php
$is_api = true;
require __DIR__ . "/../functions.php";
require __DIR__ . "/functions.php";
require __DIR__ . "/cookies.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            $result = $user->notifications(); $notifications = [];
            $page = is_numeric(get("page")) ? intval(get("page")) : 1;
            $limit = is_numeric(get("limit")) ? intval(get("limit")) : 20;
            for ($i = ($page - 1) * $limit; $i < min(count($result), $page * $limit); $i++) {
                if ($result[$i]) array_push($notifications, $result[$i]);
            }
            if (get("html")) {
                $html = "";
                foreach ($notifications as $notification) $html .= $notication->to_html();
                $notifications = $html;
            }
            api_response($notifications, "Thực hiện thành công.");
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