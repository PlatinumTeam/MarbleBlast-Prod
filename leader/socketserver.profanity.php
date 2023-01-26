<?php
/*
 * Why would anyone deliberately open this file... -HiGuy
 */

function politics() {
	return array(
		"liberal"        => 1, //Jeff needs to stop talking about politics
		"conservative"   => 1,
		"democrat"       => 1,
		"republican"     => 1,
		"libertarian"    => 1,
		"congress"       => 1,
		"government"     => 1,
		"sean hanity"    => 1,
		"o'reilly"       => 1,
		"bernie"         => 1,
		"sanders"        => 1,
		"abortion"       => 1,
		"birth control"  => 1,
		"contraception"  => 1,
		"hamas"          => 1,
		"obama"          => 1,
		"hillary"        => 1,
		"clinton"        => 1,
		"cenk ungyr"     => 1,
		"bengahzi"       => 1,
		"nixon"          => 1,
		"reagan"         => 1,
		"george bush"    => 1,
		"al franken"     => 1,
		"senator"        => 1,
		"constitution"   => 1,
		"immigration"    => 1,
		"assimilate"     => 1,
		"private sector" => 1,
		"subsidised"     => 1,
		"subsidy"        => 1,
		"healthcare"     => 1,
		"health care"    => 1,
		"medicare"       => 1,
		"obamacare"      => 1,
		"right-wing"     => 1,
		"right wing"     => 1,
		"left-wing"      => 1,
		"left wing"      => 1,
		"leftism"        => 1,
		"leftist"        => 1,
		"rightism"       => 1,
		"rightist"       => 1,
		"centrism"       => 1,
		"centrist"       => 1,
		"communist"      => 1,
		"communism"      => 1,
		"socialism"      => 1,
		"socialist"      => 1,
		"totalitarian"   => 1,
		"dictator"       => 1,
		"discrimination" => 1,
		"islam"          => 1,
		"SJW"            => 1,
		"muslim"         => 1,
		"gay marriage"   => 1,
		"drug reform"    => 1,
		"economy"        => 1,
		"economics"      => 1,
		"oppress"        => 1,
		"bush"           => 1,
		"political"	  => 1,
		"politician"	  => 1,
		"candidate"	  => 1,
		"isis"           => 1,
		"carson"         => 1,
		"trump"          => 1,
		"rubio"          => 1,
		"cruz"           => 1,
		"kasich"         => 1,
		"primary"        => 1,
		"election"       => 1,
		"delegate"       => 1
	);
}

function profanities() {
	/* 
	 * "Levels of "severity" aka how much I'll hate you if you say one of these in chat:
	 * 0 - no effect
	 * 1 - meh, not that upset
	 * 2 - keep it civil, we have lots of children here
	 * 3 - instant you're-boned
	 * 4+ - see 3
	 *
	 * If you ever feel the urge to add something to here, go right ahead. Syntax is exactly as you can see. Anyone caught adding
	 *  something non-expletive like "jeff's face" will be burned at the stake. That is all.
	 */
	return array(
		"fuck" => 3,
		"shit" => 1,
		"damn" => 1,
		"cunt" => 3,
		"bitch" => 2,
		"fag" => 2,
	    "ass" => 1,
	    "clit" => 2,
	    "cock" => 1,
	    "dick" => 2,
	    "douche" => 2,
	    "nigger" => 3,

	    "hoh" => 1.5,
	    "joj" => 1.5,

		//No linking that discord chat
	    "discord.gg/" => 3,
		"010rAuh9AdkTZVlFS" => 3,

		//Because I know somebody is going to try and evade the mute filter
		"010rAuh9" => 3,
		"AdkTZVlFS" => 3,
		"010rA" => 3,
		"uh9Adk" => 3,
		"TZVlFS" => 3
	);
}

function getInstaBanRegexes() {
	return array(
		"/\bn+i+g+(e+r+|a+)s+?\b/",
	);
}