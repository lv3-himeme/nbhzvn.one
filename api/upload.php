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
            if (!$user || $user->type < 2) api_response(null, "Bạn không có quyền truy cập vào API này.", 403);
            if (!isset($_FILES["file"])) api_response(null, "Vui lòng chọn một tệp tin để tải lên.", 400);
            $extension = strtolower(pathinfo("./uploads/" . basename($_FILES["file"]["name"]), PATHINFO_EXTENSION));
            $types = ["zip", "rar", "7z", "jpg", "png", "webp", "jpeg"];
            if (!in_array($extension, $types)) api_response(null, "Định dạng tệp tin không được hỗ trợ.", 400);
            $name = random_string(64) . "." . $extension;
            $path = $folder . "/" . $name;
            if (!move_uploaded_file($_FILES["file"]["tmp_name"], $path) || !file_exists($path)) api_response(null, "Không thể tải tệp tin lên vào thời gian này, vui lòng thử lại.", 500);
            api_response($name, "Tải lên thành công.");
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