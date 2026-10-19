<?php

ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/../error.log");

set_exception_handler(function (Throwable $exception) {
    error_log((string) $exception);

    if (!headers_sent()) {
        http_response_code(500);
    }

    exit("Something went wrong. Please try again later.");
});

$config = require __DIR__ . "/../config.php";
$db_host        = $config["host"];
$db_username    = $config["username"];
$db_password    = $config["password"];
$db_name        = $config["database"];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$con = new mysqli($db_host, $db_username, $db_password, $db_name);
