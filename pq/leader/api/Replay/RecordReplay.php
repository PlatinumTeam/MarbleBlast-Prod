<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();

$mission = Mission::getByParams();
if ($mission === null) {
	error("Need mission");
}
$conts = requireParam("conts");
$decoded = base64_decode($conts);

$tmp = tempnam(sys_get_temp_dir(), "replay-" . $mission->id);
file_put_contents($tmp, $decoded);

$dest = BASE_DIR . "/data/Replay/" . $mission->id . ".rrec.zip";
$back1  = BASE_DIR . "/data/Replay/" . $mission->id . ".1.rrec.zip";
$back2  = BASE_DIR . "/data/Replay/" . $mission->id . ".2.rrec.zip";

if (is_file($back1)) {
    copy($back1, $back2);
}
if (is_file($dest)) {
    copy($dest, $back1);
}

$archive = new ZipArchive();
$archive->open($dest, ZipArchive::OVERWRITE | ZipArchive::CREATE);
$archive->addFile($tmp, $mission->id . ".rrec");
$archive->close();
unset($archive);

unlink($tmp);

techo("RECORDED");
