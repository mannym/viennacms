<?php
/**
 * Global storage class file
 * 
 * @package framework
 * @version $Id$
 * @copyright (c) 2008 viennaCMS group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * GlobalStore
 * Stores all variables required troughout the rest of the application. 
 * 
 * @package framework
 * @access public
 */
class GlobalStore implements ArrayAccess {
	private $data = array();
	
  /**
   * GlobalStore::set()
   * Sets a key in the storage.
   * 
   * @param string $key The key to save the data in.
   * @param mixed $value The value to save in the key.
   * @return void
   */
	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	
  /**
   * GlobalStore::get()
   * Retrieves a point of data from the storage.
   * 
   * @param string $key The key to retrieve.
   * @return mixed The value stored in the key
   */
	public function get($key) {
		return $this->data[$key];
	}
	
  /**
   * GlobalStore::offsetExists()
   * Implementation of ArrayAccess::offsetExists()
   */
	public function offsetExists($key) {
		return (isset($this->data[$key]));
	}
	
  /**
   * GlobalStore::offsetGet()
   * Implementation of ArrayAccess::offsetGet()
   */
	public function offsetGet($key) {
		return $this->get($key);
	}
	
  /**
   * GlobalStore::offsetSet()
   * Implementation of ArrayAccess::offsetSet()
   */
	public function offsetSet($key, $value) {
		$this->set($key, $value);
	}
	
  /**
   * GlobalStore::offsetUnset()
   * Implementation of ArrayAccess::offsetUnset()
   */
	public function offsetUnset($key) {
		unset($this->data[$key]);
	}
}