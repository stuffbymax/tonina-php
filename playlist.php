<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    exit("Forbidden");
}

$playlists_file = 'data/playlists.json';
$playlists = json_decode(file_get_contents($playlists_file), true);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $playlists[] = ['name' => $_POST['name'], 'tracks' => $_POST['tracks']];
}

if ($action === 'delete' && isset($_GET['id'])) {
    array_splice($playlists, (int)$_GET['id'], 1);
}

file_put_contents($playlists_file, json_encode($playlists, JSON_PRETTY_PRINT));
header('Location: panel_admin.php');
exit;