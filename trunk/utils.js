jQuery(document).ready(function($) {
	$(".whoowns_auto_label").autocomplete({
	    //define callback to format results
	    source: function(req, add){
			//pass request to server
			//The alt attribute contains the wordpress callback action
			var params = {
				action: this.element[0].alt,
				term: req.term
			};
			$.getJSON(whoowns_ajax_object.ajax_url, params, function(data) {
				//pass array to callback
				add(data);
            });
		},

       	minLength: 3,
       	
       	selectFirst: true, 

		focus: function( event, ui ) {
			$(this).val(ui.item.label);
			$(this).next(".whoowns_auto_id").val(ui.item.value);
            return false;
		},

        select: function(e, ui) {
        	if ($(this).attr('trigger')=='submit') {
            	$(this).closest('form').submit();
            }
            var label = ui.item.label;
            $(this).val(label);
			$(this).next(".whoowns_auto_id").val(ui.item.value);
			return false;
            },
        change: function(e, ui) {
        	// If the user didn't select any item from the list, the item will be erased
			if ( !ui.item && !whoowns_ajax_object.autocomplete_allow_non_listed_value) {
				$(this).val("");
				$(this).next(".whoowns_auto_id").val("");
			}
		}
	});
	
	/* Auto-adjust factsheet columns */	
	if (jQuery('#whoowns-factsheet').length>0) {
		jQuery(window).resize(function(){
			whoowns_adjust_columns();
		});
		whoowns_adjust_columns();
	}
	function whoowns_adjust_columns() {
		var o = jQuery("#whoowns-factsheet .one_half");
		var t = jQuery("#whoowns-factsheet .two_half_last");
		o.height('auto');
		if (o.offset().top == t.offset().top && o.height()-100<t.height()) {
			o.height(t.height()+120);
		}
	}
	
	
	/* Toggle */
	jQuery(document).ready(function() {
		jQuery('.whoowns-toggle .whoowns-toggle_title').click(function (b) { 
			var toggled = jQuery(this).parent().find('.whoowns-toggle_content');
			
			jQuery(this).parent().find('.whoowns-toggle_content').not(toggled).slideUp();
			
			if (jQuery(this).hasClass('current')) {
				jQuery(this).removeClass('current');
			} else {
				jQuery(this).addClass('current');
			}
			
			toggled.stop(false, true).slideToggle().css( { 
				display : 'block' 
			} );
			
			b.preventDefault();
		} );
		
	});

});

/* From http://www.appelsiini.net/download/jquery.viewport.js . Thanks! */
jQuery.abovethetop = function(element, settings) {
	var top = jQuery(window).scrollTop();
	return top >= jQuery(element).offset().top + jQuery(element).height() - settings.threshold;
};
jQuery.extend(jQuery.expr[':'], {
	"above-the-top": function(a, i, m) {
		return jQuery.abovethetop(a, {threshold : 100});
	}
});

function whoowns_submit_search(e) {
	if (jQuery("#"+e.id).prop('checked')==true) {
		if (e.id=='checkbox-ranked') {
			jQuery("input:checkbox[name='whoowns_filters[]']:checked").each(function() {
				if (jQuery(this).val()!='ranked')
					jQuery(this).prop('checked', false);
			});
		} else if (e.id=='checkbox-all') {
			jQuery("input:checkbox[name='whoowns_filters[]']:checked").each(function() {
				if (jQuery(this).val()!='all')
					jQuery(this).prop('checked', false);
			});
		} else {
			jQuery("#checkbox-all,#checkbox-ranked").prop("checked", false);
		}
	}
	document.whoowns_search_form.submit();
}

function whoowns_open_popup(id) {
		var whoowns_popup_overlay = jQuery('#'+id+'-overlay');
		whoowns_popup_overlay.css({'opacity': 0, 'display': 'block'});
		whoowns_popup = jQuery('#'+id);
		whoowns_popup.css({'opacity': 0, 'display': 'block'});
		whoowns_popup_h = whoowns_popup.find('.'+id+'-wrap').outerHeight();
		whoowns_popup_overlay.fadeTo(200, 0.55);

		whoowns_popup.css( "z-index", "100002" );
		whoowns_popup.animate({'opacity':1, 'height': whoowns_popup_h+20}, 200);
		
		jQuery('#'+id+'-overlay, #'+id+'-close').click(function() {
			whoowns_popup_overlay.fadeOut();
			whoowns_popup.animate({'opacity' : 0, 'height' : 0}, 200, function(){whoowns_popup.css( "z-index", "0" );});
		});
}

