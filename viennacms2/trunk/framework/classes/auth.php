<?php
define('AUTH_USER', 1);
define('AUTH_GROUP', 2);
define('AUTH_ALL', 4);

class Auth {
	function parse_permission_string($string) {
		if (empty($string)) {
			return array(
				'owner' => array(),
				'group' => array(),
				'other' => array()
			);
		}
		
		// determine length of every part of the string, and split the string in 3 parts
		$partlen = strlen($string) / 3;
		$parts = str_split($string, $partlen);
		
		// rename the parts, and create an array
		$return = array();
		foreach ($parts as $i => $part) {
			$part = str_split($part, 1); // every character needs to be its own array element
			
			switch ($i) {
				case 0: // owner, first
					$return['owner'] = $part;
				break;
				case 1: // second is group
					$return['group'] = $part;
				break;
				case 2: // and third 'world'
					$return['other'] = $part;
				break;
			}
		}
		
		return $return;
	}
	
	function make_permission_string($array) {
		$return = '';
		
		foreach ($array as $parts) {
			foreach ($parts as $part) {
				$return .= $part;
			}
		}
		
		return $return;
	}
	
	function get_rights($resource, $object) {
		// is the object an user, or a group?
		switch (get_class($object)) {
			case 'User':
				$check = AUTH_USER | AUTH_GROUP | AUTH_ALL;
			break;
			case 'Group': // not yet implemented
				$check = AUTH_GROUP | AUTH_ALL;
			break;
			default: // in case of null, break glass
				$check = AUTH_ALL;
			break;
		}
		
		// read the database
		$data = new Permission_Object();
		$data->resource = $resource;
		$data->read(true);
		
		// get the keys to check
		$array_keys = array();
		
		if (($check & AUTH_USER) && $data->owner_id == $object->user_id) { // if this is user-specific, and it's this user...
			$array_keys[] = 'owner'; // we'll check the 'owner' key first.
		}
			
		//if (($check & AUTH_GROUP)) {
			
		//}
		
		$array_keys[] = 'other';
		
		// parse the string
		$rights = $this->parse_permission_string($data->permission_mask);
		$results = array();
		foreach ($array_keys as $key) {
			foreach ($rights[$key] as $right) { // read the rights for this key
				if ($right != '-' && !in_array($right, $results)) { // if it's allowed, and not alredy in the list...
					$results[] = $right; // add it!
				}
			}
		}
		
		return $results;
	}
}