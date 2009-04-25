<?php
/**
 * event handler class
 */
abstract class VEvents/* extends VObject*/ {
	/**
	 * we do not want this to be instantiatable
	 */
	private function __construct() { }
	
	private static $handlers = array();
	
	public static function register(string $event_name, callback $event_callback) {
		if (!isset(self::$handlers[$event_name])) {
			self::$handlers[$event_name] = array();
		} else if (in_array($event_callback, self::$handlers[$event_name])) {
			return false;
		}
		
		self::$handlers[$event_name][] = $event_callback;
		
		return true;
	}
	
	public static function unregister(string $event_name, $event_callback = false) {
		if ($event_callback === false) {
			self::$handlers[$event_name] = array();
		} else if (!empty(self::$handlers[$event_name])) {
			// as PHP doesn't have a IEnumerable.Delete method like in C#
			// we loop through the list manually.
			
			foreach (self::$handlers[$event_name] as $i => $event) {
				if ($event === $event_callback) {
					unset(self::$handlers[$event_name][$i]);
				}
			}
		}
	}
	
	public static function invoke(string $event_name) {
		if (!empty(self::$handlers[$event_name])) {
			$args = func_get_args();
			array_shift($args);
			
			$results = array();
			
			foreach (self::$handlers[$event_name] as $handler) {
				$result = call_user_func_array($handler, $args);
				
				if (is_array($result)) {
					$results = array_merge($results, $result);
				} else {
					$results[] = $result;
				}
			}
			
			return $results;
		} else {
			return array();
		}
	}
}