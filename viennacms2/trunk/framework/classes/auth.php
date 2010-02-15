<?php
define('ACL_SETTING_UNSET', 0);
define('ACL_SETTING_ALLOW', 1);
define('ACL_SETTING_DENY', 2);

class VAuth {
	public function __construct() {
		if (!isset(cms::$config['admin_auth_upgraded'])) {
			$acl_entry = new VACLEntry();
			$acl_entry->initialize();
			$acl_entry->person = 'user:1';
			$acl_entry->resource = 'admin:all';
			$acl_entry->setting = ACL_SETTING_ALLOW;
			$acl_entry->write();
			
			cms::$config['admin_auth_upgraded'] = true;
		}
	}
	
	public function parse_acl_id($id) {
		$data = explode(':', $id, 2);
		
		return array(
			'context' => $data[0],
			'id' => $data[1]
		);
	}
	
	public function get_acl($resource, $person) {
		// we NEED the values to be in order. luckily person objects should only be handled by one function :)
		$person = $person->to_acl_id();
		$person_data = $this->parse_acl_id($person);
		$is_user = false;
		
		if ($person_data['context'] == 'user') {
			$is_user = true;
			
			if (!empty(cms::$user->user->user_permissions)) {
				$usercache = unserialize(cms::$user->user->user_permissions);
				if ($usercache !== false && isset($usercache[$resource])) {
					return ($usercache[$resource] == ACL_SETTING_ALLOW);
				}
			}
		}
		
		$people = VEvents::invoke('acl.person-parents', $person);
		$people[] = $person;
		$people = array_reverse($people);
		
		$resource_data = $this->parse_acl_id($resource);
		
		$resource_handler = cms::$registry->get_type($resource_data['context'], 'ResourceHandler');
		$resources = array($resource);
		
		if ($resource_handler) {
			$resources = $resource_handler->get_parents();
			$resources[] = $resource;
			$resources = array_reverse($resources);
		}
		
		$resource_conditions = VCondition::list_to_equals($resources);
		$person_conditions = VCondition::list_to_equals($people);
		
		$query = new VACLEntry();
		$query->person = new VCondition(VCondition::CONDITION_OR, $person_conditions);
		$query->resource = new VCondition(VCondition::CONDITION_OR, $resource_conditions);
		$entries = $query->read();
		
		$sorted_entries = array();
		
		foreach ($entries as $entry) {
			$entry_resource = $entry->resource;
			$entry_person = $entry->person;
			
			if (!isset($sorted_entries[$entry_resource])) {
				$sorted_entries[$entry_resource] = array();
			}
			
			$sorted_entries[$entry_resource][$entry_person] = $entry->setting;
		}
		
		$setting = ACL_SETTING_UNSET;
		
		foreach ($resources as $resource) {
			if (empty($sorted_entries[$resource])) {
				continue;
			}
			
			foreach ($people as $person) {
				if (empty($sorted_entries[$resource][$person])) {
					continue;
				}
				
				$current_setting = $sorted_entries[$resource][$person];
				
				switch ($current_setting) {
					case ACL_SETTING_ALLOW:
						if ($setting == ACL_SETTING_UNSET) { // if already allowed, there's no sense. if denied, so as well
							$setting = $current_setting;
						}
						break;
					case ACL_SETTING_DENY:
						$setting = $current_setting;
						break;
				}
			}
		}
		
		if ($is_user) {
			$usercache = array();
			
			if (!empty(cms::$user->user->user_permissions)) {
				$usercache = unserialize(cms::$user->user->user_permissions);
				
				if (!$usercache) {
					$usercache = array();	
				}
			}
			
			$usercache[$resource] = $setting;
			cms::$user->user->user_permissions = serialize($usercache);
		}
		
		return ($setting == ACL_SETTING_ALLOW);
	}
}