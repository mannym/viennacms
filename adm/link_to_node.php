<?php
define('IN_VIENNACMS', true);
define('LIGHT_ADMIN', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();

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
			var text     = formObj.linktext.value;

			html += ''
				+ '<a href="{node:' + node + '}">' + text + '</a>';

			tinyMCEPopup.execCommand("mceInsertContent", true, html);
			tinyMCE.selectedInstance.repaint();

			tinyMCEPopup.close();
		}
	</script>
	</head>
	<body<?php /*onload="tinyMCEPopup.executeOnLoad('init();');"*/ ?>> 
	<form onsubmit="insert();return false;">
		<h3>Insert a node</h3>

		<!-- Gets filled with the selected elements name -->
		<div style="margin-top: 10px; margin-bottom: 10px">
			<?php echo utils::node_select('node_id'); ?><br />
			Link text: <input type="text" name="linktext" />
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
	<?php
include('./footer.php');
?>