/**
 * viennaCMS inline editing JS file.
 **/
 
$(document).ready(function () {
	$('.module-c').append('<div style="text-align: right;" class="clicky"><a href="#" onclick="inlineEdit(this.parentNode.parentNode); return false;">e</a></div>');
});

function inlineEdit(module) {
	dmod = '#' + module.id;
	
	$.ajax({
		cache: false,
		type: "POST",
		url: inlineedit_cb + "form",
		data: "id=" + module.id,
		success: function(output) {
			module.innerHTML = output;
			reinit_wysiwyg();
			$(dmod + ' form').submit(function() {
		        var string = $(dmod + ' form').formSerialize(false);
		        string = string + '&id=' + module.id;
		        $.post(
		                inlineedit_ad + '?ajax&mode=ajax-save',
		                string,
		                function(data) {
		                		//module.innerHTML = data;
		                        reload_module(module);
		                }
		        );
		        return false;
			})
		}
	});
}

function reload_module(module) {
	$.ajax({
		cache: false,
		type: "POST",
		url: inlineedit_cb + "getmodule",
		data: "id=" + module.id,
		success: function(output) {
			module.parentNode.parentNode.innerHTML = output;
			dmod = '#' + module.id;
			$('.clicky').remove();
			$('.module-c').append('<div style="text-align: right;" class="clicky"><a href="#" onclick="inlineEdit(this.parentNode.parentNode); return false;">e</a></div>');
		}
	});
}

function reinit_wysiwyg() {
	
			// Add submit triggers
			if (document.forms && tinyMCE.settings.add_form_submit_trigger && !tinyMCE.submitTriggers) {
				for (i=0; i<document.forms.length; i++) {
					form = document.forms[i];

					tinyMCE.addEvent(form, "submit", TinyMCE_Engine.prototype.handleEvent);
					tinyMCE.addEvent(form, "reset", TinyMCE_Engine.prototype.handleEvent);
					tinyMCE.submitTriggers = true; // Do it only once

					// Patch the form.submit function
					if (tinyMCE.settings.submit_patch) {
						try {
							form.mceOldSubmit = form.submit;
							form.submit = TinyMCE_Engine.prototype.submitPatch;
						} catch (e) {
							// Do nothing
						}
					}
				}
			}

	
				tinyMCE.settings = tinyMCE.configs[0];

			selector = tinyMCE.getParam("editor_selector");
			deselector = tinyMCE.getParam("editor_deselector");
			elementRefAr = [];
	
				mode = tinyMCE.settings.mode;
			switch (mode) {
				case "exact":
					elements = tinyMCE.getParam('elements', '', true, ',');

					for (i=0; i<elements.length; i++) {
						element = tinyMCE._getElementById(elements[i]);
						trigger = element ? element.getAttribute(tinyMCE.settings.textarea_trigger) : "";

						if (new RegExp('\\b' + deselector + '\\b').test(tinyMCE.getAttrib(element, "class")))
							continue;

						if (trigger == "false")
							continue;

						if ((tinyMCE.settings.ask || tinyMCE.settings.convert_on_click) && element) {
							elementRefAr[elementRefAr.length] = element;
							continue;
						}

						if (element)
							tinyMCE.addMCEControl(element, elements[i]);
					}
				break;

				case "specific_textareas":
				case "textareas":
					elements = document.getElementsByTagName("textarea");

					for (i=0; i<elements.length; i++) {
						elm = elements.item(i);
						trigger = elm.getAttribute(tinyMCE.settings.textarea_trigger);

						if (selector !== '' && !new RegExp('\\b' + selector + '\\b').test(tinyMCE.getAttrib(elm, "class")))
							continue;

						if (selector !== '')
							trigger = selector !== '' ? "true" : "";

						if (new RegExp('\\b' + deselector + '\\b').test(tinyMCE.getAttrib(elm, "class")))
							continue;

						if ((mode == "specific_textareas" && trigger == "true") || (mode == "textareas" && trigger != "false"))
							elementRefAr[elementRefAr.length] = elm;
					}
				break;
			}

			for (i=0; i<elementRefAr.length; i++) {
				element = elementRefAr[i];
				elementId = element.name ? element.name : element.id;

				if (tinyMCE.settings.ask || tinyMCE.settings.convert_on_click) {
					// Focus breaks in Mozilla
					if (tinyMCE.isGecko) {
						settings = tinyMCE.settings;

						tinyMCE.addEvent(element, "focus", function (e) {window.setTimeout(function() {TinyMCE_Engine.prototype.confirmAdd(e, settings);}, 10);});

						if (element.nodeName != "TEXTAREA" && element.nodeName != "INPUT")
							tinyMCE.addEvent(element, "click", function (e) {window.setTimeout(function() {TinyMCE_Engine.prototype.confirmAdd(e, settings);}, 10);});
						// tinyMCE.addEvent(element, "mouseover", function (e) {window.setTimeout(function() {TinyMCE_Engine.prototype.confirmAdd(e, settings);}, 10);});
					} else {
						settings = tinyMCE.settings;

						tinyMCE.addEvent(element, "focus", function () { TinyMCE_Engine.prototype.confirmAdd(null, settings); });
						tinyMCE.addEvent(element, "click", function () { TinyMCE_Engine.prototype.confirmAdd(null, settings); });
						// tinyMCE.addEvent(element, "mouseenter", function () { TinyMCE_Engine.prototype.confirmAdd(null, settings); });
					}
				} else
					tinyMCE.addMCEControl(element, elementId);
			}
}