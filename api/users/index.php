<?php
require __DIR__ . "/../functions.php";
require __DIR__ . "/functions.php";

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET": {
            $users = [];
            if (get("query")) {
                $query = "%" . preg_replace('/[^a-zA-Z0-9_ -]/s', "%", get("query")) . "%";
                $result = db_query('SELECT * FROM `nbhzvn_users` WHERE `username` LIKE ? OR `display_name` LIKE ?', $query, $query);
                while ($row = $result->fetch_object()) array_push($users, new Nbhzvn_User($row, true));
                api_response($users, "Thực hiện thành công.");
            }
            else if (get("id")) {
                $user = new Nbhzvn_User(intval(get("id")), true);
                if ($user->id) array_push($users, $user);
                api_response($users, "Thực hiện thành công.");
            }
            else api_response(null, "Vui lòng nhập từ khoá (query) hoặc ID thành viên (id).", 400);
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