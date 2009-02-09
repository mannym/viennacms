<?php
class AdminSystemPaneController extends Controller
{
	function main()
	{
		$this->view->path = 'admin/simple.php';
		
		$data = manager::run_hook_all('acp_system_pane');
		$output = '';
		
		foreach ($data as $link) {
			$output .= '<li><a href="' . $this->view->url($link['href']) . '" style="background-image: url(' . str_replace('~', manager::base(), $link['icon']) . '); background-repeat: no-repeat;">' . $link['title'] . '</a></li>';
		}
		
		$this->view['data'] = '<ul class="treeview">' . $output . '</ul>';
	}
}
