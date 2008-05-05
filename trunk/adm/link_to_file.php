<?php
define('IN_VIENNACMS', true);
define('LIGHT_ADMIN', true);
define('IN_FILES', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();
$files = utils::load_extension('files');

include('./header.php');
?>
	<script language="javascript" type="text/javascript" src="../includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript">
		function init() {
			var inst = tinyMCE.selectedInstance;
			var elm = inst.getFocusElement();
		}

		function insertSomething() {
			// Execute the mceTemplate command without UI this time
			//tinyMCEPopup.execCommand('mceLinkNode');

			var formObj = document.forms[0];
			var html      = '';
			var node     = formObj.node_id.value;

			html += ''
				+ '{viennafile:' + node + '}';

			tinyMCEPopup.execCommand("mceInsertContent", true, html);
			tinyMCE.selectedInstance.repaint();

			tinyMCEPopup.close();
		}
	</script>
	<script type="text/javascript">
		function treeDo() {
			$(".nodes").treeview({
			collapsed: true,
			unique: true
			});
		}
	</script>
	<style type="text/css">
		.nodes {
			display: inline;
		}
	</style>
	</head>
	<body onload="tinyMCEPopup.executeOnLoad('treeDo();');"> 
	<form onsubmit="insert();return false;">
		<h3>Insert a node</h3>

		<!-- Gets filled with the selected elements name -->
		<div style="margin-top: 10px; margin-bottom: 10px; text-align: left;">
			<?php echo $files->node_select('node_id'); ?>
			<?php //echo utils::node_select('node_id') ?>
		</div>

		<div class="mceActionPanel">
			<div style="float: left">
				<input type="button" id="insert" name="insert" value="{$lang_insert}" onclick="insertSomething();" />
			</div>

			<div style="float: right">
				<input type="button" id="cancel" name="cancel" value="{$lang_cancel}" onclick="tinyMCEPopup.close();" />
			</div>
		</div>
	</form>
	<script type="text/javascript">
				/*$(".nodes").treeview({
				persist: "location",
				collapsed: true,
				unique: true
				});*/
	</script>
	<?php
include('./footer.php');
?>