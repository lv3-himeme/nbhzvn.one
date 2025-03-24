<?php
$is_api = true;
require __DIR__ . "/../functions.php";
require __DIR__ . "/../users/functions.php";
require __DIR__ . "/../users/cookies.php";
require __DIR__ . "/functions.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            if (!get("id")) api_response(null, "Vui lòng nhập ID của game.", 400);
            $game = new Nbhzvn_Game(intval(get("id")));
            if (!$game->id) api_response(null, "Không tìm thấy game có ID này.", 404);
            $result = $game->all_ratings(); $ratings = [];
            $page = is_numeric(get("page")) ? intval(get("page")) : 1;
            $limit = is_numeric(get("limit")) ? intval(get("limit")) : 5;
            for ($i = ($page - 1) * $limit; $i < min(count($result), $page * $limit); $i++) {
                if ($result[$i]) array_push($ratings, $result[$i]);
            }
            if (get("html")) {
                $html = "";
                foreach ($ratings as $rating) $html .= $rating->to_html($user);
                $ratings = $html;
            }
            api_response($ratings, "Thực hiện thành công.");
        }
        case "PUT": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể đánh giá game này.", 401);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->id || !$json->rating || !$json->reason) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $game = new Nbhzvn_Game($json->id);
            if (!$game->id) api_response(null, "Không tìm thấy game có ID này.", 404);
            if ($user->id == $game->uploader) api_response(null, "Bạn không thể đánh giá game do chính mình tải lên.", 403);
            $result = $game->add_rating($user->id, intval($json->rating), $json->reason);
            api_response($result, "Đã đánh giá game thành công.");
            break;
        }
        case "DELETE": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể xóa đánh giá.", 401);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->id || !$json->reason) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $rating = new Nbhzvn_Rating($json->id);
            if (!$rating->id) api_response(null, "Không tìm thấy đánh giá có ID này.", 404);
            if ($user->type < 3) api_response(null, "Bạn không thể xoá đánh giá này.", 403);
            $rating->delete($json->reason);
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