function reload_topbar() {
	loading();
	$.ajax({
		cache: false,
		type: "GET",
		url: "index.php?action=get_main",
		success: function(output) {
			$('#main-items').html(output);
			unloading();
		}
	});
}

function load_main_option(id) {
	loading();
	$.ajax({
		cache: false,
		type: "GET",
		url: "index.php?action=get_left&id=" + id,
		success: function(output) {
			$('#tree-left').html(output);
			$(".nodes").treeview({
				persist: "cookie",
				collapsed: true,
				unique: true
			});
	
			load_option_default(id);
			unloading();
		}
	});
}

function load_option_default(id, value) {
	loading();
	$.ajax({
		cache: false,
		type: "GET",
		url: "index.php?action=get_default&id=" + id,
		success: function(output) {
			$('#system-right').html(output);
			reload_contents(id);		
			unloading();
		}
	});
}

var loadCount = 0;

function loading() {
	if (loadCount == 0) {
		$('#system-right').prepend('<div class="firing"><img src="style/images/loading.gif" /></div>');
	}
	
	loadCount++;
}

function unloading() {
	loadCount--;
	if (loadCount == 0) {
		$('.firing').remove();
	}
}

function reload_contents(id) {
	$("#tree-left a, #system-right a").click(function() {
		if ($(this).attr('href') != '#') {
			loading();
			$.ajax({
				cache: false,
				type: "GET",
				url: $(this).attr('href') + '&ajax=true',
				success: function(output) {
					$('#system-right').html(output);
					reload_contents(id);
					unloading();
				}
			});
			return false;
		}
	});
	
	$('form').submit(function() {
        var string = $(this).formSerialize(false);
        string = string;
        loading();
        //alert(string);
        $.post(
                $(this).attr('action'),
                string,
                function(output) {
					$('#system-right').html(output);
					if (output == 'reload') {
						reload_topbar();
						load_main_option(id);
					}
					reload_contents(id);
                	unloading();
                }
        );
        return false;
	});
}

$(document).ready(function() {
	reload_topbar();
	load_main_option('site_structure');
});