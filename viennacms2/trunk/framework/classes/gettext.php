<?php
class VGetText {
	/**
	 * array structure:
	 * 'folder hash' => array(
	 *     'reader' => [binreader],
	 *     'stringcount' => [string count],
	 *     'sourceoffset' => ...,
	 *     'transloffset' => ...,
	 *     'hashes' => array(
	 *         hash => offset
	 *     ),
	 *     'hashsize' => ...,
	 *     'sourcetable' => array(
	 *         array(length, offset)
	 *	   ),
	 *     'transltable' => array(
	 *         array(length, offset)
	 *	   )
	 * );
	 */
	private $files = array();
	private $stringcache = array();
	private $hashes = array();
	private $folders = array();
	
	private $last_lang = '';
	
	private function jpwhash($string) {
		$data = unpack('C*', $string);
		$hash = 0;
		
		foreach ($data as $char) {
			$hash = $hash << 4;
			
			// workaround for PHP not supporting uint
			if ($hash >= 0) {
				$hash += $char;
			} else {
				$hash += $char;	
			}
			
			$g = $hash & 0xf0000000;
			
			if ($g) {
				// workaround for PHP bug without unsigned ints; this line flipped the sign bit killing the calculation
				//$hash ^= $g >> 24;
				$tmp = $hash >> 24;
				$tg = $tmp & 0xF0;
				
				$hash ^= $tg;
				$hash ^= $g;
			}
		}	
		
		return $hash;
	}
	
	public function add_searchfolder($folder) {
		$this->folders[] = VIENNACMS_PATH . $folder;
		
		if (!empty($this->last_lang)) {
			$this->load_language($this->last_lang);	
		}
	}
	
	public function load_language($language) {
		$filename = $this->folders[0] . '/' . $language . '.mo';
		
		if (!file_exists($filename)) {
			return false;
		}	
		
		$this->last_lang = $language;
		
		foreach ($this->folders as $folder) {
			$filename = $folder . '/' . $language . '.mo';
			
			if (file_exists($filename)) {
				$dhash = $this->jpwhash($folder);
				
				if (isset($this->files[$dhash])) {
					continue;	
				}
				
				$reader = new VBinaryReader($filename);
				
				// is this a little-endian gettext file? if not, continue on.
				// TODO: allow differing endianness
				if ($reader->read_uint32() != 0x950412de) {
					continue;
				}
				
				// check if the file is revision 0
				$revision = $reader->read_uint32();
				
				if ($revision != 0) {
					continue;	
				}
				
				// read file header
				$stringcount = $reader->read_uint32();
				$sourceoffset = $reader->read_uint32();
				$transloffset = $reader->read_uint32();
				$hashsize = $reader->read_uint32();
				$hashoffset = $reader->read_uint32();
				
				// read hashes
				$reader->seek($hashoffset);
				$hashes = array();
				
				for ($i = 0; $i < $hashsize; $i++) {
					$hashes[$i] = array($dhash, $reader->read_uint32());
				}
				
				$sourcetable = array();
				$reader->seek($sourceoffset);
				
				for ($i = 0; $i < $stringcount; $i++) {
					$sourcetable[] = array($reader->read_uint32(), $reader->read_uint32());	
				}
				
				$transltable = array();
				$reader->seek($transloffset);
				
				for ($i = 0; $i < $stringcount; $i++) {
					$transltable[] = array($reader->read_uint32(), $reader->read_uint32());	
				}
				
				// create file structure
				$structure = array(
					'reader' => $reader,
					'stringcount' => $stringcount,
					'sourceoffset' => $sourceoffset,
					'transloffset' => $transloffset,
					'hashsize' => $hashsize,
					'hashes' => $hashes,
					'sourcetable' => $sourcetable,
					'transltable' => $transltable
				);
				
				$this->files[$dhash] = $structure;
			}
		}
		
		return true;
	}
	
	public function translate($string) {
		$found = false;

		$shash = $this->jpwhash($string);
		
		// does this string exist in the cache?
		if (isset($this->stringcache[$shash])) {
			return $this->stringcache[$shash];
		}
		
		foreach ($this->files as $file) {
			// look for the hash
			$reader = $file['reader'];
			
			$hash = $shash % $file['hashsize'];
			$incr = 1 + ($shash % ($file['hashsize'] - 2));
			
			while (true) {
				$str_id = $file['hashes'][$hash][1];
				
				if ($str_id == 0) {
					//die;
					continue 2;
				}
				
				$str_id--;
				
				if ($str_id < $file['stringcount']) {
					$length = $file['sourcetable'][$str_id][0];
					$offset = $file['sourcetable'][$str_id][1];
					
					if ($length >= strlen($string)) {
						$reader->seek($offset);
						$sdata = $reader->read_string($length);
						
						if ($sdata == $string) {
							$id = $str_id;
							break;	
						}
					}
				}
				
				if ($hash >= ($file['hashsize'] - $incr)) {
					$hash -= $file['hashsize'] - $incr;
				} else {
					$hash += $incr;	
				}
			}
			
			/*if (!isset($file['hashes'][$hash])) {
				continue;
			}*/
			
			//$pointer = $file['hashes'][$hash];
			//$file = $this->files[$pointer[0]];
			
			// read it from the file
			$reader->seek($file['transloffset'] + ($id * 8));
			$length = $reader->read_uint32();
			$offset = $reader->read_uint32();
			
			// read the string itself		
			$reader->seek($offset);
			$data = $reader->read_string($length);
			
			// place it in the cache and return it...
			$this->stringcache[$shash] = $data;
			
			return $data;
		}
		
		$this->stringcache[$shash] = $string;
		return $string;
	}
}

if (!function_exists('__')) {
	function __($string) {
		if (!empty(cms::$vars) && isset(cms::$vars['gettext'])) {
			return cms::$vars['gettext']->translate($string);
		}	
		
		return $string;
	}
}