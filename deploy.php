<?php
require "api/functions.php";
$token = $_ENV["DEPLOYMENT_TOKEN"];
$header = apache_request_headers()["Authorization"] ?? "";
if ($header !== "Bearer $token") {
    http_response_code(403);
    exit("Unauthorized");
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    exit("No file uploaded.");
}

$zipPath = __DIR__ . '/_deploy.zip';
move_uploaded_file($_FILES['file']['tmp_name'], $zipPath);

$zip = new ZipArchive();
if ($zip->open($zipPath) === true) {
    $zip->extractTo(__DIR__);
    $zip->close();
    unlink($zipPath);
    echo "Deployment successful.";
} else {
    http_response_code(500);
    echo "Failed to extract zip.";
}
?>