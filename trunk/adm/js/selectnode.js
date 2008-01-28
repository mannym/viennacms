function select_node(name, id) {
	$('#' + name).val(id);
	$('.nodes li').removeClass('selected');
	$('#' + name + '-' + id).addClass('selected');
}