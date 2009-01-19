function file_add_to_manager(node_id, node_type, url) {
	if (node_type != 'file') {
		return;
	}
	
	if ($.wymeditors(wi) == undefined) {
		return;
	}
	
	wym = $.wymeditors(wi);
	
	$('#file-admin-pane').append('<img class="loading" style="float: right; margin: 5px;" src="extensions/core/icons/file-loading.gif" />');

	$.ajax({
		cache: false,
		type: "GET",
		url: url,
		success: function(output) {
		    if ($.wymeditors(wi) == undefined) {
				return;
	        }
	        
	        wym.insert(output);
			
			$('.loading').remove();
		}
	});
}
