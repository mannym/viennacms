<?php
class AdminController extends Controller {
	public static $panes = array();
	
	public static function add_pane($location, $pane, $arguments = array()) {
		self::$panes[$location][] = array(
			'title' => __('todo'),
			'href' => $pane,
			'arguments' => $arguments
		);
	}
	
	public function __construct() {
		Controller::$searchpaths[] = 'blueprint/controllers/admin/';
		View::$searchpaths['blueprint/views/admin/'] = VIEW_PRIORITY_HIGH;
	}
	
	private function check_auth() {
		if (!cms::$user->logged_in) {
			cms::$vars['error_title'] = __('Authentication required');
			trigger_error(__('You need to log in to access the ACP.'));
		}
		
		$auth = new Auth();
		$rights = $auth->get_rights('admin:see_acp', cms::$user->user);
		
		if (!in_array('y', $rights)) {
			cms::$vars['error_title'] = __('Information');
			trigger_error(__('You are not allowed to access the Administration Control Panel.'));
		}		
	}
	
	public static function add_toolbar($data, $caller) {
		ob_start();
		?>
		<ul class="toolbar">
			<?php
			foreach ($data as $id => $item) {
				?>
				<li><a style="background-image: url(<?php echo $item['icon'] ?>);" class="<?php echo $item['type'] ?>" href="<?php echo View::url($item['callback']) ?>"><span><?php echo $id ?></span></a></li>
				<?php
			}
			?>
		</ul>
		<?php
		$c = ob_get_contents();
		ob_end_clean();
		
		return $c;
	}
	
	public function main() {
		$this->check_auth();
		$this->init();
	}
	
	public function view() {
		$this->check_auth();
		
		setcookie('viennacms_acp_view', $this->arguments[0], time() + (3600), '/', '');
		$_COOKIE['viennacms_acp_view'] = $this->arguments[0];
		$this->view->path = 'admin/simple.php';
				
		$this->init();
	}
	
	public function init() {
		if (isset($_COOKIE['viennacms_acp_view'])) {
			$view = $_COOKIE['viennacms_acp_view'];
		} else {
			$view = 'nodes';
		}
		
		cms::$layout->view['pane_url'] = $this->view->url('admin/panes');
		
		$node_types = manager::run_hook_all('get_node_types');
		$icons = array();
		foreach ($node_types as $id => $data) {
			$icons[$id] = str_replace('~/', manager::base(), $data['icon']);
		}
		
		cms::$layout->view['icons'] = $icons;
		
		$panes = manager::run_hook_all('acp_get_panes', $view);
		$panes = array_merge_recursive($panes, AdminController::$panes);
		$panes_output = array(
			'left' => array()
		);
		
		foreach ($panes as $location => $lpanes) {
			foreach ($lpanes as $pane) {
				$panes_output[$location][] = array(
					'title' => $pane['title'],
					'content' => $this->pane($pane['href'], $pane['arguments'])
				);
			}
		}
		
		cms::$layout->view['panes'] = $panes_output;
		cms::$layout->view['views'] = manager::run_hook_all('acp_views');
	}
	
	public function controller() {
		$this->check_auth();
		
		$this->view->path = 'admin/simple.php';
		
		$controllern = array_shift($this->arguments);
		$method = array_shift($this->arguments);
		
		$controller = cms::$manager->get_controller('admin/' . $controllern); // array_shift to remove the original argument.
		$controller->view = new View();
		$controller->view->path = 'admin/' . $controllern . '/' . $method . '.php';
		$controller->arguments = $this->arguments;
		$return = $controller->$method();
		
		if (is_string($return)) {
			$this->view['data'] = $return;
		} else {
			$this->view['data'] = $controller->view->display();
		}
		
		$this->init();
	}
	
	public function panes() {
		$this->check_auth();
		echo json_encode($this->get_panes());
		exit;
	}
	
	public function pane($pane, $arguments = array()) {
		$controller = cms::$manager->get_controller('admin/' . $pane . 'pane'); // array_shift to remove the original argument.
		$controller->view = new View();
		$controller->view->path = 'admin/panes/' . $pane . '.php';
		$controller->arguments = $arguments;
		$controller->base = 'admin/panec/nodes/';
		$controller->main();
		return $controller->view->display();
	}
	
	private function get_panes() {
		return array(
			'left' => array(
				array(
					'title' => __('Nodes'),
					'href' => 'nodes'
				),
				/*array(
					'title' => __('Revisions'),
					'href' => $this->view->url('admin/pane/revisions/%parameter'),
					'aclass' => ''
				),*/
				
			),
		);
	}
}
