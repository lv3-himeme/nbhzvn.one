<?php
setcookie("nbhzvn_username", "", time() - 3600);
setcookie("nbhzvn_login_token", "", time() - 3600);
header("Location: /");
?>