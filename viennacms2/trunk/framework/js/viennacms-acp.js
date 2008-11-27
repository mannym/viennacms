var dragging = false;

function create_init_panes(loc, data) {
	for (i = 0; i < data.length; i++) {
		loc.append('<li class="' + data[i].aclass + '"><a href="' + data[i].href + '">' + data[i].title + '</a></li>');
	}
	
	//loc.addClass('active');
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
			if ($.browser.msie) {
				activate_click_panes();
			}
			dragging = false;
		}
	});
	
	activate_click_panes();
	load_active_panes();
	reload_pane_containers(false);

    $('#main-content').click(function() {
        $('.panes ul.submenu').slideUp('normal', function() {
            $(this).remove();
        });
    });
}

function activate_click_panes() {
	$('.panes .pane .tabs li a').click(function() {
		if (!dragging) {
			//load_pane($(this).attr('href'), $(this));
			$(this).parents('.tabs').find('li.active').removeClass('active');
			$(this).parents('li').addClass('active');
			load_active_panes();
		}
		
		return false;
	});
}

function ie_position_fix() {
	if ($.browser.msie) {
		$('div').each(function() {
			if ($(this).css('position') == 'absolute') {
				if ($(this).css('top') != 'auto' && $(this).css('bottom') != 'auto') {
					height = $(document).height() - $(this).css('top').replace('px', '') - $(this).css('bottom').replace('px', '');
					$(this).height(height);
				}
				
				if ($(this).css('right') != 'auto' && $(this).css('left') != 'auto') {
					width = $(document).width() - $(this).css('left').replace('px', '') - $(this).css('right').replace('px', '');
					$(this).width(width);
				}
			}
		});
	}
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
				unique: true,
				persist: 'cookie'
			});
			
			$('.treeview a').click(function() {
			    $(this).parents('.treeview').find('a.selected').removeClass('selected');
			    $(this).addClass('selected');
			});
			
			object.parents('.pane').find('.content div a').click(function() {
				load_content($(this).attr('href'));
				return false;
			});
			
			object.parents('.pane').find('.toolbar li a').click(function() {
			    url = $(this).attr('href');
			    if ($(this).parents('.pane').find('a.selected').parents('li').length > 0) { 
			        url = url.replace('%selected_id', $(this).parents('.pane').find('a.selected').parents('li').attr('id').replace('node-', ''));
			    } else {
			        url = url.replace('%selected_id', 0);
			    }

			    if ($(this).hasClass('submenu')) {
			        var old = this;
			    
		            $.ajax({
		                cache: false,
		                type: "GET",
		                url: url,
		                success: function(output) {
		                    var callback = function() {
		                        $('ul.submenu').remove();
                                $(old).parents('ul.toolbar').after(output);
    		                    
		                        $('ul.submenu').slideDown('normal');
		                        $('ul.submenu a').click(function() {
		                            load_content($(this).attr('href'));
		                            /*$(this).parents('ul.submenu').slideUp('normal', function() {
		                                $(this).remove();
		                            });*/
				                    return false;
		                        });		                        
		                    };
		                    
		                    if ($('ul.submenu').length > 0) {
    		                    $('ul.submenu').slideUp('normal', callback);
		                    } else {
		                        callback();
		                    }
		                }
		            });
			    }
			    
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
		success: function(output) {
		    if ($.wymeditors(wi) != undefined) {
	            wi++;
	        }
	        
	        var saveMe;	        
		    $('.oncontentremove').each(function() {
		        if ($(this).parents('li:not(.oncontentremove)').length > 0) {
		            saveMe = $(this).parents('li:not(.oncontentremove)').eq(0);
		        }
		    });
		    $('.oncontentremove').remove();
		    
		    if (saveMe) {
		        $('.treeview').treeview({ add: saveMe });
		    }
		    
			$('#main-content').html(output);
			$('ul.submenu').slideUp('normal', function() {
                $(this).remove();
		    });
			update_content();
		}
	});
}

var wi = 0;

function update_content() {
	$('fieldset legend').each(function() {
		$(this).html('<a class="toggle-me" href="#">' + $(this).html() + '</a>');
	});
	$('fieldset legend a.toggle-me').click(function() {
		$(this).parents('fieldset').children('div').toggle("fast", function() {
			$('fieldset > div:hidden').parents('fieldset').addClass('contracted').removeClass('expanded');
			$('fieldset > div:visible').parents('fieldset').addClass('expanded').removeClass('contracted');
			if($.browser.msie)
				$(this).parents('fieldset').find('div').get(0).style.removeAttribute('filter');
		});
		return false;
	});

	$('fieldset.contracted > div').toggle();
	
	$('.wysiwyg').wymeditor();
	
	$('#main-content form').ajaxForm()
	  .bind('form-pre-serialize', function(event, $form, formOptions, veto) {
        if ($.wymeditors(wi) != undefined) {
	        $.wymeditors(wi).update();
	        wi++;
	    }
    }).submit(function() {
        options = {
	            success: function(output) {
	                $('#main-content').html(output);
	                $('ul.submenu').slideUp('normal', function() {
                        $(this).remove();
		            });
	                update_content();
	            },
	            object: $(this)
	        };

	    callback = $(this).ajaxSubmit;

        if ($('div.modform').length > 0) {
	        update_modform($('div.modform').parents('li').eq(0).find('a').get(0), callback, options);
	    } else {
	        callback(options);
	    }
	    
        return false;
    });
    
    //$('.modules').sortable();
    build_modules();
}

function build_modules() {
    $('.modules li').each(function() {
        $(this).find('a').click(function() {
            if (!dragging) {
                var old = this;
                // .replace('+', '%2B').replace('=', '%3D').replace('/', '%2F')
                $.ajax({
	                cache: false,
	                type: "POST",
	                data: 'node_edit_revision_content=' + escape($('#nerc').attr('value'), true),
	                url: $(this).attr('href'),
	                success: function(output) {
	                    var callback = function() {
	                        $('div.modform').remove();
                            $(old).parents('li').append(output);
		                    $('.modform .wysiwyg').wymeditor();
		                    $('.modform input, .modform textarea').blur(function() {
		                        update_modform(old);
		                    });
		                    $('.modform').click(function() {
		                        if ($.wymeditors(wi) != undefined) {
		                            $.wymeditors(wi).update();
		                        }
		                    });
		                    
		                    $('.modform a').click(function() {
		                        $.ajax(
		                        {
	                                cache: false,
	                                type: "POST",
	                                data: 'nerc=' + escape($('#nerc').attr('value'), true),
	                                url: $(this).attr('href'),
	                                success: function(output) {
	                                    if ($.wymeditors(wi) != undefined) {
		                                    wi++;
		                                }
	                                    $(old).parents('li').eq(0).before(output);
	                                    $(old).parents('li').eq(0).remove();
	                                    build_modules();
	                                }
	                            });
		                    
		                        return false;
		                    });
		                    
		                    if ($.browser.msie) {
		                        $('div.modform').show(); // IE seems to hide form elements otherwise
		                    } else {
	                            $('div.modform').slideDown('normal');
	                        }
	                    };
	                    
	                    if ($('div.modform').length > 0) {
	                    	update_modform($('div.modform').parents('li').eq(0).find('a').get(0), function() {
	                    	    $('div.modform').slideUp('normal', callback);
		                        if ($.wymeditors(wi) != undefined) {
		                            wi++;
		                        }
                            });
	                    } else {
	                        callback();
	                    }
	                }
	            });
            }
            
            return false;
        });
    });
}

function update_modform(base, callback, params) {
    if ($.wymeditors(wi) != undefined) {
	    $.wymeditors(wi).update();
	}

    var fields = $('.modform input, .modform textarea, #nerc').fieldSerialize();
    $.ajax({
        cache: false,
        type: "POST",
        url: $(base).attr('href'),
        data: fields,
        success: function(output) {
            $('#nerc').attr('value', output);
            if (callback) {
                if (params) {
                    callback(params);
                } else {
                    callback();
                }
            }
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

		if($.browser.msie)
			$(this).get(0).style.removeAttribute('filter');
		
		percentage = 100 / $(this).find('.pane').length;
		$(this).find('.pane').height(percentage + '%');
		$(this).find('.pane .content').height(percentage - 2 + '%');
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
		
		ie_position_fix();
	}
}
