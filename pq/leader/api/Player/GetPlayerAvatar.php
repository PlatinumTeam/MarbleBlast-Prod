<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$username = param("user") ?? Login::getCurrentUsername();

//Get their id
$id = JoomlaSupport::getUserId($username);
$avatar = JoomlaSupport::getUserAvatarPath($id);

//Default avatar if they don't have one
if ($avatar != "") {
	//Actual path
	$avatar = JOOMLA_BASE . "/media/kunena/avatars/$avatar";

	//Make sure it exists
	if (is_file($avatar)) {
		$tmp = tempnam(sys_get_temp_dir(), "avatar.png");
		$im = new Imagick($avatar);

		$size = max($im->getImageWidth(), $im->getImageHeight());
		$offX = intval(($size - $im->getImageWidth()) / 2);
		$offY = intval(($size - $im->getImageHeight()) / 2);

		$canvas = new Imagick();
		$canvas->newImage($size, $size, new ImagickPixel("#ffffff00"));
		$canvas->compositeImage($im, Imagick::COMPOSITE_DEFAULT, $offX, $offY);
		$canvas->flattenImages();
		$canvas->resizeImage(72, 72, Imagick::FILTER_LANCZOS, 1, false);
		$canvas->setImageFormat("PNG");
		$canvas->writeImage($tmp);

		//Get MIME-Type
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$type  = finfo_file($finfo, $tmp);
		finfo_close($finfo);

		//Spit it out
		$contents = file_get_contents($tmp);

		$hash = hash("sha256", $contents);

		switch ($type) {
		case "image/jpeg":
			$extension = ".jpg";
			break;
		case "image/bmp":
			$extension = ".bmp";
			break;
		case "image/gif":
			$extension = ".gif";
			break;
		default:
			$extension = ".png";
			break;
		}

		$output = [
			"username" => $username,
			"filename" => "avatar{$id}{$extension}",
			"hash"     => $hash,
			"contents" => tbase64_encode($contents)
		];

		unlink($tmp);

		techo(json_encode($output));
	} else {
		//Not found
		techo(json_encode([
			"error" => "No user"
		]));
	}
} else {
	//No avatar
	techo(json_encode([
		"error" => "No avatar"
	]));
}