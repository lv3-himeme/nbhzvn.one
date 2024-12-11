<?php
$is_api = true;
require __DIR__ . "/../../functions.php";
require __DIR__ . "/../../users/functions.php";
require __DIR__ . "/../../users/cookies.php";
require __DIR__ . "/../functions.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            if (!get("id")) api_response(null, "Vui lòng nhập ID của bình luận.", 400);
            $comment = new Nbhzvn_Comment(intval(get("id")));
            if (!$comment->id) api_response(null, "Không tìm thấy bình luận có ID này.", 404);
            $result = $comment->fetch_replies();
            if (get("html")) {
                $html = "";
                foreach ($result as $comment) $html .= echo_comment($comment, !!$comment->replied_to, $user);
                $result = $html;
            }
            api_response($result, "Thực hiện thành công.");
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