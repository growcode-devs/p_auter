<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include(__DIR__ . '/spotify_auth.php');

$artist_id = '3BKSXPjZx4LSMToAoqQGEK';
$access_token = getAccessToken();

if (!$access_token) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unable to fetch Spotify access token']);
    exit;
}

$url = "https://api.spotify.com/v1/artists/$artist_id/top-tracks?market=US";
$options = [
    'http' => [
        'header' => "Authorization: Bearer $access_token",
        'method' => 'GET'
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unable to fetch data from Spotify API']);
    exit;
}

$decodedResponse = json_decode($response, true);

if (!isset($decodedResponse['tracks']) || empty($decodedResponse['tracks'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No tracks data returned from Spotify API']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['tracks' => $decodedResponse['tracks']]);
