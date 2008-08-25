<?php
class NodeController extends Controller {
	public function show() {
		$node = new Node();

		$node_id = intval($this->arguments[0]);
		try 
		{
			$node->load('id = ?', $node_id);
		}
		catch(ADODB_Exception $e)
		{
			// Most likely this means we got a wrong node, but we do want to perform a check
			if(!preg_match('#id = \?#', $e->getMessage()))
			{
				// Oh oh, we got an error
				// return to default error handler
				return;
			}
			throw new viennaCMS_Exception('Node not found');
		}
	
		$this->view['node'] = $node;
		$this->global['layout']->view['title'] = $node->title;
	}
	
	public function main() {
		/*$node = new Node();
		$node->title = 'Woef!';
		$node->parent = 2;
		$node->description = 'De blafnode :D';
		$node->type = 'page';
		$node->revision->content = 'Woef :D';
		$node->save();
		
		$node = new Node();
		$node->load('id = 7');
		echo $node->revision->content;
			
		$node = new Node();
		$node->load('id = 7');
		$node->revision->content = 'Woef x2';
		$node->save();
		*/

		$this->global['router']->parts['action'] = 'show';
		$this->global['sitenode'] = $this->get_sitenode();
		$this->view->reset_path();
		$this->arguments = array((string) $this->global['sitenode']->options['homepage']);
		$this->show();
	}
	
	public function get_sitenode() {
		// create a temporary node to serve as the main root
		$node = new Node();
		$node->id = 0;
		$sites = $node->get_children();
		
		// now check the hostname
		foreach ($sites as $node) {
			if ($node->options['hostname'] == '' && !isset($default)) {
				// save it for the default
				$default = $node;
			} else if ($_SERVER['HTTP_HOST'] == $node->options['hostname']) {
				return $node; // return immediately
			}
		}
		
		return $default;
	}
}
?>