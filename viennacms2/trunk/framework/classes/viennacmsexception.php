<?php
/**
 * Exception class
 * 
 * @package framework
 * @version $Id$
 * @copyright (c) 2008 viennaCMS group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */


/**
 * Exception class
 * Handling exceptions
 *
 * @package framework
 * @access public
 */
class ViennaCMSException extends Exception {
	public function __construct($message, $code = 0)
	{
		// @TODO: Do something usefull, such as storing it
		
		// Make sure everything goes the way it has to
		parent::__construct($message, $code);
	}
	
	public function __toString()
	{
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}