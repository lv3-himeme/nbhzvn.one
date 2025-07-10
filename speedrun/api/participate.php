<?php
$is_api = true;
require __DIR__ . "/../../api/functions.php";
require __DIR__ . "/../../api/users/functions.php";
require __DIR__ . "/../../api/users/cookies.php";
require __DIR__ . "/init.php";

try {
    if (!$user || !$user->id) api_response(null, "Bạn cần đăng nhập tài khoản để có thể đăng ký tham gia.", 401);
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            if (!in_array(get("os"), ["windows", "mac", "linux", "android", "ios"])) api_response(null, "Hãy chọn một thiết bị hợp lệ.", 400);
            if (time() >= 1752253200) api_response(null, "Đợt đăng ký tham gia đã kết thúc. Hẹn gặp lại bạn ở sự kiện năm sau nhé!", 403);
            if (!$user->discord_id) api_response(null, "Bạn chưa liên kết với tài khoản Discord. Hãy liên kết ở phần <a href='https://nbhzvn.one/change_info'>Thay Đổi Thông Tin</a> của tài khoản và thử lại.", 403);
            $speedrunner = new Nbhzvn_Speedrunner($user->id);
            if ($speedrunner->id) api_response(null, "Bạn đã đăng ký tham gia sự kiện speedrun từ trước đó rồi.", 403);
            $check_response = check_speedrun_user($user->discord_id, true);
            if (!$check_response->pass) {
                $reason = array(
                    "SERVER_ERROR" => "Không thể kết nối tới máy chủ của Discord. Vui lòng thử lại sau.",
                    "INVALID_USER" => "Bạn chưa gia nhập máy chủ Discord của cộng đồng. Hãy <a href='https://discord.gg/QpMuX3gQ5u'>gia nhập vào máy chủ</a> và thử lại.",
                    "NOT_IN_GUILD" => "Bạn chưa gia nhập máy chủ Discord của cộng đồng. Hãy <a href='https://discord.gg/QpMuX3gQ5u'>gia nhập vào máy chủ</a> và thử lại.",
                    "MEMBER_BANNED" => "Bạn đã bị cấm khỏi máy chủ Discord của cộng đồng. Rất tiếc nhưng bạn không đủ điều kiện để tham gia sự kiện.",
                    "MEMBER_MUTED" => "Bạn đang bị tắt tiếng ở máy chủ Discord của cộng đồng. Hãy đợi đến lúc thời hạn tắt tiếng kết thúc và thử lại.",
                    "MEMBER_IS_A_HOST" => "Bạn đang là thành viên của Ban Tổ Chức nên bạn không thể đăng ký tham gia sự kiện. Nếu đây là sự nhầm lẫn, hãy báo cáo với Ban Giám Hiệu của máy chủ.",
                    "MEMBER_IS_ALREADY_SPEEDRUNNER" => "Bạn đã đăng ký tham gia sự kiện speedrun từ trước đó rồi. Nếu đây là sự nhầm lẫn, hãy báo cáo với Ban Giám Hiệu của máy chủ.",
                );
                api_response(null, $reason[$check_response->reason], 403);
            }
            else {
                add_speedrunner($user->id, $user->discord_id, get("os"));
                api_response(null, "Bạn đã đăng ký tham gia sự kiện thành công. Chúc bạn may mắn và đạt kết quả tốt nhất trong sự kiện sắp tới!", 200);
            }
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