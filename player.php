<?php
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit('Forbidden: You must be logged in to access this content.');
}

// Ensure the config file exists before trying to include it.
if (!file_exists('config.php')) {
    http_response_code(500);
    exit('Server Error: Configuration file is missing.');
}
$config = include('config.php');

// --- Robust Path Construction ---
// 1. Ensure the configured music folder has one, and only one, trailing slash.
$music_folder = rtrim(trim($config['music_folder']), '/') . '/';

// 2. Get the requested filename and use basename() to prevent any directory traversal attacks (e.g., ../../etc/passwd).
$filename = basename($_GET['file']);

// 3. Combine them to get the full, safe path to the audio file.
$file_path = $music_folder . $filename;


// --- File Streaming Logic ---
if (file_exists($file_path) && is_readable($file_path)) {
    // Determine the MIME type. The 'fileinfo' PHP extension is required for this to work reliably.
    // If it's missing, the browser might not understand the content type.
    $mime_type = function_exists('mime_content_type') ? mime_content_type($file_path) : 'audio/mpeg';
    $file_size = filesize($file_path);

    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . $file_size);
    header('Accept-Ranges: bytes');
    header('Content-Disposition: inline; filename="' . $filename . '"');

    // Handle HTTP Range requests for seeking/skipping in the audio player
    $range = $_SERVER['HTTP_RANGE'] ?? null;
    if ($range) {
        list(, $range) = explode('=', $range, 2);
        list($start, $end) = explode('-', $range);
        $end = empty($end) ? $file_size - 1 : min($end, $file_size - 1);
        $start = empty($start) || $end < $start ? 0 : (int)$start;

        header('HTTP/1.1 206 Partial Content');
        header("Content-Range: bytes $start-$end/$file_size");
        $length = $end - $start + 1;
        header("Content-Length: $length");

        $file = fopen($file_path, 'rb');
        fseek($file, $start);
        echo fread($file, $length);
        fclose($file);
    } else {
        // If no range is requested, stream the whole file.
        readfile($file_path);
    }
    exit;
} else {
    // The requested file was not found, so display our custom 404 page.
    include '404.php';
    exit;
}