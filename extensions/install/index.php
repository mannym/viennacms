<?php
/**
 * Installation/upgrade backend
 * 
 * @package viennaCMS
 * @author viennacms.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 */

if (!defined ('IN_VIENNACMS')) {
	exit;
}

class extension_install {
	function admin_get_mainitems() {
		return array(
			'welcome' => array(
				'image' => 'install/images/welcome.png',
				'title'	=> __('Welcome'),
				'extension'	=> 'install',
			),
			'install' => array(
				'image' => 'install/images/install.png',
				'title'	=> __('Install'),
				'extension'	=> 'install',
			),
			'update' => array(
				'image' => 'install/images/update.png',
				'title'	=> __('Upgrade'),
				'extension'	=> 'install',
			),
		);
	}
	
	function admin_welcome($args) {
		switch ($args['type']) {
			case 'welcome':
				echo __('Welcome to the development version of viennaCMS 1.2.0! viennaCMS 1.2.0 includes a lot of new platform enhancements, which should make building a web site even easier.<br /><strong>Please note that this is a development version, and you should not use this on a live site.</strong>');
			break;
			case 'license':
				echo nl2br(file_get_contents(ROOT_PATH . '/docs/COPYING'));
			break;
			default:
				echo $args['type'];
			break;
		}
	}
	
	function admin_get_default() {
		return array(
			'welcome' => array(
				'extension' 	=> 'install',
			),
			'install' => array(
				'extension' 	=> 'install',
			)
		);
	}
	
	function admin_default_welcome() {
		$_GET['callback'] = 'install::admin_welcome';
		$_GET['params'] = base64_encode(serialize(array('type' => 'welcome')));
		admin::go_call();
	}

	function admin_left_welcome()
	{
		?>
		<ul class="nodes">
			<li><a href="<?php echo admin::get_callback(array('install', 'admin_welcome'), array('type' => 'welcome')) ?>" class="page">
				<?php echo __('Welcome') ?>
			</a></li>
			<li><a href="<?php echo admin::get_callback(array('install', 'admin_welcome'), array('type' => 'license')) ?>" class="page">
				<?php echo __('License') ?>
			</a></li>
			<li><a href="<?php echo admin::get_callback(array('install', 'admin_welcome'), array('type' => 'support')) ?>" class="page">
				<?php echo __('Support') ?>
			</a></li>
		</ul>
		<?php
	}
	
	function get_install_steps() {
		return array(
			1 => __('Choose language'),
			2 => __('Verify requirements'),
			3 => __('Setup database'),
			4 => __('Initial configuration'),
			5 => __('Finished')
		);
	}
	
	function admin_default_install() {
		$this->admin_install(array('step' => 1));
	}
	
	function admin_install($args) {
		$nargs = $args;
		if ($args['step'] <= 5) {
			$nargs['step'] += 1;
		} else {
			$nargs['step'] = 1;
		}
		?>
		<form method="post" action="<?php echo admin::get_callback(array('install', 'admin_install'), $nargs) ?>">
			<?php
			$function = 'install_step_' . intval($args['step']);
			$disabled = $this->$function($args);
			?>
		
			<div style="text-align: center;"><input type="submit" value="<?php echo __('Next') ?> &raquo;"<?php echo $disabled ?> /></div>
		</form>
		
		<script type="text/javascript">
			load_main_option('install&step=<?php echo $args['step'] ?>', true);
		</script>
		<?php
	}
	
	function install_step_1($args) {
		$dir = scandir(ROOT_PATH . 'locale');
		$languages = array();
		
		foreach ($dir as $file) {
			if (file_exists(ROOT_PATH . 'locale/' . $file . '/LC_MESSAGES')) {
				$languages[] = $file;
			}
		}
		
		echo __('Choose a language for the installation of viennaCMS. This will also be the language of the default administration user.');
		?>
		<br />
		<input type="radio" name="language" value="en_US" /> Default<br />
		<?php
		
		foreach ($languages as $language) {
			?>
			<input type="radio" name="language" value="<?php echo $language ?>" /> <?php echo $language ?><br />
			<?php
		}
		
		return '';
	}
	
	function get_dbals() {
		return array(
			'mysql' => array(
				'title' => __('MySQL'),
				'extension' => 'mysql'
			),
			'sqlite' => array(
				'title' => __('SQLite'),
				'extension' => 'sqlite'
			),
			'postgres' => array(
				'title' => __('PostgreSQL'),
				'extension' => 'pgsql'
			)
		);
	}
	
	function install_step_2() {
		// check language
		if ($_POST['language'] != 'en_US') {
			setcookie('language', $_POST['language'], time() + 3600, '/', '');
		} else {
			setcookie('language', '', time() - 3600, '/', '');
		}
		
		load_language();
		
		$requirements = array(
			'required' => array(
				'php_52' => array(
					'message' => __('PHP version is at least 5.2.0'),
					'result' => version_compare(phpversion(), '5.2.0', '>=')					
				),
				'url_fopen' => array(
					'message' => __('allow_url_fopen is enabled'),
					'result' => (@ini_get('allow_url_fopen') == 1 || strtolower(@ini_get('allow_url_fopen')) == 'on')
				)
			),
			'optional' => array(
				'gd' => array(
					'message' => __('GD extension [image resizing]'),
					'result' => extension_loaded('gd')
				)
			),
			'files' => array(
				'cache/', 'files/', 'config.php'
			),
			'databases' => $this->get_dbals()
		);
		
		?>
		<div class="group half">
		<h1><?php echo __('Required items') ?></h1>
		<?php
		$continue = true;
		
		foreach ($requirements['required'] as $key => $value) {
			echo '<div style="float: left; width: 69%; margin-left: 5px; padding-right: 3px;  padding-bottom: 3px;">';
			echo $value['message'];
			echo '</div><div style="float: right; width: 29%; margin-bottom: 3px; padding-bottom: 3px;">';
			echo ($value['result']) ? '<span style="color: #090;">' . __('Correct') . '</span>' : '<span style="color: #900;">' . __('Wrong') . '</span>';
			echo '</div><br style="clear: both;" />';
			if (!$value['result']) {
				$continue = false;
			}
		}
		?>
		</div>
		<div class="group half">
		<h1><?php echo __('Optional items') ?></h1>
		<?php
		foreach ($requirements['optional'] as $key => $value) {
			echo '<div style="float: left; width: 69%; margin-left: 5px; padding-right: 3px;  padding-bottom: 3px;">';
			echo $value['message'];
			echo '</div><div style="float: right; width: 29%; margin-bottom: 3px; padding-bottom: 3px;">';
			echo ($value['result']) ? '<span style="color: #090;">' . __('Correct') . '</span>' : '<span style="color: #900;">' . __('Wrong') . '</span>';
			echo '</div><br style="clear: both;" />';
		}
		?>
		</div>
		<br style="clear: both;" />
		<div class="group half">
		<h1><?php echo __('Database systems') ?></h1>
		<?php
		$dbal_working = false;
		
		foreach ($requirements['databases'] as $key => $value) {
			$value['result'] = extension_loaded($value['extension']);
			
			echo '<div style="float: left; width: 69%; margin-left: 5px; padding-right: 3px;  padding-bottom: 3px;">';
			echo $value['title'];
			echo '</div><div style="float: right; width: 29%; margin-bottom: 3px; padding-bottom: 3px;">';
			echo ($value['result']) ? '<span style="color: #090;">' . __('Available') . '</span>' : '<span style="color: #900;">' . __('Not available') . '</span>';
			echo '</div><br style="clear: both;" />';
			
			if ($value['result']) {
				$dbal_working = true;
			}
		}
		
		if (!$dbal_working) {
			$continue = false;
		}
		
		?>
		</div>
		<div class="group half">
		<h1><?php echo __('File permissions') ?></h1>
		<?php
		
		foreach ($requirements['files'] as $file) {
			$result = is_writable(ROOT_PATH . $file);
			
			echo '<div style="float: left; width: 69%; margin-left: 5px; padding-right: 3px;  padding-bottom: 3px;">';
			echo $file;
			echo '</div><div style="float: right; width: 29%; margin-bottom: 3px; padding-bottom: 3px;">';
			echo ($result) ? '<span style="color: #090;">' . __('Writable') . '</span>' : '<span style="color: #900;">' . __('Not writable') . '</span>';
			echo '</div><br style="clear: both;" />';
			
			if (!$result) {
				$continue = false;
			}
		}
		?>
		</div>
		<br style="clear: both;" />
		<script type="text/javascript">
			reload_topbar();
		</script>
		<?php
		
		if (!$continue) {
			echo __('Some things are not correct for running viennaCMS. Please, for example, make the required files writable, or change server settings. Installation cannot proceed.');
		}
		
		return ($continue) ? '' : ' disabled="disabled"';
	}
	
	function install_step_3($args) {
		//extract($args);
		?>
		<table width="100%" border="0">
    <tr> 
      <th colspan="2"><?php echo __('Database login data') ?></th>
    </tr>
    <?php
    global $error;
	if ($error) :
		global $error_msg, $dbhost, $dbuser, $dbpasswd, $dbname, $table_prefix, $admin_username;
		?>
		<tr>
			<td colspan="2"><span style="color: red;"><?php echo $error_msg ?></span></td>
		</tr>
		<?php
	endif;
    ?>
		<tr>
			<td><?php echo __('Database Type') ?></td>
			<td>
				<select name="dbms">
					<?php
					foreach ($this->get_dbals() as $key => $value) {
						echo '<option value="' . $key . '">' . $value['title'] . '</option>';
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo __('Database server (mostly localhost)') ?></td>
			<td><input type="text" value="<?php echo (empty($dbhost) ? 'localhost' : $dbhost) ?>" name="database_host" /></td>

		</tr>
		<tr>
			<td><?php echo __('Database user name') ?></td>
			<td><input type="text" value="<?php echo $dbuser ?>" name="database_username" /></td>
		</tr>
		<tr>
			<td><?php echo __('Database password') ?></td>
			<td><input type="password" value="<?php echo $dbpasswd ?>" name="database_password" /></td>

		</tr>
		<tr>
			<td><?php echo __('Database name') ?></td>
			<td><input type="text" value="<?php echo $dbname ?>" name="database_name" /></td>
		</tr>
		<tr>
			<td><?php echo __('Table prefix') ?></td>
			<td><input type="text" value="<?php echo (empty($table_prefix) ? 'viennacms_' : $table_prefix) ?>" name="table_prefix" /></td>
		</tr>
	</table>
		<?php
	}
	
	function admin_left_install() {
		$step = (isset($_GET['step'])) ? intval($_GET['step']) : 1;
		?>
		<ul class="nodes">
			<?php
			$steps = $this->get_install_steps();
			foreach ($steps as $key => $value) {
				$class = 'notdone';
				
				if ($step > $key) {
					$class = 'done';
				}
				
				if ($step == $key) {
					$class = 'current';
				}
				
				echo '<li><a href="#" class="' . $class . '">' . $value . '</a></li>';
			}
			?>
		</ul>
		<?php
	}
}
?>