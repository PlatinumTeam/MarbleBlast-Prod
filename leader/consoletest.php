<?php

$dir = date("Y");
$zipDir =  __DIR__ . "/consoles/" . date("Y");

if (!is_dir($zipDir)) {
	mkdir($zipDir, 0775, true);
}

$issueTitle = "Crash Report: " . date("Y-m-d H:i:s");
$issueBody = "";
$createIssue = false;
foreach ($_FILES as $name => $file) {
	$start = ($name === "crash" ? "crash" : "console");
	$name = date("Y-m-d_H-i-s") . "-{$start}.zip";
	$dir = date("Y");
	$path =  __DIR__ . "/consoles/" . $dir . "/" . $name;

	if (move_uploaded_file($file["tmp_name"], $path)) {
		echo("File sent. Thanks for helping us improve PQ.\n");
		$issueBody .= "$start.log: https://marbleblast.com/leader/consoles/$dir/$name\n";
		$createIssue = true;
	} else {
		echo("Error sending file. Unknown server error.\n");
	}
}

if ($createIssue) {
    $issue = createGitlabIssue($issueTitle, $issueBody);
    discordPost(XXXXXXXXXXXXXXXXXXX, "Crash Report #{$issue["iid"]}", "<https://git.marbleblast.com:37794/PlatinumTeam/PQ-Crashes/issues/{$issue["iid"]}>");
}

function createGitlabIssue($title, $body) {
    if (!function_exists("curl_init")) {
        return;
    }
    $data = json_encode([
        "id" => 13,
        "title" => $title,
        "description" => $body,
        "labels" => "Bot Added"
    ]);
    $headers = ["PRIVATE-TOKEN: XXXXXXXXXXXXXXXXXXXX", "Content-Type: application/json"];
    $curl = curl_init("https://git.marbleblast.com:37794/api/v4/projects/13/issues");
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($curl);
    curl_close($curl);

    return json_decode($result, true);
}

function discordPost($channel, $title, $body) {
	if (!function_exists("curl_init")) {
		return false;
	}

	$token = "Bot XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
	$message = "**$title**\n$body";

	$data = json_encode(["content" => $message]);
	$headers = ["Authorization: " . $token, "Content-Type: application/json"];
	$curl = curl_init("https://discordapp.com/api/channels/" . $channel . "/messages");
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	$result = curl_exec($curl);
	curl_close($curl);
}
