<?php
define('ALIAS_AUTO_DELETABLE', 1);
define('ALIAS_URL_DEFAULT', 2);

class Router {
	public $routes;
	public $parts;
	public $query;
	public $aliases = array();
	public $paths = array();
	
	public function sort_length($a, $b) {
		if ($a == $b) {
			return 0;
		}
		
		return (strlen($a) > strlen($b) ? -1 : 1);
	}
	
	public function __construct() {
		include(VIENNACMS_PATH . 'framework/config/router.php');
		$this->routes = $routes;
		
		if (!defined('MINIMAL')) {
			$alias = new URL_Alias();
			$aliases = $alias->read();
			
			foreach ($aliases as $alias) {
				$this->aliases[$this->regexify($alias->alias_url)] = $alias->alias_target;
				
				if ($alias->alias_flags & ALIAS_URL_DEFAULT) {
					$this->paths[$alias->alias_target] = $alias->alias_url;
				}
			}
			
			uasort($this->aliases, array($this, 'sort_length'));
		}
	}
	
	public function route($query) {
		$this->query = $query;
		
		$queryobject = new stdClass;
		$queryobject->query = $this->query;
		
		VEvents::invoke('url.preroute-url', $queryobject);
		
		$this->query = $queryobject->query;
		
		$this->query = $this->check_alias();
		$this->match_parts();
	}
	
	private function regexify($source) {
		if (preg_match('/(.+?)\.(.+?)$/', $source, $regs)) {
			// URL contains a . -- let's run it that way
			$regex = '@^' . preg_quote($regs[1], '@') . '(/(.+?))?\.' . $regs[2] . '@';
		} else {
			// the simple way, no dot
			$regex = '@^' . preg_quote($source, '@') . '(/(.+?))?@';
		}
		
		return $regex;
	}
	
	private $context_arguments = '';
	
	private function check_alias($url = '') {
		$url = (!empty($url)) ? $url : $this->query;
		
		foreach ($this->aliases as $source => $target) {
			//$source_regex = $this->regexify($source);
			
			//if (preg_match('@' .  . '@'))
			
			if (preg_match($source, $url, $contextregs)) {
				$url = $target;
				
				if (!empty($contextregs[1])) {
					// note that we have parameters
					
					$this->context_arguments = substr($contextregs[1], 1); // there's a / in front of it, we don't want that here
				}
				
				return $url;
			}
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
		
		if (!isset($this->parts['params'])) {
			$this->parts['params'] = '';
		}
		
		if (!empty($this->context_arguments)) {
			if (!empty($this->parts['params'])) {
				$this->parts['params'] .= '/';			
			}
			
			$this->parts['params'] .= $this->context_arguments;
		}
	}
	
	public function add_url_alias($from, $to, $default = false, $unique_target = true) {
		if ($default && $unique_target) {
			throw new viennaCMSException('The parameters default and unique_target can not be both set to true.');
		}
		
		$deleted = false;
		
		$alias = new URL_Alias();
		$alias->alias_target = $to;
		$alias->read(true);
			
		if ($unique_target && !empty($alias->alias_url)) {
			if ($alias->alias_flags & ALIAS_AUTO_DELETABLE) {
				$alias->delete();
				$deleted = true;
				$default = true; // umm, wait, now I don't get it anymore...
			}
		} else if (!$default) {
			$default = true;
		}
		
		// check if the FROM url is free... TO url would cost too much processing power -- or at least be inefficient... though this function 
		// should only be called by administrators :\
		if (!$deleted && $this->check_url_existence($from)) {
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
	public function alias_url_link($url, $arguments = '') {
		if (!empty($this->paths[$url])) {
			$alias = $this->paths[$url];
			
			if ($arguments) {
				if (preg_match('/(.+?)\.(.+?)$/', $alias, $regs)) {
					// URL contains a . -- let's run it that way
					$alias = preg_replace('/\.(.+?)$/', '/' . $arguments . '.\1', $alias);
				} else {
					// the simple way, no dot
					$alias .= '/' . $arguments;
				}
			}
			
			return $alias;
		}
		
		if ($arguments) {
			$url .= '/' . $arguments;
		}
		
		return $url;
	}
	
	// these formatting functions adapted from WordPress...
	// creating your own character tables may be hard :)
	public function seems_utf8($str) {
		$length = strlen($str);
		for ($i=0; $i < $length; $i++) {
			$c = ord($str[$i]);
			if ($c < 0x80) $n = 0; # 0bbbbbbb
			elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
			elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
			elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
			elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
			elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
			else return false; # Does not match any model
			for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
				if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
					return false;
			}
		}
		return true;
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
			
		if ($this->seems_utf8($string)) {
			$chars = array(
			// Decompositions for Latin-1 Supplement
			chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
			chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
			chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
			chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
			chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
			chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
			chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
			chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
			chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
			chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
			chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
			chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
			chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
			chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
			chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
			chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
			chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
			chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
			chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
			chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
			chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
			chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
			chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
			chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
			chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
			chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
			chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
			chr(195).chr(191) => 'y',
			// Decompositions for Latin Extended-A
			chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
			chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
			chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
			chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
			chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
			chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
			chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
			chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
			chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
			chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
			chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
			chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
			chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
			chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
			chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
			chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
			chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
			chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
			chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
			chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
			chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
			chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
			chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
			chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
			chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
			chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
			chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
			chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
			chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
			chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
			chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
			chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
			chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
			chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
			chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
			chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
			chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
			chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
			chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
			chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
			chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
			chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
			chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
			chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
			chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
			chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
			chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
			chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
			chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
			chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
			chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
			chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
			chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
			chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
			chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
			chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
			chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
			chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
			chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
			chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
			chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
			chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
			chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
			chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
			// Euro Sign
			chr(226).chr(130).chr(172) => 'E',
			// GBP (Pound) Sign
			chr(194).chr(163) => '');
	
			$string = strtr($string, $chars);
		} else {
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
		}
	
		return $string;
	}
	// end 'taken from WordPress'
}