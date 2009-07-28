<?php
class AdminTranslateController extends Controller {
	public function node() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$result = $node->read();
		$node = $result[0];
		
		AdminController::set_context('node_translate', $node); // for the toolbar links
		$tset = cms::ext('translation')->create_translationset($node); // will create a tset for this node
		
		$translations = $tset->get_children();
		
		// check the status
		foreach ($translations as $translation) {
			if ($translation->revision->time >= $node->revision->time) {
				$translation->translation_updated = true;
			} else {
				$translation->translation_updated = false;
			}
		}
		
		$this->view['translations'] = $translations;
		$this->view['basenode'] = $node;
		$this->view['tset'] = $tset;
	}
	
	public function create() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$result = $node->read();
		$tset = $result[0];
		
		// and read the corresponding node
		$node->node_id = (string)$tset->options['tset_parent'];
		$node = $node->read();
		$node = $node[0];
		
		// create the new translation
		$translation = Node::create('Node');
		Model::copy_data($node, $translation);
		// correct any issues come up becuase of this
		$translation->written = false;
		unset($translation->node_id);
		unset($translation->revision->node);
		unset($translation->revision->id);
		$translation->parent = $tset->node_id;
		$translation->options['tset_language'] = $_POST['language'];
		$translation->write(); // let's hope this goes right :)
		
		cms::redirect($translation->to_admin_url());
	}
	
	public function edit() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$result = $node->read();
		$node = $result[0];
		
		$parent = $node->get_parent();
		
		if (!isset($parent->options['tset_parent'])) {
			return __('Error');
		}
		
		cms::redirect($node->to_admin_url());
	}
}