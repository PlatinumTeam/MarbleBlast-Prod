<?php

class DiscordLink {
	/* @var DiscordLink $instance */
	private static $instance = null;

	/* @var string $token */
	private $token;

	public function __construct($token) {
		$this->token = $token;
	}

	private function curlDiscord($endpoint) {
		$headers = ["Authorization: " . $this->token, "Content-Type: application/json"];
		$curl = curl_init("https://discordapp.com/api$endpoint");
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		return $curl;
	}

	public function sendMessage(string $channel, string $message) {
		if (!function_exists("curl_init")) {
			return false;
		}
		$data = json_encode(["content" => $message]);
		$curl = $this->curlDiscord("/channels/" . $channel . "/messages");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($curl);
		curl_close($curl);
		return json_decode($result, true);
	}

	public function sendMessageEmbed(string $channel, $data) {
		if (!function_exists("curl_init")) {
			return false;
		}
		$data = json_encode($data);
		$curl = $this->curlDiscord("/channels/" . $channel . "/messages");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($curl);
		curl_close($curl);
		return json_decode($result, true);
	}

	public function addReaction(string $channel, string $message, string $reaction) {
		if (!function_exists("curl_init")) {
			return false;
		}
		$reaction = urlencode($reaction);
		$curl = $this->curlDiscord("/channels/$channel/messages/$message/reactions/$reaction/@me");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS, "");
		$result = curl_exec($curl);
		curl_close($curl);
		return json_decode($result, true);
	}

	public static function escapeMessage($text) {
		return str_replace(["\\", "_", "*", "~", "-", "`", ":", "|", "@", "<"], ["\\\\", "\\_", "\\*", "\\~", "\\-", "\\`", "\\:", "\\|", "@﻿", "<﻿"], $text);
	}

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new DiscordLink("Bot XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX");
		}
		return self::$instance;
	}
}
