<?php
require __DIR__ . "/functions.php";

$code = 500;
$text = "Đã có lỗi không xác định xảy ra. Vui lòng thử lại.";

if (is_numeric(get("code"))) $code = get("code");
switch (get("code")) {
    case "403": {
        $text = "Bạn không có quyền truy cập vào endpoint này.";
        break;
    }
    case "404": {
        $text = "Thử kiểm tra lại địa chỉ endpoint bạn đã nhập. Không biết là bạn có nhập sai chỗ nào không?";
        break;
    }
    case "500": {
        $text = "Đã có lỗi không xác định xảy ra. Vui lòng liên hệ với nhà phát triển của website.";
        break;
    }
}

api_response(null, $text, $code);
?>