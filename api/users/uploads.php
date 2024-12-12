<?php
$is_api = true;
require __DIR__ . "/../functions.php";
require __DIR__ . "/functions.php";
require __DIR__ . "/cookies.php";
require __DIR__ . "/../games/functions.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            if (!get("id")) api_response(null, "Vui lòng nhập ID của người dùng.", 400);
            $profile_user = new Nbhzvn_User(intval(get("id")));
            if (!$profile_user->id) api_response(null, "Không tìm thấy người dùng có ID này.", 404);
            $result = $profile_user->uploaded_games(); $games = [];
            $page = get("page") ? intval(get("page")) : 1;
            for ($i = ($page - 1) * 20; $i < min(count($result), $page * 20); $i++) {
                if ($result[$i]) array_push($games, $result[$i]);
            }
            if (get("html")) {
                $html = "";
                foreach ($games as $game) $html .= echo_search_game($game, true);
                $games = $html;
            }
            api_response($games, "Thực hiện thành công.");
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