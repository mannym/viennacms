<?php
class AdminExtensionsController extends Controller {
	public function index() {
		// finally, some extension stuff :)
		$files = scandir(ROOT_PATH . 'extensions');
		$extensions = array();
		
		foreach ($files as $extension) {
			if ($extension{0} != '.') {
				$directory = ROOT_PATH . 'extensions/' . $extension . '/';
				
				if (file_exists($directory . $extension . '.info.php')) {
					$data = include($directory . $extension . '.info.php');
					
					$extensions[$extension] = $data;
					$extensions[$extension]['path'] = $directory;
				}
			}
		}
		
		$installed_extensions = $disabled_extensions = array();
		
		foreach ($extensions as $id => $extension) {
			if (isset(manager::$extpaths[$id])) {
				if ($id != 'core') {
					$extension['actions'] = sprintf('<a href="%s">%s</a><br />', $this->view->url('admin/controller/extensions/deactivate/' . $id), __('Deactivate'));
				} else {
					$extension['actions'] = __('Locked');
				}

				$installed_extensions[$id] = $extension;
			} else {
				$extension['actions'] = sprintf('<a href="%s">%s</a><br />', $this->view->url('admin/controller/extensions/activate/' . $id), __('Activate'));

				$disabled_extensions[$id] = $extension;
			}
		}
		
		$this->view['installed'] = $installed_extensions;
		$this->view['disabled'] = $disabled_extensions;
		$this->view['all'] = $extensions;
	}
	
	public function activate() {
		$id = $this->arguments[0];
		
		if (!file_exists(ROOT_PATH . 'extensions/' . $id . '/' . $id . '.ext.php')) {
			return __('This extension does not exist!');
		}
		
		if (isset(manager::$extpaths[$id])) {
			return __('This extension is already activated.');
		}
		
		if (file_exists(ROOT_PATH . 'extensions/' . $id . '/' . $id . '.install.php')) {
			include(ROOT_PATH . 'extensions/' . $id . '/' . $id . '.install.php');
			
			$class = 'extension_install_' . $id;
			$install = new $class;
			
			if (method_exists($install, 'install')) {
				$install->install();
			}
		}
		
		$data = include(ROOT_PATH . 'extensions/' . $id . '/' . $id . '.info.php');
		$version = $data['version'];
				
		$extensions = unserialize(cms::$config['extensions']);
		$extensions[$id] = array(
			'version' => $version
		);
		
		cms::$config['extensions'] = serialize($extensions);
		
		return sprintf(__('The extension %s is successfully enabled.'), $data['name']);
	}
	
	public function deactivate() {
		$id = $this->arguments[0];
		
		if (!isset(manager::$extpaths[$id])) {
			return __('This extension is not activated.');
		}
		
		if (file_exists(ROOT_PATH . 'extensions/' . $id . '/' . $id . '.install.php')) {
			include(ROOT_PATH . 'extensions/' . $id . '/' . $id . '.install.php');
			
			$class = 'extension_install_' . $id;
			$install = new $class;
			
			if (method_exists($install, 'disable')) {
				$install->install();
			}
		}
		
		$data = include(ROOT_PATH . 'extensions/' . $id . '/' . $id . '.info.php');
				
		$extensions = unserialize(cms::$config['extensions']);
		unset($extensions[$id]);
		cms::$config['extensions'] = serialize($extensions);
		
		return sprintf(__('The extension %s is successfully disabled.'), $data['name']);
	}
}