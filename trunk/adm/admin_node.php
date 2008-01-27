<?php
/**
 * Node page for the ACP of the viennaCMS
 * 
 * @package viennaCMS
 * @author viennainfo.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 */

define("IN_VIENNACMS", true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();
$display_admin_tree = (empty($_GET['display_admin_tree']) ) ?  1 : 0;
$page_title = __('viennaCMS ACP');
include('./header.php');

$node = new CMS_Node();
$node->node_id = intval($_GET['node']);
$node->read();

echo '<h1>' . sprintf(__('Actions for %s'), $node->title) . '</h1>';
?>
<p class="icon_p"><a href="admin_node_new.php?node=<?php echo $node->node_id ?>&amp;do=new"><img src="images/add.png" /><br /><?php echo __('Add new child node') ?></a><br /><?php echo __('Add a new child node to this node.') ?></p>
<?php
if ($node->revision->has_modules) {
?>
<p class="icon_p"><a href="admin_node_modules.php?node=<?php echo $node->node_id ?>"><img src="images/modules.png" /><br /><?php echo __('Edit modules') ?></a><br /><?php echo __('Add or edit the modules (content) in this node.') ?></p>
<?php
} else if (utils::$types[$node->type]['type'] == NODE_CONTENT) {
?>
<p class="icon_p"><a href="admin_node_content.php?node=<?php echo $node->node_id ?>"><img src="images/modules.png" /><br /><?php echo __('Edit content') ?></a><br /><?php echo __('Add or edit the text content in this node.') ?></p>
<?php
}
?>
<p class="icon_p"><a href="admin_node_options.php?node=<?php echo $node->node_id ?>"><img src="images/edit.png" /><br /><?php echo __('Edit options') ?></a><br /><?php echo __('Configure options in this node.') ?></p>
<?php
if (utils::$types[$node->type]['type'] != NODE_NO_REVISION) {
?>
<p class="icon_p"><a href="admin_node_revisions.php?node=<?php echo $node->node_id ?>"><img src="images/revisions.png" /><br /><?php echo __('Older versions') ?></a><br /><?php echo __('View older versions of this node.') ?></p>
<?php
}
?>
<p class="icon_p"><a href="admin_node_new.php?node=<?php echo $node->node_id ?>&amp;do=edit"><img src="images/edit.png" /><br /><?php echo __('Edit node details') ?></a><br /><?php echo __('Edit the node details like title and description.') ?></p>
<p class="icon_p"><a href="admin_node_delete.php?node=<?php echo $node->node_id ?>"><img src="images/edit_remove.png" /><br /><?php echo __('Delete node') ?></a><br /><?php echo __('Delete this node.') ?></p>
<?php

include('./footer.php');

?>