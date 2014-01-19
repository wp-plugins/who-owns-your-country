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
});
