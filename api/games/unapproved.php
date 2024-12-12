<?php
$is_api = true;
require __DIR__ . "/../functions.php";
require __DIR__ . "/../users/functions.php";
require __DIR__ . "/../users/cookies.php";
require __DIR__ . "/functions.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            if ($user->type < 3) api_response(null, "Bạn không đủ quyền để thực hiện lệnh này.", 403);
            $result = unapproved_games(); $games = [];
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