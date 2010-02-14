<!-- <div class="uploader"><a id="file_upload" href="#"><?php echo __('Upload') ?></a></div> -->
<ol id="uploads">
</ol>
<?php //echo sprintf(__('Maximum file size: %s'), $this['max_size']) ?>

<script type="text/javascript">
$(function() {
new AjaxUpload('#file-upload-button', {
	  action: '<?php echo $this['action'] ?>',
	  name: 'viennafile',
	  autoSubmit: true,
	  onSubmit: function(file, extension) {
		$('#file-upload-button span').text('<?php echo __('Uploading...') ?>');
		$('#main-content').append('<img class="loading" style="float: right; margin: 5px;" src="extensions/core/icons/file-loading.gif" />');
	  },
	  onComplete: function(file, response) {
			var object = eval("(" + response + ")");

			$('#uploads').append('<li>' + object.message + '</li>');

			if (object.addendum) {
				hasChildren = $('.treeview a.selected').parents('li').eq(0).find('ul').length;
				if (hasChildren > 0) {
					var add = $('.treeview a.selected').parents('li').eq(0).find('ul').eq(0).append(object.addendum.ux_html);
					$('.treeview').treeview({ add: add });
				} else {
					var add = $('.treeview a.selected').parents('li').eq(0).append('<ul class="oncontentremove open">' + object.addendum.ux_html + '</ul>')
					$('.treeview').treeview({ add: add });
				}
			}

			$('#file-upload-button span').text('<?php echo __('Upload') ?>');
			$('.loading').remove();
	  }
	});
});
</script>