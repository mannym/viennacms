<?php
class NiceNavController extends Controller {
	public function run() {
		ob_start();
		
		$node = cms::$vars['node'];
		$nodes = new Node();
		$nodes->parent = $node->node_id;
		$nodes = $nodes->read();
		
		foreach ($nodes as $node) {
			?>
			<div style="width: 49%; float: left;">
				<strong><a href="<?php echo $this->view->url('node/show/' . $node->node_id) ?>"><?php echo $node->title ?></a></strong>
				<p>
				<?php echo $node->description ?>
				</p>
			</div>
			<?php
		}
		
		$output = ob_get_contents();
		ob_end_clean();
		
		return array(
			'title' => $this->arguments['title'],
			'content' => $output
		);
	}
	
	public function friendlyname() {
		return __('Nice sub-navigation');
	}
	
	public function arguments($module) {
		return array(
			'fields' => array(
				'title' => array(
					'label' => __('Title'),
					'description' => __('The title of this module.'),
					'required' => true,
					'type' => 'textbox',
					'value' => $module['arguments']['title'],
					'group' => 'all',
					'weight' => -10
				),
			),
			'groups' => array(
				'all' => array(
					'title' => __('Module'),
					'expanded' => true
					)
				)
		);
	}
}
