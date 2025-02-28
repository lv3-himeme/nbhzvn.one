<?php
$is_api = true;
require __DIR__ . "/../functions.php";
require __DIR__ . "/../users/functions.php";
require __DIR__ . "/../users/cookies.php";
require __DIR__ . "/functions.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            if (!get("game_id") && !get("id")) api_response(null, "Vui lòng nhập ID của game hoặc ID của nội dung cập nhật.", 400);
            $changelogs = [];
            if (get("game_id")) {
                $game = new Nbhzvn_Game(intval(get("game_id")));
                if (!$game->id) api_response(null, "Không tìm thấy game có ID này.", 404);
                $result = $game->changelogs();
                $page = is_numeric(get("page")) ? intval(get("page")) : 1;
                $limit = is_numeric(get("limit")) ? intval(get("limit")) : 5;
                for ($i = ($page - 1) * $limit; $i < min(count($result), $page * $limit); $i++) {
                    if ($result[$i]) array_push($changelogs, $result[$i]);
                }
            }
            else if (get("id")) {
                $changelog = new Nbhzvn_Changelog(intval(get("id")));
                if ($changelog->id) array_push($changelogs, $changelog);
            }
            if (get("html")) {
                $html = "";
                foreach ($changelogs as $changelog) {
                    $changelog->set_game_object($game);
                    $html .= $changelog->to_html($user);
                }
                $changelogs = $html;
            }
            api_response($changelogs, "Thực hiện thành công.");
        }
        case "POST": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể chỉnh sửa nội dung cập nhật.", 401);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->id || !$json->description) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $changelog = new Nbhzvn_Changelog(intval($json->id));
            if (!$changelog->id) api_response(null, "Không tìm thấy nội dung cập nhật có ID này.", 404);
            $game = new Nbhzvn_Game($changelog->game_id);
            if ($user->id != $game->uploader) api_response(null, "Bạn không đủ quyền để thực hiện hành động này.", 403);
            $changelog->edit_description($json->description);
            $changelog->set_game_object($game);
            api_response($changelog->to_html($user), "Thực hiện thành công.");
            break;
        }
        case "PUT": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể đăng nội dung cập nhật mới.", 401);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->game_id || !$json->version || !$json->description) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $game = new Nbhzvn_Game(intval($json->game_id));
            if (!$game->id) api_response(null, "Không tìm thấy game có ID này.", 404);
            if ($user->id != $game->uploader) api_response(null, "Bạn không đủ quyền để thực hiện hành động này.", 403);
            $html = $game->add_changelog($json->version, $json->description, $user);
            if ($html == FAILED) api_response(null, "Đã có lỗi xảy ra, vui lòng thử lại.", 500);
            api_response($html, "Thực hiện thành công.");
            break;
        }
        case "DELETE": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể xóa nội dung cập nhật.", 401);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->id) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $changelog = new Nbhzvn_Changelog($json->id);
            if (!$changelog->id) api_response(null, "Không tìm thấy nội dung cập nhật có ID này.", 404);
            $game = new Nbhzvn_Game($changelog->game_id);
            if ($user->id != $game->uploader) api_response(null, "Bạn không đủ quyền để thực hiện hành động này.", 403);
            $changelog->delete();
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