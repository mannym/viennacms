<?php
/**
 * The cache class
 * 
 * @package framework
 * @version $Id$
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */


/**
 * Cache
 * Cache query's, pages, etc.
 *
 * @package framework
 * @access public
 */

class acm_file
{
	private $vars = array();
	private $var_expires = array();
	private $is_modified = false;
	
	public $sql_rowset = array();
	public $cache_dir = '';
	
	/**
	* Set cache path
	*/
	function __construct()
	{
		$this->cache_dir = VIENNACMS_PATH . 'cache/';
	}
	
	/**
	* Load global cache
	*/
	private function load()
	{
		// grab the global cache
		if (file_exists($this->cache_dir . 'data_global.php'))
		{
			@include($this->cache_dir . 'data_global.php');
			return true;
		}
		
		return false;
	}
	
	/**
	* Unload cache object
	*/
	public function unload()
	{
		$this->save();
		unset($this->vars);
		unset($this->var_expires);
		unset($this->sql_rowset);
		
		$this->vars = array();
		$this->var_expires = array();
		$this->sql_rowset = array();
	}
	
	/**
	* Save modified objects
	*/
	private function save()
	{
		if (!$this->is_modified)
		{
			return;
		}
		
		$fp = @fopen($this->cache_dir . 'data_global.php', 'wb');
		
		if ($fp)
		{
			@flock($fp, LOCK_EX);
			fwrite($fp, "<?php\n\$this->vars = unserialize(" . var_export(serialize($this->vars), true) . ");\n\$this->var_expires = unserialize(" . var_export(serialize($this->var_expires), true) . ");");
			@flock($fp, LOCK_UN);
			fclose($fp);
			
			@chmod($this->cache_dir . 'data_global.php', 0777);
		}
		else
		{
			// Now, this occurred how often? ... phew, just tell the user then...
			if (!@is_writable($this->cache_dir))
			{
				throw new RuntimeException($this->cache_dir . ' is NOT writable.');
			}
			
			throw new RuntimeException('Not able to open ' . $this->cache_dir . 'data_global.php');
		}
		
		$this->is_modified = false;
	}
	
	/**
	* Tidy cache
	*/
	public function tidy()
	{
		$dir = @opendir($this->cache_dir);
		
		if (!$dir)
		{
			return;
		}
		
		while (($entry = readdir($dir)) !== false)
		{
			if (!preg_match('/^(sql_|data_(?!global))/', $entry))
			{
				continue;
			}
			
			$expired = true;
			@include($this->cache_dir . $entry);
			if ($expired)
			{
				$this->remove_file($this->cache_dir . $entry);
			}
		}
		closedir($dir);
		
		if (file_exists($this->cache_dir . 'data_global.php'))
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}
			
			foreach ($this->var_expires as $var_name => $expires)
			{
				if (time() > $expires)
				{
					$this->destroy($var_name);
				}
			}
		}
	}
	
	/**
	* Get saved cache object
	*/
	public function get($var_name)
	{
		if ($var_name[0] === '_')
		{
			if (!$this->_exists($var_name))
			{
				return false;
			}
			
			@include($this->cache_dir . "data{$var_name}.php");
			return (isset($data)) ? $data : false;
		}
		else
		{
			return ($this->_exists($var_name)) ? $this->vars[$var_name] : false;
		}
	}
	
	/**
	* Put data into cache
	*/
	function put($var_name, $var, $ttl = 31536000)
	{
		if ($var_name[0] === '_')
		{
			$fp = @fopen($this->cache_dir . "data{$var_name}.php", 'wb');
			
			if ($fp)
			{
				@flock($fp, LOCK_EX);
				fwrite($fp, "<?php\n\$expired = (time() > " . (time() + $ttl) . ") ? true : false;\nif (\$expired) { return; }\n\$data =  " . (sizeof($var) ? "unserialize(" . var_export(serialize($var), true) . ");" : 'array();'));
				@flock($fp, LOCK_UN);
				fclose($fp);
				
				@chmod($this->cache_dir . "data{$var_name}.php", 0777);
			}
		}
		else
		{
			$this->vars[$var_name] = $var;
			$this->var_expires[$var_name] = time() + $ttl;
			$this->is_modified = true;
		}
	}
	
	/**
	* Purge cache data
	*/
	public function purge()
	{
		// Purge all cache files
		$dir = @opendir($this->cache_dir);
		
		if (!$dir)
		{
			return;
		}
		
		while (($entry = readdir($dir)) !== false)
		{
			if (strpos($entry, 'sql_') !== 0 && strpos($entry, 'data_') !== 0)
			{
				continue;
			}
			
			$this->remove_file($this->cache_dir . $entry);
		}
		closedir($dir);
		
		unset($this->vars);
		unset($this->var_expires);
		unset($this->sql_rowset);
		
		$this->vars = array();
		$this->var_expires = array();
		$this->sql_rowset = array();
		
		$this->is_modified = false;
	}
	
	/**
	* Destroy cache data
	*/
	public function destroy($var_name, $table = '')
	{
		if ($var_name === 'sql' && !empty($table))
		{
			if (!is_array($table))
			{
				$table = array($table);
			}
			
			$dir = @opendir($this->cache_dir);
			
			if (!$dir)
			{
				return;
			}
			
			while (($entry = readdir($dir)) !== false)
			{
				if (strpos($entry, 'sql_') !== 0)
				{
					continue;
				}
				
				// The following method is more failproof than simply assuming the query is on line 3 (which it should be)
				$check_line = @file_get_contents($this->cache_dir . $entry);
				
				if (empty($check_line))
				{
					continue;
				}
				
				// Now get the contents between /* and */
				$check_line = substr($check_line, strpos($check_line, '/* ') + 3, strpos($check_line, ' */') - strpos($check_line, '/* ') - 3);
				
				$found = false;
				foreach ($table as $check_table)
				{
					// Better catch partial table names than no table names. ;)
					if (strpos($check_line, $check_table) !== false)
					{
						$found = true;
						break;
					}
				}
				
				if ($found)
				{
					$this->remove_file($this->cache_dir . $entry);
				}
			}
			closedir($dir);
			
			return;
		}
		
		if (!$this->_exists($var_name))
		{
			return;
		}
		
		if ($var_name[0] === '_')
		{
			$this->remove_file($this->cache_dir . 'data' . $var_name . '.php', true);
		}
		else if (isset($this->vars[$var_name]))
			{
				$this->is_modified = true;
				unset($this->vars[$var_name]);
				unset($this->var_expires[$var_name]);
				
				// We save here to let the following cache hits succeed
				$this->save();
			}
	}
	
	/**
	* Check if a given cache entry exist
	*/
	private function _exists($var_name)
	{
		if ($var_name[0] === '_')
		{
			return file_exists($this->cache_dir . 'data' . $var_name . '.php');
		}
		else
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}
			
			if (!isset($this->var_expires[$var_name]))
			{
				return false;
			}
			
			return (time() > $this->var_expires[$var_name]) ? false : isset($this->vars[$var_name]);
		}
	}
	
	/**
	* Load cached sql query
	*/
	public function sql_load($query)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$query_id = sizeof($this->sql_rowset);
		
		if (!file_exists($this->cache_dir . 'sql_' . md5($query) . '.php'))
		{
			return false;
		}
		
		@include($this->cache_dir . 'sql_' . md5($query) . '.php');
		
		if (!isset($expired))
		{
			return false;
		}
		else if ($expired)
			{
				$this->remove_file($this->cache_dir . 'sql_' . md5($query) . '.php', true);
				return false;
			}
		
		
		return $query_id;
	}
	
	/**
	* Save sql query
	*/
	public function sql_save($query, &$query_result, $ttl)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$filename = $this->cache_dir . 'sql_' . md5($query) . '.php';
		$fp = @fopen($filename, 'wb');
		
		if ($fp)
		{
			@flock($fp, LOCK_EX);
			
			$query_id = sizeof($this->sql_rowset);
			$this->sql_rowset[$query_id] = array();
			
			while ($row = cms::$db->sql_fetchrow($query_result))
			{
				$this->sql_rowset[$query_id][] = $row;
			}
			cms::$db->sql_freeresult($query_result);
			
			$file = "<?php\n/* " . str_replace('*/', '*\/', $query) . " */";
			$file .= "\n\$expired = (time() > " . (time() + $ttl) . ") ? true : false;\nif (\$expired) { return; }\n";
			
			fwrite($fp, $file . "\$this->sql_rowset[\$query_id] = " . (sizeof($this->sql_rowset[$query_id]) ? "unserialize(" . var_export(serialize($this->sql_rowset[$query_id]), true) . ");" : 'array();'));
			@flock($fp, LOCK_UN);
			fclose($fp);
			
			@chmod($filename, 0777);
			
			$query_result = $query_id;
		}
	}
	
	/**
	* Fetch row from cache (database)
	*/
	public function sql_fetchrow($query_id)
	{
		list(, $row) = each($this->sql_rowset[$query_id]);
		
		return ($row !== NULL) ? $row : false;
	}
	
	/**
	* Get a rowset
	*/
	public function sql_fetchrowset($query_id) {
		return $this->sql_rowset[$query_id];
	}
	
	/**
	* Fetch a field from the current row of a cached database result (database)
	*/
	public function sql_fetchfield($query_id, $field)
	{
		$row = current($this->sql_rowset[$query_id]);
		
		return ($row !== false && isset($row[$field])) ? $row[$field] : false;
	}
	
	/**
	* Free memory used for a cached database result (database)
	*/
	public function sql_freeresult($query_id)
	{
		if (!isset($this->sql_rowset[$query_id]))
		{
			return false;
		}
		
		unset($this->sql_rowset[$query_id]);
		
		return true;
	}
	
	/**
	* Removes/unlinks file
	*/
	private function remove_file($filename, $check = false)
	{
		if ($check && !@is_writable($this->cache_dir))
		{
			// E_USER_ERROR - not using language entry - intended.
			throw new viennaCMSException('Unable to remove files within ' . $this->cache_dir . '. Please check directory permissions.');
		}
		
		return @unlink($filename);
	}
}
?>