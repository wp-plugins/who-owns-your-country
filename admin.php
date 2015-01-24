<?php

//Creation of the meta_box for owner's shareholders and metadata (other infos)
function whoowns_meta_box_add() {  
	add_meta_box( 'whoowns_owner_details', __('Details','whoowns'), 'whoowns_meta_box_details', 'whoowns_owner', 'normal', 'core');
	add_meta_box( 'whoowns_related_owners', __('Related Owners','whoowns'), 'whoowns_meta_box_related_owners', 'post', 'side', 'high');
	add_meta_box( 'whoowns_related_owners', __('Related Owners','whoowns'), 'whoowns_meta_box_related_owners', 'page', 'side', 'high');
}
add_action( 'add_meta_boxes', 'whoowns_meta_box_add' );

function whoowns_clean_metaboxes() {
	remove_meta_box( 'postimagediv','whoowns_owner','side' ); // Featured Image Metabox
	add_meta_box( 'postimagediv', __('Owner Image','whoowns'), 'post_thumbnail_meta_box', 'whoowns_owner', 'normal', 'high');

	/*if(current_user_can('publish_whoowns_owners'))
		return;*/
	if(current_user_can('publish_posts'))
		return;
	//I remove the following meta_boxes for common users (not admin or editors):
	remove_meta_box( 'authordiv','whoowns_owner','normal' ); // Author Metabox
	remove_meta_box( 'commentstatusdiv','whoowns_owner','normal' ); // Comments Status Metabox
	remove_meta_box( 'commentsdiv','whoowns_owner','normal' ); // Comments Metabox
	remove_meta_box( 'revisionsdiv','whoowns_owner','normal' ); // Revisions Metabox
	remove_meta_box( 'slugdiv','whoowns_owner','normal' ); // Slug Metabox
	remove_meta_box( 'trackbacksdiv','whoowns_owner','normal' ); // Trackback Metabox
	remove_meta_box( 'categorydiv','whoowns_owner','normal' ); // Categories Metabox
	remove_meta_box( 'formatdiv','whoowns_owner','normal' ); // Formats Metabox
	remove_meta_box( 'tagsdiv-post_tag','whoowns_owner','normal' ); // Tags Metabox
}
add_action('do_meta_boxes','whoowns_clean_metaboxes');

function whoowns_meta_box_details ($post) {

	// Nonce field for checking when saving.
	wp_nonce_field( 'whoowns_nonce', 'meta_box_nonce' );
	
	//Registration ID
	$legal_registration = get_post_meta($post->ID, 'whoowns_legal_registration',true);
	?>
	<h3><?=__('Legal registration ID','whoowns')?></h3>
	<p><label for="whoowns_legal_registration"><?=__('What is the formal registration identity of this owner?','whoowns')?></label>
	<input type="text" size="14" name="whoowns_legal_registration" id="whoowns_legal_registration" value="<?=$legal_registration?>" />
	</p>
	<?php

	//Owner's TYPE:
	$owner_types = get_terms('whoowns_owner_types', 'hide_empty=0');
	$whoowns_owner_type = wp_get_post_terms($post->ID, 'whoowns_owner_types');
	$selected_type = (isset($whoowns_owner_type[0]->term_id)) ? $whoowns_owner_type[0]->term_id : '';
	?>
	<h3><?=__('Type of this Owner','whoowns')?></h3>
	<p class="description"><?=__('Is this owner an enterprise? a person? other type? Please choose below:','whoowns')?></p>
	<p>
		<label for="whoowns_owner_type"><?=__('Type','whoowns')?></label>
		<select name="whoowns_owner_type" id="whoowns_owner_type">
			<option value=""><?=__('None', 'whoowns')?></option>
		<?php
		foreach ($owner_types as $t) {
			?>
			<option value="<?=$t->term_id?>" <?php selected($selected_type, $t->term_id); ?>><?=__($t->name,'whoowns')?></option>
			<?php
		}
		?>
		</select>
	</p>
	
	<?php
	
	// DBpedia URI:
	$dbpedia_uri = get_post_meta($post->ID, 'whoowns_dbpedia_uri',true);
	?>
	<h3><?=__('URI in dbpedia','whoowns')?></h3>
	<p class="description"><?=__('Leave it blank if you don\'t know what dbpedia is.','whoowns')?></p>
	<p><label for="whoowns_dbpedia_uri"><?=__('URI in dbpedia:','whoowns')?></label>
	<input type="text" size="60" name="whoowns_dbpedia_uri" id="whoowns_dbpedia_uri" value="<?=$dbpedia_uri?>" />
	</p>
	<?php
	
	
	
	
	//Revenues of the owner
	$revenue = get_post_meta($post->ID, 'whoowns_revenue',true);
	$selected_revenue_months = (isset($revenue['months'])) ? $revenue['months'] : '12';
	?>
	<h3><?=__('Net revenue','whoowns')?></h3>
	<p class="description"><?=__('What is the net revenue of this owner? You must also define the date and period of this information, along with the source','whoowns')?></p>
	<p><label for="whoowns_revenue_value"><?=__('Net revenue in $ millions:','whoowns')?></label>
	<input type="text" size="6" name="whoowns_revenue_value" id="whoowns_revenue_value" value="<?=(isset($revenue['value'])) ? whoowns_set_decimal_symbol($revenue['value']) : ''?>" />
	</p>
	<p><label for="whoowns_revenue_year"><?=__('From what year is this information?','whoowns')?></label>
	<input type="text" size="4" name="whoowns_revenue_year" id="whoowns_revenue_year" value="<?=isset($revenue['year']) ? $revenue['year'] : ''?>" />
	</p>
	<p><label for="whoowns_revenue_months"><?=__('This revenue is for how many months?','whoowns')?></label>
	<select name="whoowns_revenue_months" id="whoowns_revenue_months">
			<option value=""><?=__('None', 'whoowns')?></option>
		<?php
		for ($m=1;$m<13;$m++) {
			switch ($m) {
				case 3:
					$mtxt=$m.' ('.__('trimester','whoowns').')';
				break;
				case 6:
					$mtxt=$m.' ('.__('semester','whoowns').')';
				break;
				case 12:
					$mtxt=$m.' ('.__('year','whoowns').')';
				break;
				default:
					$mtxt=$m;
				break;
			}
		?>
			<option value="<?=$m?>" <?php selected($selected_revenue_months, $m); ?>><?=$mtxt?></option>
			<?php
		}
		?>
	</select>
	</p>
	<p><label for="whoowns_revenue_source_name"><?=__('What is the name of the source of this information?','whoowns')?></label><br />
	<textarea COLS="50" ROWS="2" name="whoowns_revenue_source_name" id="whoowns_revenue_source_name"><?=(isset($revenue['source_name'])) ? $revenue['source_name'] : ''?></textarea>
	</p>
	<p><label for="whoowns_revenue_source_url"><?=__('What is the internet address of this source?','whoowns')?></label>
	<input type="text" name="whoowns_revenue_source_url" id="whoowns_revenue_source_url" value="<?=isset($revenue['source_url']) ? $revenue['source_url'] : ''?>" />
	</p>
	
	<?php
	
	//Owner's owners (shareholders)
	$owners = whoowns_get_direct_shareholders( $post->ID );
	$num_existing_owners=count($owners);
	for ($i=$num_existing_owners;$i<get_option('whoowns_default_shareholders_number');$i++) {
		if (!$owners[$i])
			$owners[$i] = new stdClass();
		$owners[$i]->shareholder_id='';
	}
	?>
	<h3><?=__('Owners and shares','whoowns')?></h3>
	<p class="description"><?=__('Who owns this enterprise? The information must be validated by source documents from a Junta Comercial or from the Receita Federal','whoowns')?></p>
	<table>
		<thead>
		<tr>
			<th><label for="whoowns_shareholder_id"><?=__('Name','whoowns')?></label></th>
			<th><label for="whoowns_share"><?=__('Ownership Shares (in percentage)','whoowns')?></label></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($owners as $i=>$owner) {
			$owner_name = ($i<$num_existing_owners && get_post_status($owner->shareholder_id)=='pending' )
				? $owner->shareholder_name . " (".__('Pending', 'whoowns').")"
				: $owner->shareholder_name;
			
			if ($i>$num_existing_owners && $i>=3 && !$done) {
				$done=true;
			?>
				</tbody>
				</table>
				<p><a id='whoowns_toggle_more_shareholders' href='javascript:whoowns_toggle("whoowns_toggle_more_shareholders","whoowns_more_shareholders","<b>+</b> <?=__("Add more shareholders...","whoowns")?>","<b>-</b> <?=__("Hide","whoowns")?>");'><b>+</b> <?=__('Add more shareholders...','whoowns')?></a></p>
				<table id='whoowns_more_shareholders' style='display:none'>
				<tbody>
			<?php
			}
		?>
			<tr>
				<td>
				<input class="whoowns_auto_label" type="text" name="whoowns_shareholder_name-<?=$i?>" alt="whoowns_autocomplete" id="whoowns_shareholder_name-<?=$i?>" value="<?=$owner->shareholder_name?>" size="50"/>
				<input class="whoowns_auto_id" type="hidden" name="whoowns_shareholder_id-<?=$i?>" id="whoowns_shareholder_id-<?=$i?>" value="<?=$owner->shareholder_id?>"/>
				</td>
				<td style="text-align:center"><input type="text" name="whoowns_share-<?=$i?>" id="whoowns_share-<?=$i?>" value="<?=whoowns_set_decimal_symbol($owner->share)?>" size="5"/>%</td>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table>
	<?php
	
	// Source of the shareholders data
	$shareholders_source = get_post_meta($post->ID, 'whoowns_shareholders_source',true);
	$shareholders_source_year = get_post_meta($post->ID, 'whoowns_shareholders_source_year',true);
	$shareholders_source_files = get_post_meta($post->ID, 'whoowns_shareholders_source_files');
	?>
	<br />
	<h3><?=__('Source of shareholders data','whoowns')?></h3>
	<p>
	<label for='whoowns_shareholders_source'><?=__('Where did you get the shareholders data from?','whoowns')?></label><br />
	<textarea COLS="50" ROWS="2" name="whoowns_shareholders_source" id="whoowns_shareholders_source"><?=$shareholders_source?></textarea>
	</p>
	<p>
	<label for='whoowns_shareholders_source_year'><?=__('Year of this information:','whoowns')?></label>
	<input type="text" size="4" name="whoowns_shareholders_source_year" id="whoowns_shareholders_source_year" value="<?=$shareholders_source_year?>" />
	</p>
	<p class="description"><?=__('Please attach the documents to validate the above mentioned source:','whoowns')?></p>
	<ul>
	<?php
	foreach($shareholders_source_files as $i=>$shareholders_source_file) {
		$li = "whoowns_shareholders_source_file_".intval($i);
		?>
		<li id="<?=$li?>"><?=$shareholders_source_file['name']?> <a href="javascript:whoowns_file_delete('<?=$shareholders_source_file['name']?>','<?=$post->ID?>','<?=$li?>');" class="whoowns_file_delete"><?=__('Delete File')?></a></li>
	<?php }
	for ($j=$i+1;$j<=$i+3;$j++) {
	?>
		<li><label for="whoowns_shareholders_source_file_<?=intval($j)?>"><?=__('Add document','whoowns')?></label>
		<input id="whoowns_shareholders_source_file_<?=intval($j)?>" name="whoowns_shareholders_source_file_<?=intval($j)?>" value="" size="25" type="file"></li>
	<?php } ?>
	</ul>
	<?php
}






function whoowns_meta_boxes_save( $post_id ) {
    // Bail if we're doing an auto save
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	// if our nonce isn't there, or we can't verify it, bail
	if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'whoowns_nonce' ) ) return;
	
	// if our current user can't edit this post, bail
	#if( !current_user_can( 'edit_whoowns_owners' ) ) return;
	if( !current_user_can( 'edit_posts' ) ) return;
	
	// If this is just a revision, bail
	if ( wp_is_post_revision( $post_id ) )
		return;
	
	// if it is not a whoowns_owner type of post, bail
	if ( $_POST['post_type'] != 'whoowns_owner') return;
	
	//save the registration id:
	update_post_meta($post_id, 'whoowns_legal_registration', $_POST['whoowns_legal_registration']);
	
	// save the type of the owner:
	if ($_POST['whoowns_owner_type'])
		wp_set_object_terms( $post_id, intval($_POST['whoowns_owner_type']), 'whoowns_owner_types');
	else
		wp_delete_object_term_relationships( $post_id, 'whoowns_owner_types'); 
	
	//save the dbpedia URI:
	update_post_meta($post_id, 'whoowns_dbpedia_uri', $_POST['whoowns_dbpedia_uri']);
	
	// save its revenue data
	$revenue = array (
		'value'=>whoowns_set_decimal_symbol($_POST['whoowns_revenue_value'],'.'),
		'year'=>$_POST['whoowns_revenue_year'],
		'months'=>$_POST['whoowns_revenue_months'],
		'source_name'=>$_POST['whoowns_revenue_source_name'],
		'source_url'=>$_POST['whoowns_revenue_source_url']
	);
	$changed_revenue = update_post_meta($post_id, 'whoowns_revenue', $revenue);
	
	//Save the name of the source of the owner's shareholders:
	update_post_meta($post_id, 'whoowns_shareholders_source', $_POST['whoowns_shareholders_source']);
	
	//Save the year of the source of the owner's shareholders:
	update_post_meta($post_id, 'whoowns_shareholders_source_year', $_POST['whoowns_shareholders_source_year']);
	
	// save the file(s) of the source:
	if(!empty($_FILES)) {
		
		foreach ($_FILES as $f=>$file) {
			if(substr($f,0,33)=='whoowns_shareholders_source_file_' && !empty($file['name'])) {
          
		        // Get the file type of the upload  
		        $arr_file_type = wp_check_filetype(basename($file['name']));  
		        $uploaded_type = $arr_file_type['type'];  

		        // Check if the type is supported. If not, throw an error.  
		        if(in_array($uploaded_type, get_option('whoowns_supported_file_types'))) {
  
	 		        // Use the WordPress API to upload the file  
		            $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));  
		            if(isset($upload['error']) && $upload['error'] != 0) {  
						//TODO: Discover how to pass an error message to the user after saving the changes
						//wp_die('There was an error uploading your file. The error is: ' . $upload['error']);  
		            } else {
		            	$upload['name']=$file['name'];
		                add_post_meta($post_id, 'whoowns_shareholders_source_files', $upload);
		            }
  
		        } else {
		        	//TODO: Discover how to pass an error message to the user after saving the changes
		            //wp_die(__("The file type that you've uploaded is not supported. Please try a different type."));  
		        }
			}
		} //end of loop in uploaded files      
    }
		
    // Prepare the POST data for saving the shareholders:
    $shareholders=array();
    foreach ($_POST as $f=>$value) {
    	if (substr($f,0,20)=='whoowns_shareholder_' || substr($f,0,14)=='whoowns_share-'){
			list($shareholder_attr,$shareholder_id) = explode('-',str_replace('whoowns_','',$f));
			if (!$shareholders[$shareholder_id])
				$shareholders[$shareholder_id] = new stdClass();
   			$shareholders[$shareholder_id]->$shareholder_attr = $value;
    	}
    }
    // Now we can actually save the data
    $changed_shares = whoowns_update_shareholders($post_id, $shareholders);
    #pR($changed_shares || $changed_revenue);exit;
    // If the shares or the revenue changed, it's necessary to do recalculations: Erase the network cache of all related nodes, Schedule events to refill the cache and to calculate the new accumulated power values for the whole affected nodes and finally recalculate the IPA and ranking of the whole database:
    #pR($changed_shares);exit;
    if ($_POST['post_status']=='publish' && ($changed_revenue || count($changed_shares)>0 || get_post_status($post_id)=='pending'))
    	whoowns_init_owner_universe_update($post_id, $changed_shares);
}
add_action( 'save_post', 'whoowns_meta_boxes_save' );

function whoowns_meta_boxes_trashed( $post_id ) {
	global $whoowns_tables, $wpdb;
	// if our current user can't edit this post, bail
	#if( !current_user_can( 'delete_whoowns_owners' ) ) return;
	if( !current_user_can( 'delete_posts' ) ) return;
	
	if (get_post_status( $post_id )=='publish') {
    	whoowns_init_owner_universe_update($post_id, true);
		$wpdb->query( $wpdb->prepare(  "DELETE FROM ".$whoowns_tables->shares." WHERE to_id = %d", $post_id ) );
		$wpdb->query( $wpdb->prepare(  "DELETE FROM ".$whoowns_tables->networks_cache." WHERE post_id = %d", $post_id ) );
	}
	return true;
}
add_action( 'wp_trash_post', 'whoowns_meta_boxes_trashed');
add_action( 'untrashed_post', 'whoowns_meta_boxes_trashed');





function whoowns_meta_boxes_delete( $post_id ) {
	global $whoowns_tables, $wpdb;
	// if our current user can't edit this post, bail
	#if( !current_user_can( 'delete_whoowns_owners' ) ) return;
	if( !current_user_can( 'delete_posts' ) ) return;
	
   	whoowns_init_owner_universe_update($post_id, true);
	$wpdb->query( $wpdb->prepare(  "DELETE FROM ".$whoowns_tables->shares." WHERE to_id = %d", $post_id ) );
	$wpdb->query( $wpdb->prepare(  "DELETE FROM ".$whoowns_tables->shares." WHERE from_id = %d", $post_id ) );
	$wpdb->query( $wpdb->prepare(  "DELETE FROM ".$whoowns_tables->networks_cache." WHERE post_id = %d", $post_id ) );
	return true;
}
add_action( 'delete_post', 'whoowns_meta_boxes_delete', 10 );





function whoowns_meta_box_related_owners ($post) {

	// Nonce field for checking when saving.
	wp_nonce_field( 'whoowns_related_nonce', 'meta_box_related_nonce' );
	
	//Related owners
	$tmp = whoowns_get_owner_data(get_post_meta( $post->ID, 'whoowns_related_owner' ));
	$owners = (is_array($tmp))
		? $tmp
		: array($tmp);
	#pR($owners);exit;
	$num_existing_owners=count($owners);
	for ($i=$num_existing_owners;$i<get_option('whoowns_default_shareholders_number');$i++) {
		if (!isset($owners[$i]))
			$owners[$i] = new stdClass();
		$owners[$i]->ID='';
	}
	?>
	<div>
	<p class="description"><?=__('Who are the owners related to this article?','whoowns')?></p>
		<?php
		foreach ($owners as $i=>$owner) {
			if ($i>$num_existing_owners && $i>=3 && !$done) {
				$done=true;
			?>
				</div>
				<p><a id='whoowns_toggle_more_related_owners' href='javascript:whoowns_toggle("whoowns_toggle_more_related_owners","whoowns_more_related_owners","<b>+</b> <?=__("Add more...","whoowns")?>","<b>-</b> <?=__("Hide","whoowns")?>");'><b>+</b> <?=__('Add more...','whoowns')?></a></p>
				<div id='whoowns_more_related_owners' style='display:none'>
			<?php
			}
		?>
				<input class="whoowns_auto_label" type="text" name="whoowns_related_owner_name-<?=$i?>" alt="whoowns_autocomplete" id="whoowns_related_owner_name-<?=$i?>" value="<?=$owner->name?>" size="30"/>
				<input class="whoowns_auto_id" type="hidden" name="whoowns_related_owner_id-<?=$i?>" id="whoowns_related_owner_id-<?=$i?>" value="<?=$owner->ID?>"/>
		<?php
		}
		?>
		</div>
	<?php
}





function whoowns_related_owners_meta_box_save( $post_id ) {
    // Bail if we're doing an auto save
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	// if our nonce isn't there, or we can't verify it, bail
	if( !isset( $_POST['meta_box_related_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_related_nonce'], 'whoowns_related_nonce' ) ) return;
	
	// if our current user can't edit this post, bail
	if( !current_user_can( 'edit_post' ) && !current_user_can( 'edit_page' )) return;
	
	// if it is not a whoowns_owner type of post, bail
	if ( !in_array($_POST['post_type'], array('post', 'page'))) return;
	
	// Prepare the POST data for saving the shareholders:
	delete_post_meta($post_id, 'whoowns_related_owner');
	#pR($_POST);exit;
    foreach ($_POST as $f=>$value) {
    	if (substr($f,0,25)=='whoowns_related_owner_id-' && $value){
			add_post_meta($post_id, 'whoowns_related_owner', $value);
    	}
    }
}
add_action( 'save_post', 'whoowns_related_owners_meta_box_save' );





function whoowns_delete_file_callback() {
	global $wpdb;
	
	$file_name=$_REQUEST['file_name'];
	$post_id=$_REQUEST['post_id'];
	
	if (!$file_name || !$post_id)
		die(__('Error: you did not specify valid parameters for this callback','whoowns'));
		
	$files = get_post_meta($post_id, 'whoowns_shareholders_source_files');
	foreach ($files as $file)
		if ($file['name']==$file_name) {
			delete_post_meta($post_id, 'whoowns_shareholders_source_files', $file);
			if (unlink($file['file'])) {
				die();
			} else {
				die(__('Error: I was not able to delete the file. Therefore, I only erased the associated metadata','whoowns'));
			}
		}
		
	die(__('Error: you did not specify valid parameters for this callback','whoowns'));
}
add_action('wp_ajax_whoowns_delete_file', 'whoowns_delete_file_callback');

function whoowns_initialize_update_schedule() {
	$frequency = get_option('whoowns_cron_frequency');
	if ( !in_array($frequency, array_keys(wp_get_schedules())) )
		$frequency = 'hourly';
	$date = date('G-i-s',current_time('timestamp'));
	list($h,$m,$s) = explode('-',$date);
	if ($frequency=='hourly') {
		$interval = 3600 - $m*60 - $s;
	} else {
		if (!($ref_hour = get_option('whoowns_cron_ref_hour')))
			$ref_hour = 24;
		$interval = ($ref_hour>$h)
			? ($ref_hour-1)-$h
			: ($h-1)-$ref_hour;
		$interval = $interval*3600 + $m*60 + $s;
	}
	wp_clear_scheduled_hook( 'whoowns-update' );
	wp_schedule_event(time()+$interval, $frequency, 'whoowns-update');
}
?>
