var curMain;

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
			$("#tree-left .nodes").treeview({
				persist: "cookie",
				collapsed: true,
				unique: true
			});
	
			load_option_default(id);
			curMain = id;
			unloading();
		}
	});
}

var goOut = '';

function load_option_default(id, value) {
	loading();
	$.ajax({
		cache: false,
		type: "GET",
		url: "index.php?action=get_default&id=" + id,
		success: function(output) {
			delete_wysiwyg();
			$('#system-right').html(output);
			if (goOut != '') {
				$('#system-right').html(goOut);
				goOut = '';
			}
			reload_contents(id);		
			unloading();
		}
	});
}

var loadCount = 0;

function loading() {
	if (loadCount == 0) {
		$('#tree-left').append('<div class="firing"><img src="style/images/loading.gif" /></div>');
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
			load_in_system($(this).attr('href'), id);
			return false;
		}
	});
	
	$('a.external').click(
		function() {
			location.href = $(this).attr('href');
			return false;
		}
	);
	
	$('form').submit(function() {
		// tinyMCE does not save content correctly, so we do it manually :)
		if ($('#wysiwyg_form').html() != null) {
			var ed = tinyMCE.getInstanceById('wysiwyg_form');
			$('#wysiwyg_form').val(tinyMCE.getContent(ed.editorId));
			tinyMCE.removeInstance(ed); // required to be able to use this again
		}
        var string = $(this).formSerialize(false);
        string = string;
        loading();

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
	
	$('form.upload').submit(function() {
		loading();
		$.ajaxFileUpload
		(
		    {
		        url: $(this).attr('action'),
		        fileElementId: 'file',
		        dataType: 'json',
		        success: function (data, status)
		        {
		            $('#system-right').html(output);
					if (output == 'reload') {
						reload_topbar();
						load_main_option(id);
					}
					reload_contents(id);
                	unloading();
		        }
		    }
		);
		return false;
	});
	
	$("#system-right .nodes").treeview({
				persist: "cookie",
				collapsed: true,
				unique: true
	});
	
	reinit_wysiwyg();
}

function load_in_system(url, id) {
	loading();
	$.ajax({
		cache: false,
		type: "GET",
		url: url + '&ajax=true',
		success: function(output) {
			delete_wysiwyg();
			if (id != curMain) {
				goOut = output;
				load_main_option(id);
			} else {
				$('#system-right').html(output);
				reload_contents(id);
			}
			unloading();
		}
	});
}

function delete_wysiwyg() {
	if ($('#wysiwyg_form').html() != null) {
		var ed = tinyMCE.getInstanceById('wysiwyg_form');
		tinyMCE.removeInstance(ed); // required to be able to use the WYSIWYG again
	}
}

$(document).ready(function() {
	if (jQuery.browser.msie) {
		docheight = $(document).height();
		docheight = docheight - 150;
		
		$('#tree-left, #system-right').height(docheight);
	}
	reload_topbar();
	load_main_option('site_structure');
});

function reinit_wysiwyg() {
	if ($('#wysiwyg_form').html() != null) {
		tinyMCE.execCommand("mceAddControl", true, "wysiwyg_form");
	}
}