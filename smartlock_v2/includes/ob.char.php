<?php

/*
 * Standard USER OBJECT module.
 * This is the skeleton of a visiter object and other possible variations.
 * It is supposed to be inherited or instanciated before use.
 */

class CharOB
{
	// All user related date is stored here.
	var $data = array(
		'id'	=> 0
	);

	// Default object constructing function.
	function __construct($uid=0) {
		if ( $uid )
			$this->load_user($uid);
	}

	// Load a user by it's id.
	function load_user($uid) {
		global $DB, $SYS;

		$cdata = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."user WHERE id = ".$uid);

		if ( !$cdata )
			return FALSE;

		$cdata = clean_array($cdata);
		$this->data = array_merge($this->data, $cdata);

		return TRUE;
	}
}

?>