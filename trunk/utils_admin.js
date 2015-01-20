//Delete files through ajax
function whoowns_file_delete(file_name, post_id, element_id) {
	//Is the user sure that he/she wants to delete the file?
	if (confirm(whoowns_admin_ajax_object.delete_confirmation.replace('{file}',file_name))) {
		//Let's delete the file!
		var params = {
			action: 'whoowns_delete_file',
			file_name: file_name,
			element_id: element_id, 
			post_id: post_id
		};
		jQuery.post(whoowns_admin_ajax_object.ajax_url, params, function(response) {
			// I only do the alert if the file deletion did not work
			if (response)
				alert(response);
			// Hide file from the list:
			jQuery("#"+params.element_id).hide();
		});
	}
}

//Show/hide element
function whoowns_toggle(toggler,target,msg1,msg2) {
	jQuery("#"+target).toggle();
	var content = (jQuery("#"+toggler).html()==msg1) 
		? msg2
		: msg1;
	jQuery("#"+toggler).html(content);
}
