<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri === '/' || !file_exists(__DIR__ . '/' . $uri)) {
    $indexFile = __DIR__ . '/index.html';
    if (file_exists($indexFile)) {
        readfile($indexFile);
    } else {
        http_response_code(404);
        echo "index.html not found";
    }
}