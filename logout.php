<?php
setcookie("nbhzvn_username", "", time() - 3600, "/", "." . get_root_domain());
setcookie("nbhzvn_login_token", "", time() - 3600, "/", "." . get_root_domain());
setcookie("nbhzvn_username", "", time() - 3600);
setcookie("nbhzvn_login_token", "", time() - 3600);
header($_GET["speedrun"] ? "Location: https://speedrun.nbhzvn.one" : "Location: /");
?>