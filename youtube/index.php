<?php
require_once "../vendor/autoload.php";

$url = "https://www.googleapis.com/youtube/v3/playlistItems";
$playlist_id = $_REQUEST['playlist'] ?? "PLV681LxQUUTPwZB_FzbCR3Q-bW0uzc3Pf";
$key = "AIzaSyDQee1RVHVMBtE-I-QdgvFxtErLddXf8gw";
$full_url = "$url?part=snippet%2CcontentDetails&maxResults=25&playlistId=$playlist_id&key=$key";

$client = new Google_Client();
$client->setApplicationName("dobuki-net");
$client->setDeveloperKey($key);
$service = new Google_Service_YouTube($client);

function playlistItemsListByPlaylistId($service, $part, $params) {
    $params = array_filter($params);
    $response = $service->playlistItems->listPlaylistItems(
        $part,
        $params
    );

    $result = [];
    foreach ($response['items'] as $item) {
        if ($item['snippet']['title']==='Private video') {
            continue;
        }
        if ($item['snippet']['resourceId']['kind']=='youtube#video') {
            $result []= [
                'thumbnail' => $item['snippet']['thumbnails'],
                'url' => $item['snippet']['thumbnails']['medium']['url'] ?? $item['snippet']['thumbnails']['default']['url'],
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'id' => $item['snippet']['resourceId']['videoId'],
            ];
        }
    }

    return $result;
}

function getVideos($service, $playlist_id) {
    return playlistItemsListByPlaylistId($service,
        'snippet',
        [
            'maxResults' => 48,
            'playlistId' => $playlist_id,
        ]
    );
}

$videos = getVideos($service, $playlist_id);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
echo json_encode($videos, JSON_PRETTY_PRINT);
