<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

// Handle the request based on the URL
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Remove the script name from the request URI if present
$base_path = str_replace('index.php', '', $script_name);
$path = str_replace($base_path, '', $request_uri);

// Remove query string if present
if (($pos = strpos($path, '?')) !== false) {
    $path = substr($path, 0, $pos);
}

// Route the request
switch ($path) {
    case '/':
    case '/index.html':
        include __DIR__ . '/../index.html';
        break;
        
    case '/login':
    case '/login.php':
        include __DIR__ . '/../templates/login.php';
        break;
        
    case '/handlers/login-handler.php':
        include __DIR__ . '/../handlers/login-handler.php';
        break;
        
    // Add more routes as needed
        
    default:
        // Try to include the file directly
        $file_path = __DIR__ . '/..' . $path;
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
        break;
}
