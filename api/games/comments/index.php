<?php
$is_api = true;
require __DIR__ . "/../../functions.php";
require __DIR__ . "/../../users/functions.php";
require __DIR__ . "/../../users/cookies.php";
require __DIR__ . "/../functions.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            if (!get("id")) api_response(null, "Vui lòng nhập ID của game.", 400);
            $game = new Nbhzvn_Game(intval(get("id")));
            if (!$game->id) api_response(null, "Không tìm thấy game có ID này.", 404);
            $result = $game->comments(); $comments = [];
            $page = is_numeric(get("page")) ? intval(get("page")) : 1;
            $limit = is_numeric(get("limit")) ? intval(get("limit")) : 20;
            for ($i = ($page - 1) * $limit; $i < min(count($result), $page * $limit); $i++) {
                if ($result[$i]) array_push($comments, $result[$i]);
            }
            if (get("html")) {
                $html = "";
                foreach ($comments as $comment) $html .= $comment->to_html(!!$comment->replied_to, $user);
                $comments = $html;
            }
            api_response($comments, "Thực hiện thành công.");
        }
        case "PUT": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể bình luận.", 401);
            if ($user->check_timeout("comment")) api_response(null, "Bạn cần đợi ít nhất 1 phút từ lần bình luận cuối cùng trước khi có thể bình luận một lần nữa.", 429);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->id || !$json->content) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $game = new Nbhzvn_Game($json->id);
            if (!$game->id) api_response(null, "Không tìm thấy game có ID này.", 404);
            if ($json->replied_to) {
                $reply_comment = new Nbhzvn_Comment($json->replied_to);
                if (!$reply_comment->id) api_response(null, "Không tìm thấy bình luận có ID là " . $json->replied_to . " để trả lời.", 404);
            }
            $result = $game->add_comment($user->id, $json->content, $json->replied_to);
            $user->update_timeout("comment", time() + 60);
            api_response($result->to_html(!!$json->replied_to, $user), "Thực hiện thành công.");
            break;
        }
        case "POST": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể bình luận.", 401);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->comment_id || !$json->content) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $comment = new Nbhzvn_Comment($json->comment_id);
            if (!$comment->id) api_response(null, "Không tìm thấy bình luận có ID này.", 404);
            if ($comment->author != $user->id) api_response(null, "Không thể chỉnh sửa bình luận này.", 403);
            $comment->edit($json->content);
            api_response($comment->to_html(!!$comment->replied_to, $user), "Thực hiện thành công.");
            break;
        }
        case "DELETE": {
            if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập để có thể bình luận.", 401);
            $json = json_decode(file_get_contents("php://input"));
            if (!$json->comment_id) api_response(null, "Vui lòng nhập đầy đủ thông tin.", 400);
            $comment = new Nbhzvn_Comment($json->comment_id);
            if (!$comment->id) api_response(null, "Không tìm thấy bình luận có ID này.", 404);
            if ($comment->author != $user->id && $user->type < 3) api_response(null, "Không thể xoá bình luận này.", 403);
            $comment->delete();
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