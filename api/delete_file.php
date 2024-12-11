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
            if (!$json->file) api_response(null, "Vui lòng chọn một tệp tin để xoá.", 400);
            if (str_starts_with($json->file, ".") || str_contains($json->file, "/")) api_response(null, "Tên tệp tin không hợp lệ.", 400);
            $path = $folder . "/" . $json->file;
            if (!unlink($path) || file_exists($path)) api_response(null, "Không thể tải tệp tin lên vào thời gian này, vui lòng thử lại.", 500);
            api_response($name, "Xoá tệp tin thành công.");
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