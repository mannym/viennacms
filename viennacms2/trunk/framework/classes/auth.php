<?php
define('ACL_SETTING_UNSET', 0);
define('ACL_SETTING_ALLOW', 1);
define('ACL_SETTING_DENY', 2);

class VAuth {
	private $acl = array();
	
	public function __construct() {
		if (!isset(cms::$config['admin_auth_upgraded'])) {
			$acl_entry = new VACLEntry();
			$acl_entry->initialize();
			$acl_entry->person = 'user:1';
			$acl_entry->resource = 'admin:all';
			$acl_entry->operation = '';
			$acl_entry->setting = ACL_SETTING_ALLOW;
			$acl_entry->write();
			
			cms::$config['admin_auth_upgraded'] = true;
		}
		
		$this->acl = $this->read_acl();
	}
	
	public function parse_acl_id($id) {
		$data = explode(':', $id, 2);
		
		return array(
			'context' => $data[0],
			'id' => $data[1]
		);
	}
	
	public function get_resource_handler($resource) {
		$resource_data = $this->parse_acl_id($resource);
		return cms::$registry->get_type($resource_data['context'], 'ResourceHandler');
	}
	
	private function read_acl() {
		$query = new VACLEntry();
		$query->cache = 3600;
		$entries = $query->read();
		
		$sorted_entries = array();
		
		/*foreach ($resources as $resource) {
			$handler = $this->get_resource_handler($resource);
			
			if ($handler) {
				$sorted_entries[$resource] = $handler->get_default_rights($resource);
			}
		}*/
		
		foreach ($entries as $entry) {
			$entry_resource = $entry->resource;
			$entry_person = $entry->person;
			$entry_operation = ($entry->operation) ? $entry->operation : 'default';
			
			if (!isset($sorted_entries[$entry_resource])) {
				$sorted_entries[$entry_resource] = array();
			}
			
			if (!isset($sorted_entries[$entry_resource][$entry_person])) {
				$sorted_entries[$entry_resource][$entry_person] = array();
			}
			
			$sorted_entries[$entry_resource][$entry_person][$entry_operation] = $entry->setting;
		}
		
		return $sorted_entries;
	}
	
	public function get_default($type, $resource, $person = '', $operation = '') {
		// TODO: error checking
		
		$handler = $this->get_resource_handler($resource);
			
		if ($handler) {
			$rightset = $handler->get_default_rights($resource);
			
			switch ($type) {
				case 'resource':
					return $rightset;
				case 'person':
					return (!empty($rightset[$person])) ? $rightset[$person] : false;
				case 'operation':
					return (!empty($rightset[$person][$operation])) ? $rightset[$person][$operation] : false;
			}
		}
		
		return false;
	}
	
	public function get_acl($resource, $person, $operation = 'default') {
		// we might need the values to be in order. luckily person objects should only be handled by one function :)
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
		
		if (!is_string($resource)) {
			$resource = $resource->to_acl_id();
		}
		
		$resource_data = $this->parse_acl_id($resource);
		
		$resource_handler = $this->get_resource_handler($resource);
		$resources = array($resource);
		
		if ($resource_handler) {
			$resources = $resource_handler->get_parents($resource);
			$resources[] = $resource;
			$resources = array_reverse($resources);
		}
		
		$resource_conditions = VCondition::list_to_equals($resources);
		$person_conditions = VCondition::list_to_equals($people);
		
		$setting = ACL_SETTING_UNSET;
		
		foreach ($resources as $resource) {
			if (empty($this->acl[$resource])) {
				$this->acl[$resource] = $this->get_default('resource', $resource);
				
				if (empty($this->acl[$resource])) {
					continue;
				}
			}
			
			foreach ($people as $person) {
				if (empty($this->acl[$resource][$person])) {
					$this->acl[$resource][$person] = $this->get_default('person', $resource, $person);
					
					if (empty($this->acl[$resource][$person])) {
						continue;
					}
				}
				
				if (empty($this->acl[$resource][$person][$operation])) {
					$this->acl[$resource][$person][$operation] = $this->get_default('person', $resource, $person, $operation);
					
					if (empty($this->acl[$resource][$person][$operation])) {
						continue;
					}
					
					continue;
				}
				
				$current_setting = $this->acl[$resource][$person][$operation];
				
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