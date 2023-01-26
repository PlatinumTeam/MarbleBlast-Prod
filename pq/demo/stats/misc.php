<?php
	function splitCamelCaseStringToWordString($str) {
		// split into multiple words based on camel case.
		// http://stackoverflow.com/questions/4519739/split-camelcase-word-into-words-with-php-preg-match-regular-expression/4519809#4519809
		preg_match_all('/((?:^|[A-Z])[a-z]+)/', $str, $words);
		return ucwords(implode(" ", $words[0]));
	}
?>