<?php
class cms {
	static $db;
	static $vars;
	static $user;
	static $manager;
	static $router;
	static $layout;
	
	public static function register($name, $object) {
		self::$$name = $object;
	}
}
