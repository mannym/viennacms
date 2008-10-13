<?php
/**
 * The cache class
 * 
 * @package framework
 * @version $Id$
 * @copyright (c) 2008 viennaCMS group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */


/**
 * Cache
 * Cache query's, pages, etc.
 *
 * @package framework
 * @access public
 */
class cache {
	/**
	 * Construct
	 *
	 * @param GlobalStore $global
	 */
	public function __construct()
	{
		// @TODO: load this from somewhere else
		cms::$vars['cachedir'] = ROOT_PATH . 'cache/';
		
		// Writable?
		if(!is_writable(cms::$vars['cachedir']))
		{
			throw new viennaCMSException('Cache dir is not writeable');
		}
	}
}
?>