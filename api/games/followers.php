<?php
require __DIR__ . "/../functions.php";
require __DIR__ . "/functions.php";

try {
    if (!get("id")) api_response(null, "Vui lòng nhập ID của một game.", 400);
    $game = new Nbhzvn_Game(intval(get("id")));
    if (!$game->id) api_response(null, "Không tìm thấy game có ID này.", 404);
    api_response($game->follows(), "Thực hiện thành công.");
}
catch (Exception $ex) {
    api_response($ex->getMessage(), "Có lỗi xảy ra, vui lòng thử lại.", 500);
}
?>