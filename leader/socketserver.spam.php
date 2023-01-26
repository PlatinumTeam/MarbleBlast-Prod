<?php

require("socketserver.profanity.php");

class SpamFilter {
	/**
	 * How many times the player has been muted for spamming
	 * @var timesMuted
	 */
	public $timesMuted;

	/**
	 * How long the player has been muted for
	 * @var muteTime
	 */
	public $muteTime;

	/**
	 * Older messages from this client
	 * @var $previousMessages
	 */
	private $previousMessages;

	/**
	 * A multiplier for the mute index
	 * @var $muteMultiplier
	 */
	private $muteMultiplier;

	function __construct($muteMultiplier) {
		$this->loginTime = gettimeofday(true);
		$this->previousMessages = array();
		$this->muteMultiplier = $muteMultiplier;
	}

	function chatSpam(&$message) {
		$time = gettimeofday(true);
		$delta = $time - $this->lastChat;

		//Record when their last chat was
		$this->lastChat = $time;

		//How many chats we've sent
		$this->chatCount ++;

		//If you send messages too quickly, your multiplier is higher
		$multiplier = 0.08;
		if ($delta < 1) {
			$multiplier = 0.16;
		}

		//All-caps (or all-number, but oh well)
		if (strtoupper($message) == $message) {
			//Yeah stop yelling
			$multiplier *= 2;
		}

		//If you've been muted a lot, this goes up too
		$multiplier *= 1 + ($this->muteTime / ($time - $this->loginTime));

		//If you have a long string of all the same character, say goodbye
		$uniques = strlen(count_chars($message, 3));

		//I'm going to be fairly lenient about this one, if you have more than a 1/10 ratio you should be fine.
		$multiplier *= 1 + pow((strlen($message) / $uniques) / 10, 2);

		//Short messages are annoying as hell, similarly but less so for long messages.
		//I did a quick graph in GeoGebra and got this:
		//y = 0.00002 * (x-100)^2 + 1
		//EDIT 9/11/14 - Much more lenient than before
		$multiplier *= (0.00002 * pow(strlen($message) - 100, 2)) + 1;

		//This gives 1 multiplier at 50 chars, ~1.4 at 100, and a shitton at anything over 255

		//If your message is supershort, you deserve to die
		if (strlen($message) < 10) {
			$multiplier *= (1 + (1.5 / strlen($message)));
		}

		//This is nice, as it gives 2-letter messages a $multiplier of 0.25
		//You shouldn't need more than one of those every 2.5 seconds, right? I hope so.
		//It gives a 0.37 $multiplier for 1-letter messages... very nice

		//1 for all messages so everything gets a least a little spam
		$base = 1;

		//Detect for any profanities in their message
		$profanities = $this->detectProfanities($message);

		//echo("profanity count: $profanities\n");

		//Keep a record of how many they use (because I get pissed off)
		$this->profanities += $profanities;

		//And factor that in (this will probably ban jeff more than it needs to)
		$profanities += pow($profanities, 2) * ($this->profanities / $this->chatCount);

		//echo("adjusted count: $profanities\n");

		$multiplier *= ($profanities + $base);

		//Mods/admins get more leeway
		$multiplier *= $this->muteMultiplier;

		//Make sure they're not just repeating the same damn thing
		$previous = array_count_values($this->previousMessages);
		$previous = $previous[strtolower($message)];

		//Give them some leeway
		$multiplier *= ($previous * 0.5) + 1;

		//Add this message to their previous messages
		array_push(strtolower($message), $this->previousMessages);

		//Max of 20 messages to be nice
		if (count($this->previousMessages) > 20)
			array_shift($this->previousMessages);

		//Determine final spam amount
		return $multiplier;
	}

	function detectProfanities(&$message) {
		//The dirty work gets done in this function... avert your eyes, small children!
		$count = 0;

		//Fill this array with everything your mother taught you not to say
		//Creds to http://www.noswearing.com/dictionary, even I don't know what some of these are (although they do provide descriptions... oooh)
		$profanities = profanities();

		$found = array();

		//Basic loop for now, nothing too fancy
		foreach ($profanities as $profanity => $value) {

			//Make sure we're not using any twice
			$didfind = false;
			foreach ($found as $checkprof => $checkcount) {
				if (strpos($profanity, $checkprof) !== false ||
					 strpos($checkprof, $profanity) !== false) {

					//echo("Found substr $profanity $checkprof, $value, $profanities[$checkprof]\n");

					//If that one is worse, nail em for it
					if ($profanities[$checkprof] >= $value) {
						$didfind = true;
						//echo("Skipping $profanity, already have $checkprof\n");
						break;
					}
				}
			}

			if ($didfind)
				continue;

			$instances = substr_count(strtolower($message), strtolower($profanity)) * $value;
			if ($instances) {
				//echo("Found $profanity\n");
				$count += $instances;
				$found[$profanity] = $count;
			}
		}

		return $count;
	}

	function detectPolitics(&$message) {
		//The dirty work gets done in this function... avert your eyes, small children!
		$count = 0;
		$politics = politics();

		$found = array();
		foreach ($politics as $politic => $value) {
			$didfind = false;
			foreach ($found as $checkpol => $checkcount) {
				if (strpos($politic, $checkpol) !== false ||
					 strpos($checkpol, $politic) !== false) {

					// echo("Found substr $politic $checkpol, $value, $politics[$checkpol]\n");

					//If that one is worse, nail em for it
					if ($politics[$checkpol] >= $value) {
						$didfind = true;
						// echo("Skipping $politic, already have $checkpol\n");
						break;
					}
				}
			}

			if ($didfind)
				continue;

			$instances = substr_count(strtolower($message), strtolower($politic)) * $value;
			if ($instances) {
				// echo("Found $politic\n");
				$count += $instances;
				$found[$politic] = $count;
			}
		}

		return $count;
	}
}

?>