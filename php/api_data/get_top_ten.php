<?php
require_once __DIR__ . '/get_top_songs.php';
header('Content-Type: application/json');

$data = get_top_ten();

echo json_encode(['tracks' => $data]);
