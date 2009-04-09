<?php
class extension_news {
	public function get_node_types() {
		return array(
			'newsfolder' => array(
				'extension' => 'news',
				'title' => __('News folder'),
				'description' => __('A folder under which you can store the news items'),
				'type' => 'none',
				'icon' => '~/blueprint/views/admin/images/icons/page.png',
				'big_icon' => '~/blueprint/views/admin/images/icons/page_big.png',
				'options' => array(),
				'display_callback' => array($this, 'news'),
			),
		);
	}
	
	public function news($node, $arguments)
	{
		
	}
}
?>