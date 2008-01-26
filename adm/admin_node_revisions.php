<?php
 /**
 * Page for viewing older revisions.
 * "Let's go back to the beginning..."
 * 
 * @package viennaCMS
 * @author viennainfo.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

define('IN_VIENNACMS', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();

$display_admin_tree = (empty($_GET['display_admin_tree']) ) ?  1 : 0;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'form';
if(!isset($_GET['node']) && !isset($_POST['node'])) {
	header('Location: ' . utils::base() . 'index.php');
	exit;
}
$node_id = (isset($_GET['node'])) ? intval($_GET['node']) : intval($_POST['node']);
$node = new CMS_Node();
$node->node_id = $node_id;
$node->read();
$page = page::getnew(false);
$mode = (isset($_GET['mode'])) ? $_GET['mode'] : 'list';
$page_title = __("viennaCMS ACP - Node revisions");

switch ($mode) {
	case 'list':
	default:
		include('./header.php');
		
		$page->sitenode = $page->get_this_site();
		
		$db = database::getnew();
		$sql = 'SELECT * FROM ' . NODE_REVISIONS_TABLE . ' WHERE node_id = ' . $node->node_id . ' ORDER BY revision_date DESC';
		$result = $db->sql_query($sql);
		$rowset = $db->sql_fetchrowset($result);
		?>
		<h1><?php echo sprintf(__('View older versions of %s'), $node->title); ?></h1>
		<ul>
		<?php
		foreach ($rowset as $row) {
			//echo '<li><a href="../index.php?id=' . $node->node_id . '&amp;revision=' . $row['revision_number'] . '">';
			echo '<li><a href="../' . $page->get_link($node, '/revision/' . $row['revision_number']) . '">';
			echo 'Revision ' . $row['revision_number'] . ' (' . date('d-m-Y G:i:s', $row['revision_date']) . ' )';
			echo '</a></li>' . "\r\n";
		}
		?>
		</ul>
		<?php
		include('./footer.php');
	break;
}
?>