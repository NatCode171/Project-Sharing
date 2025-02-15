<?php
if (!file_exists("videocache/$targetUser_id.txt")) {
    file_put_contents("videocache/$targetUser_id.txt", '0;0');
}

$cache = explode(";", file_get_contents("videocache/$targetUser_id.txt"));

if (time() - $cache[0] > 3600) {
    $response = file_get_contents("https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=$targetIdChannelYt&type=video&maxResults=1&order=date&key=***");
    $videoId = json_decode($response, true)['items'][0]['id']['videoId'];
    file_put_contents("videocache/$targetUser_id.txt", time() . ";" . $videoId);
} else {
    $videoId = $cache[1];
}

?>
<iframe id="MainPlayer" frameborder="0" allowfullscreen 
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
    title="YouTube video player" width="620" height="360" src="https://www.youtube.com/embed/<?php echo $videoId; ?>">
</iframe>

<?php exit(); ?>

