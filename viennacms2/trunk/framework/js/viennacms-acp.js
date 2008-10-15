var dragging = false;

function create_init_panes(loc, data) {
	for (i = 0; i < data.length; i++) {
		loc.append('<li><a href="' + data[i].href + '">' + data[i].title + '</a></li>');
	}
	
	loc.find('li:last').addClass('active');
}

$(function() {
	$.getJSON(
		pane_url,
		function(data) {
			create_init_panes($('#panes-left ul.tabs'), data.left);
			init_rest();
		}
	);
});

function init_rest() {
		
	$('.panes .pane .tabs').sortable({
		'revert': true,
		'connectWith': ['.panes .pane .tabs'],
		'containment': 'document',
		'scroll': false,
		//'zIndex': 200,
		'start': function(e, ui) {
			$('.panes:hidden').each(function(){
				$(this).show('fast', function() {
					$(this).find('.pane .tabs').sortable('refresh');
				});
			});
			
			dragging = true;
		},
		'receive': function(e, ui) {
			$(this).find('li.active').removeClass('active');
			ui.item.addClass('active');
			ui.sender.children(':last').addClass('active');
			load_pane(ui.item.find('a').attr('href'), ui.item);
			load_active_panes();
		},
		'stop': function() {
			reload_pane_containers(true);
			
			dragging = false;
		}
	});
	
	$('.panes .pane .tabs li a').click(function() {
		if (!dragging) {
			//load_pane($(this).attr('href'), $(this));
			$(this).parents('.tabs').find('li.active').removeClass('active');
			$(this).parents('li').addClass('active');
			load_active_panes();
		}
		
		return false;
	});
	
	load_active_panes();
	reload_pane_containers(false);

}

function load_active_panes() {
	$('.panes').each(function() {
		$(this).find('li.active a').each(function() {
			load_pane($(this).attr('href'), $(this));
		});
		
		if ($(this).find('li.active a').length == 0) {
			$(this).find('.content').html('');
		}
	});
}

function load_pane(url, object) {
	$.ajax({
		cache: false,
		type: "GET",
		url: url,
		success: function(output) {
			object.parents('.pane').find('.content').html(output);		

			$(".treeview").treeview({
				collapsed: true,
				unique: true
			});
			
			object.parents('.pane').find('.content a').click(function() {
				load_content($(this).attr('href'));
				return false;
			});
		}
	});
}

function load_content(url){
	$.ajax({
		cache: false,
		type: "GET",
		url: url,
		success: function(output){
			$('#main-content').html(output);
			
			$('fieldset legend').each(function() {
				$(this).html('<a class="toggle-me" href="#">' + $(this).html() + '</a>');
			});
			$('fieldset legend a.toggle-me').click(function() {
				$(this).parents('fieldset').find('div').toggle("fast", function() {
					$('fieldset div:hidden').parents('fieldset').addClass('contracted').removeClass('expanded');
					$('fieldset div:visible').parents('fieldset').addClass('expanded').removeClass('contracted');
				});
				return false;
			});

			$('fieldset.contracted > div').toggle();
		}
	});
}

var waitForHide = 0;

function reload_pane_containers(nicely) {
	$('.panes').each(function() {
		if ($(this).find('.pane ul.tabs li').length == 0) {
			if (nicely) {
				waitForHide++;
				$(this).hide('fast', function() {
					waitForHide--;
					reload_pane_containers();
				});
			}
			else {
				$(this).hide();
			}
		}
		
		percentage = 100 / $(this).find('.pane').length;
		$(this).find('.pane').height(percentage + '%');
	});
	
	if (waitForHide == 0) {
		$('#main-content').css('left', '0px').css('right', '0px');
		$('#panes-left, #panes-right').css('bottom', '1px');
		
		$('.panes:visible').each(function(){
			switch ($(this).attr('id')) {
				case 'panes-left':
					$('#main-content').css('left', '180px');
					break;
				case 'panes-right':
					$('#main-content').css('right', '180px');
					break;
				case 'panes-bottom':
					$('#main-content').css('bottom', '135px');
					$('#panes-left, #panes-right').css('bottom', '135px');
			}
		});
	}
}
