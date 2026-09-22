<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = $_SERVER['DOCUMENT_ROOT'] . $uri;

if (is_file($file)) {
    return false;
}

if (is_dir($file) && is_file(rtrim($file, '/') . '/index.php')) {
    return false;
}

http_response_code(404);
include $_SERVER['DOCUMENT_ROOT'] . '/includes/404.php';
