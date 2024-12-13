<?php
$is_api = true;
require __DIR__ . "/functions.php";
require __DIR__ . "/users/functions.php";
require __DIR__ . "/users/cookies.php";

try {
    $folder = "../uploads";
    if (!file_exists($folder)) {
        if (!mkdir($folder, 0775, true)) api_response(null, "Có lỗi xảy ra, vui lòng thử lại.", 500);
    }
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST": {
            $json = json_decode(file_get_contents("php://input"));
            if (!$user || $user->type < 2) api_response(null, "Bạn không có quyền truy cập vào API này.", 403);
            if (!$json->chunk) api_response(null, "Vui lòng chọn một chunk để xoá.", 400);
            if (str_starts_with($json->chunk, ".") || str_contains($json->chunk, "/")) api_response(null, "Tên chunk không hợp lệ.", 400);
            $path = $folder . "/" . $json->chunk;
            if (!file_exists($path)) api_response(null, "Chunk đã bị xoá trước đó, bạn không cần phải làm gì thêm.", 200);
            if (!rmdir($path) || file_exists($path)) api_response(null, "Không thể xoá chunk vào thời gian này, vui lòng thử lại.", 500);
            api_response($name, "Xoá chunk thành công.");
            break;
        }
        default: {
            api_response(null, "Yêu cầu HTTP không hợp lệ.", 405);
            break;
        }
    }
}
catch (Exception $ex) {
    api_response($ex->getMessage(), "Có lỗi xảy ra, vui lòng thử lại.", 500);
}
?>