<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

techo(json_encode([
	"result" => "false",
	"error" => "In-game signup disabled.\nGo to <a:marbleblast.com>marbleblast.com</a> for now"
]));
die();

//DRY, right? Well turns out that doesn't work when your site uses Captcha.
// Ugh. This file is a shit hole, I just hope it never breaks.

//See components/com_users/models/registration.php if it does in fact break

$username = requireParam("username");
$password = requireParam("password");
$email    = requireParam("email");

// Get the user data.
$requestData = array(
	"name"      => $username,
	"username"  => $username,
	"password1" => $password,
	"password2" => $password,
	"email1"    => $email,
	"email2"    => $email,
);

$error = "";
$result = tryRegisterUser($requestData, $error);
techo(json_encode([
	"result" => $result,
	"error" => $error
]));

/* The rest of this file is Extremely GPL and has therefore been omitted :) */
