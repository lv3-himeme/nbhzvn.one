<?php
include __DIR__ . "/config.php";
$conn = new mysqli($db_host, $db_username, $db_password, $db_database);
if ($conn->connect_error) die(); // TODO
?>