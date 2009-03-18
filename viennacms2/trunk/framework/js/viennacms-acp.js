WYMeditor.XhtmlLexer.prototype.addTokens = function() {
  // These tokens are for Radiant CMS radius tags  
  this.addEntryPattern("</?viennacms:", 'Text', 'Text');
  this.addExitPattern(">", 'Text');

  this.addCommentTokens('Text');
  this.addScriptTokens('Text');
  this.addCssTokens('Text');
  this.addTagTokens('Text');
};

var dragging = false;

function create_init_panes(loc, data) {
	for (i = 0; i < data.length; i++) {
		loc.append('<li class="' + data[i].aclass + '"><a href="' + data[i].href + '">' + data[i].title + '</a></li>');
	}
	
	//loc.addClass('active');
}

$(function() {
	var thickDims = function() {
		var tbWindow = $('#TB_window');
		var H = $(window).height();
		var W = $(window).width();

		if ( tbWindow.size() ) {
			tbWindow.width( W - 90 ).height( H - 60 );
			$('#TB_iframeContent').width( W - 90 ).height( H - 90 );
			tbWindow.css({'margin-left': '-' + parseInt((( W - 90 ) / 2),10) + 'px'});
			if ( typeof document.body.style.maxWidth != 'undefined' )
				tbWindow.css({'top':'30px','margin-top':'0'});
		}

		return $('a.thickbox').each( function() {
			var href = $(this).parents('.available-theme').find('.previewlink').attr('href');
			if ( ! href ) return;
			href = href.replace(/&width=[0-9]+/g, '');
			href = href.replace(/&height=[0-9]+/g, '');
			$(this).attr( 'href', href + '&width=' + ( W - 110 ) + '&height=' + ( H - 100 ) );
		});
	};

	thickDims()
	.click( function() {
		var alink = $(this).parents('.available-theme').find('.activatelink');
		var url = alink.attr('href');
		var text = alink.html();

		$('#TB_title').css({'background-color':'#222','color':'#cfcfcf'});
		$('#TB_closeAjaxWindow').css({'float':'left'});
		$('#TB_ajaxWindowTitle').css({'float':'right'})
			.append('&nbsp;<a href="' + url + '" target="_top" class="tb-theme-preview-link">' + text + '</a>');

		$('#TB_iframeContent').width('100%');
		return false;
	} );

	$(window).resize( function() { thickDims() } );
	
/*	$.getJSON(
		pane_url,
		function(data) {
			create_init_panes($('#panes-left ul.tabs'), data.left);
			init_rest();
		}
	);*/
	
	reload_pane_containers(false);
	update_content();

	$(".treeview").treeview({
		collapsed: true,
		unique: true,
		persist: 'cookie'
	});

	$('.toolbar li a').click(function() {
	    url = $(this).attr('href');

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
                    };
                    
                    if ($('ul.submenu').length > 0) {
	                    $('ul.submenu').slideUp('normal', callback);
                    } else {
                        callback();
                    }
                }
            });
			
				    
		    return false;
	    }
	});
	
	if ($('#main-content .toolbar').length > 0) {
		$('#main-content').css('padding-top', '28px');
	}
});

function init_rest() {
		
/* this stuff is disabled... for now ;)
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
*/
	
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

var global_pane_param;

function load_active_panes() {
	$('.panes').each(function() {
		$(this).find('li.active a').each(function() {
			load_pane($(this).attr('href').replace('%parameter', global_pane_param), $(this));
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
				if ($(this).parents('.pane').find('a.selected').parents('li').length > 0) { 
			        global_pane_param = $(this).parents('.pane').find('a.selected').parents('li').attr('id').replace('node-', '');
			    } else {
			        global_pane_param = 0;
			    }

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
	
/*	$('#main-content form').ajaxForm()
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
    });*/
	$('#main-content form').submit(function() {
		if ($.wymeditors(wi) != undefined) {
	        $.wymeditors(wi).update();
	        wi++;
	    }
		
		if ($('div.modform').length > 0 && mf == false) {
			mycallback = $(this);
			
	        update_modform($('div.modform').parents('li').eq(0).find('a').get(0), function() {
				mf = true;
				mycallback.find('input[@type=submit]').click(); // simply calling submit() won't work, FORM_ID_submit won't be added
			});
			return false;
	    }
	});
    
    //$('.modules').sortable();
    build_modules();
}

var mf = false;

function build_modules() {
    $('.modules li').each(function() {
        $(this).find('a.module').click(function() {
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
		
		$(this).find('a.delete-module').click(function() {
			var old = this;
			
			if (!window.confirm("Do you really want to remove this module?")) {
				return false;
			}
			
			$.ajax({
	                cache: false,
	                type: "POST",
	                data: 'node_edit_revision_content=' + escape($('#nerc').attr('value'), true),
	                url: $(this).attr('href'),
	                success: function(output) {
						$('#nerc').attr('value', output);
						$(old).parents('li').eq(0).remove();
	                }
			});
			
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
		if ($(this).find('.pane').length == 0) {
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
		$(this).find('.pane .content').height('96%');
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
