<?php
include __DIR__ . "/connection.php";

function db_query($query, ...$args) {
    global $conn;
    global $log_path;
    $tmp = $conn->prepare($query);
    $type = "";
    $json = json_encode($args);
    $log_file = fopen($log_path, "a");
    fwrite($log_file, "\n" . "[" . date("d/m/Y H:i:s", time() + 25200) . "] " . $query . "; args = " . json_encode($json));
    fclose($log_file);
    for ($i = 0; $i < func_num_args() - 1; $i++) $type .= "s";
    if (func_num_args() >= 2) $tmp->bind_param($type, ...$args);
    $tmp->execute();
    return $tmp->get_result();
}