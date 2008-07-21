<?php
/**
* ACP index for viennaCMS.
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('IN_VIENNACMS', true);
define('IN_ADMIN', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();

if (!isset($_GET['legacy'])) {
	include(ROOT_PATH . 'includes/admin.php');
	admin::load();
}

$display_admin_tree = (empty($_GET['display_admin_tree']) ) ?  1 : 0;
$page_title = __('viennaCMS ACP');
include('./header.php');
echo '<h1>' . __('Add new content') . '</h1>';
$types = utils::run_hook_all('list_types');
foreach ($types as $key => $value) {
	if ($value['allow_easy']) {
		?>
		<p class="icon_p"><a href="admin_node_new.php?mode=easy&amp;type=<?php echo base64_encode($value['extension'] . '::' . $key) ?>&amp;do=new"><img src="images/add.png" /><br /><?php echo sprintf(__('Add new %s content item'), $key) ?></a><br /><?php echo sprintf(__('Add a new content item (node) of the type %s'), $key) ?></p>
		<?php
	}
}
include('./footer.php');
?>