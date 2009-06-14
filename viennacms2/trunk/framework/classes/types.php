<?php
interface DataType {
	static function is_valid($object);
}

class String implements DataType {
	static function is_valid($object) {
		return is_string($object);
	}
	
	static function ends_with($string, $with) {
		return (substr($string, -strlen($with)) == $with);
	}
}

class Integer implements DataType {
	static function is_valid($object) {
		return is_numeric($object);
	}
}

class Float implements DataType {
	static function is_valid($object) {
		return is_float($object);
	}
}

class Boolean implements DataType {
	static function is_valid($object) {
		return is_bool($object);
	}
}

class Callback implements DataType {
	static function is_valid($object) {
		return is_callable($object);
	}
}

class ViewNotFoundException extends Exception {}
//class InvalidArgumentException extends Exception {}

class TypeCheck {
	const TYPEERROR = '/^Argument (\d)+ passed to (?:(\w+)::)?(\w+)\(\) must be an instance of (\w+), (\w+) given/';
	
	public static function get_argument_from_trace($backtrace, $function, $argument, &$value) {
		foreach ($backtrace as $element) {
			if (!empty($element['function']) && $element['function'] == $function) {
				$value = $element['args'][$argument - 1];
				
				return true;
			}
		}
		
		return false;
	}
	
	public static function handle_typehinting($error_type, $error_message) {
		if ($error_type == E_RECOVERABLE_ERROR) {
			if (preg_match(TypeCheck::TYPEERROR, $error_message, $matches)) {
				$we_want = $matches[4]; // the type we want
				$function = $matches[3];
				$argument = $matches[1];
				
				if (method_exists($we_want, 'is_valid')) {
					// this is a type we can handle
					$backtrace = debug_backtrace(); // we need this to get the parameters
					$argument_value = null;
					
					if (self::get_argument_from_trace($backtrace, $function, $argument, $argument_value)) {
						if (call_user_func(array($we_want, 'is_valid'), $argument_value)) {
							return true; // we are safe
						}
					}
				}
				
				throw new Exception($error_message);
			}
		}
		
		return false;
	}
}