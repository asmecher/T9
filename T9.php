<?php
/**
 * T9 Misunderstanding Generator
 * Copyright (c) 2012 by Alec Smecher
 *
 * Given text input, generate all the possible T9 (predictive texting)
 * misunderstandings that might come from a phone's auto-suggest, using a
 * dictionary text file as a source of possible misunderstandings.
 *
 * Distributed under the GNU GPL v2. For full terms see the file COPYING.
 */

define('DICTIONARY_PATH', '/usr/share/dict/american-english');

class T9Confuzzler {
	/** @var $dictionary array Array of T9-hashed word lists for finding collisions */
	var $dictionary;

	/**
	 * Constructor
	 */
	function T9Confuzzler() {
		$this->dictionary = array();
	}

	/**
	 * Translate a word into its T9 equivalent (that is, what you'd type on the keypad)
	 * @param $word string
	 * @return string
	 */
	static function calculateT9($word) {
		return strtr(strtoupper($word), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', '22233344455566677778889999');
	}

	/**
	 * Add a word list to the current set.
	 * @param $filename string Filename containing word list, one word per line.
	 */
	function addDictionary($filename) {
		foreach (file($filename) as $word) {
			$word = trim($word);
			$this->dictionary[$this->calculateT9($word)][] = $word;
		}
	}

	/**
	 * Get the potential misunderstandings of a word, if any.
	 * @param $word string Single T9 word
	 * @param $dictionary Lookup array of potential misunderstandings in T9 format
	 * @return string "{A, B, C}" if A, B, C are potential misunderstandings; "A" if none possible
	 */
	function getMisunderstandings($word) {
		$t9 = $this->calculateT9($word);
		if (isset($this->dictionary[$t9])) {
			return '{' . join(', ', $this->dictionary[$t9]) . '}';
		} else {
			return $word;
		}
	}

	/**
	 * Replace misunderstandings in a string; this function is only useful
	 * as a callback for preg_match_callback.
	 * @param $match array
	 * @return string
	 */
	function replace_callback($match) {
		return $this->getMisunderstandings($match[0], $this->dictionary);
	}
};

// Load and store the dictionary, T9'ed, as a lookup table.
$t9Confuzzler = new T9Confuzzler();
$t9Confuzzler->addDictionary(DICTIONARY_PATH);

// While there's data on standard input, confuzzle it.
$fp = fopen('php://stdin', 'r');
while (!feof($fp)) {
	echo preg_replace_callback('/[a-zA-Z]+/', array($t9Confuzzler, 'replace_callback'), fgets($fp));
};
fclose($fp);

?>
