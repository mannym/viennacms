<?php
class VBinaryReader {
	private $filepointer;
	
	public function __construct($filename) {
		$this->filepointer = fopen($filename, 'rb');
		
		if (!$this->filepointer) {
			throw new Exception("File could not be loaded.");
		}
	}	
	
	public function __destruct() {
		fclose($this->filepointer);	
	}
	
	private function unpack($format, $string) {
		$d = unpack($format, $string);
		
		return $d[1];
	}
	
	public function seek($position) {
		fseek($this->filepointer, $position);
	}
	
	public function read_string($length) {
		$data = fread($this->filepointer, $length);
		
		return str_replace("\0", '', $data);	
	}
	
	public function read_float() {
		$data = fread($this->filepointer, 4);	
		
		return $this->unpack('f', $data); // f: machine float
	}
	
	public function read_int16() {
		$data = fread($this->filepointer, 2);	
		
		return $this->unpack('s', $data); // s: machine int16
	}
	
	public function read_uint16() {
		$data = fread($this->filepointer, 2);	
		
		return $this->unpack('v', $data); // v: little-endian int16
	}
	
	public function read_int32() {
		$data = fread($this->filepointer, 4);	
		
		return $this->unpack('l', $data); // V: machine int32
	}
	
	public function read_uint32() {
		$data = fread($this->filepointer, 4);	
		
		return $this->unpack('V', $data); // V: little-endian uint32
	}
}