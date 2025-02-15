<?php

if (!isset($_GET["uid"]) || !isset($_GET["pid"]) || !isset($_GET["bn"])) {
    echo "paramètres invalides";
}

$uid = $_GET["uid"];
$pid = $_GET["pid"];
$bn = $_GET["bn"];
$filepath = "./users/$uid/$pid/$bn";

if (!file_exists($filepath)) {
    echo "fichier non trouvé";
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimetype = finfo_file($finfo, $filepath);
finfo_close($finfo);

header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
header("Cache-Control: public");
header("Content-Type: " . $mimetype);
header("Content-Transfer-Encoding: Binary");
header("Content-Length:" . filesize($filepath));
header("Content-Disposition: attachment");
readfile($filepath);
exit;

?>
