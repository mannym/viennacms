<?php
define('ALIAS_AUTO_DELETABLE', 1);
define('ALIAS_URL_DEFAULT', 2);

class Router {
	public $routes;
	public $parts;
	public $query;
	public $aliases = array();
	public $paths = array();
	
	public function __construct() {
		include(ROOT_PATH . 'framework/config/router.php');
		$this->routes = $routes;
		
		if (!defined('MINIMAL')) {
			$alias = new URL_Alias();
			$aliases = $alias->read();
			
			foreach ($aliases as $alias) {
				$this->aliases[$alias->alias_url] = $alias->alias_target;
				
				if ($alias->alias_flags & ALIAS_URL_DEFAULT) {
					$this->paths[$alias->alias_target] = $alias->alias_url;
				}
			}
		}
	}
	
	public function route($query) {
		$this->query = $query;
		$this->query = $this->check_alias();
		$this->match_parts();
	}
	
	private function check_alias($url = '', $depth = 5) {
		$url = (!empty($url)) ? $url : $this->query;

		if (isset($this->aliases[$url]) && $depth > 0) {
			$depth--;
			$url = $this->check_alias($this->aliases[$url], $depth);
		}
		
		return $url;
	}
	
	private function match_parts() {		
		foreach ($this->routes as $regex => $mapping) {
			if (preg_match($regex, $this->query, $regs)) {
				$parts = array();
				
				foreach ($mapping as $key => $part) {
					$parts[$part] = $regs[$key + 1];
				}
				
				$this->parts = $parts;
				break;
			}
		}
	}
	
	/**
	* @todo figure out why unique_target can be used, or some other stuff with that strangeness.......
	*/ 
	
	public function add_url_alias($from, $to, $default = false, $unique_target = true) {
		if ($default && $unique_target) {
			throw new viennaCMSException('The parameters default and unique_target can not be both set to true.');
		}
		
		$alias = new URL_Alias();
		$alias->alias_target = $to;
		$alias->read(true);
			
		if ($unique_target && !empty($alias->alias_url)) {
			if ($alias->alias_flags & ALIAS_AUTO_DELETABLE) {
				$alias->delete();
			}
		} else if (!$default) {
			$default = true;
		}
		
		// check if the FROM url is free... TO url would cost too much processing power -- or at least be inefficient... though this function 
		// should only be called by administrators :\
		if ($this->check_url_existence($from)) {
			return false;
		}
		
		$alias = new URL_Alias();
		$alias->alias_url = $from;
		$alias->alias_target = $to;
		$alias->alias_flags = 0;
		
		if ($unique_target) {
			$alias->alias_flags |= ALIAS_AUTO_DELETABLE; // cool, so |= really exists... looks like a smiley.. just kidding ;)
		}
		
		if ($default) {
			$alias->alias_flags |= ALIAS_URL_DEFAULT;
		}
		
		$alias->write();
		
		return true; // and away we go!
	}
	
	public function check_url_existence($url) {
		$result = cms::$manager->run($url, true);

		if ($result === CONTROLLER_ERROR) {
			return false; // URL does not exist
		}
		
		return true;
	}
	
	/**
	* Checks for a default alias on any URL, and returns it. If not found, it returns the original URL.
	*/	
	public function alias_url_link($url) {
		if (!empty($this->paths[$url])) {
			return $this->paths[$url];
		}
		
		return $url;
	}
	
	public function clean_title($title) {
		$title = strip_tags($title);
		// Preserve escaped octets.
		$title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
		// Remove percent signs that are not part of an octet.
		$title = str_replace('%', '', $title);
		// Restore octets.
		$title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
	
		$title = self::remove_accents($title);
	
		$title = strtolower($title);
		$title = preg_replace('/&.+?;/', '', $title); // kill entities
		$title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
		$title = preg_replace('/\s+/', '-', $title);
		$title = preg_replace('|-+|', '-', $title);
		
		$title = trim($title, '-');
	
		return $title;
	}
	
	public function remove_accents($string) {
		if ( !preg_match('/[\x80-\xff]/', $string) )
			return $string;
	
		// Assume ISO-8859-1 if not UTF-8
		$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
			.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
			.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
			.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
			.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
			.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
			.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
			.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
			.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
			.chr(252).chr(253).chr(255);
	
		$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";
	
		$string = strtr($string, $chars['in'], $chars['out']);
		$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
		$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
		$string = str_replace($double_chars['in'], $double_chars['out'], $string);
	
		return $string;
	}
}