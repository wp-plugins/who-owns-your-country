<?php

function whoowns_batch_get_owners_ids() {
	$args = array(
		'post_type' => 'whoowns_owner', 
		'fields' => 'ids',
		'nopaging' => true
	);
	return get_posts($args);
}

/*Use with care: this function updates:
	. Metakey whoowns_controlled_by (who controls each enterprise);
	. Metakey whoowns_controls_final (list of enterprises ultimately controlled by the owner)
	. Accumulated Power (PA) of the whole database;
	. interchainers
	. final controllers
	. IPA/IPAR and ranking
*/
function whoowns_full_update() {
	
	whoowns_batch_update_metakey_controlled_by();
	whoowns_batch_update_metakey_controls_final();
	whoowns_batch_calculate_accumulated_power();
	whoowns_batch_update_interchainers();
	whoowns_batch_update_final_controllers();
	whoowns_batch_update_power_index_and_rank();
}

function whoowns_batch_update_power_index_and_rank() {
	whoowns_batch_update_rank();
	whoowns_batch_update_accumulated_power_index();
}

function whoowns_batch_update_metakey_controlled_by() {
	$postids = whoowns_batch_get_owners_ids();
	whoowns_update_metakey_controlled_by($postids);
}

function whoowns_batch_update_metakey_controls_final() {
	$postids = whoowns_batch_get_owners_ids();
	whoowns_update_metakey_controls_final($postids);
}

function whoowns_batch_calculate_accumulated_power() {
	$postids = whoowns_batch_get_owners_ids();
	whoowns_update_accumulated_power($postids);
}



//INTERCHAINERS:
function whoowns_batch_update_interchainers() {
	whoowns_update_interchainers(whoowns_batch_get_owners_ids());
}


//FINAL CONTROLLERS:
function whoowns_batch_update_final_controllers() {
	whoowns_update_final_controllers(whoowns_batch_get_owners_ids());
}




// Warning: this function depends on updating PA, is_interchainer and is_final_controller!
function whoowns_batch_update_rank() {
	global $wpdb;
	$sql = "SELECT post_id
			FROM ".$wpdb->prefix."postmeta
			WHERE meta_key = 'whoowns_PA'
				AND post_id IN (
					SELECT post_id FROM ".$wpdb->prefix."postmeta
					WHERE ( meta_key = 'whoowns_is_interchainer'
						OR meta_key = 'whoowns_is_final_controller')
						AND meta_value =1
				)
			ORDER BY CAST( meta_value AS SIGNED ) DESC";
	$postids = $wpdb->get_col($sql);
	
	$sql = "SELECT post_id FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'whoowns_rank' AND post_id NOT IN (".implode(",",$postids).")";
	$extra_postids = $wpdb->get_col($sql);
	if ($extra_postids)
		foreach ($extra_postids as $extra_postid)
			delete_post_meta($extra_postid, 'whoowns_rank');
	
	foreach ($postids as $r=>$postid) {
		//echo ($r+1).": $postid<br>";
		update_post_meta($postid,'whoowns_rank',($r+1));
	}
}


function whoowns_sum_total_revenue() {
	#pR(whoowns_calculate_revenue(whoowns_batch_get_owners_ids()));exit;
	return array_sum(whoowns_calculate_revenue(whoowns_batch_get_owners_ids()));
}


function whoowns_batch_update_accumulated_power_index() {
	global $wpdb;
	
	//Erase IPA or IPAR where there is no PA:
	$sql = "SELECT post_id, meta_key FROM ".$wpdb->prefix."postmeta where (meta_key='whoowns_IPA' OR meta_key='whoowns_IPAR') AND post_id NOT IN (SELECT post_id FROM ".$wpdb->prefix."postmeta where meta_key='whoowns_PA')";
	$posts = $wpdb->get_results($sql);
	if (count($posts))
		foreach ($posts as $post)
			delete_post_meta($post->ID,$post->meta_key);
	
	//Calculate total sum of PA (only from the actors which are not controlled, so that the sum doesn't repeat items):
	$sql = "SELECT sum(meta_value) FROM ".$wpdb->prefix."postmeta WHERE meta_key='whoowns_PA' AND post_id NOT IN (SELECT post_id FROM ".$wpdb->prefix."postmeta WHERE meta_key='whoowns_controlled_by')";
	$res = $wpdb->get_col($sql);
	$total_PA = $res[0];
	//Update meta data:
	$sql = "SELECT post_id, meta_value 'PA' FROM ".$wpdb->prefix."postmeta WHERE meta_key='whoowns_PA'";
	$posts = $wpdb->get_results($sql);
	foreach ($posts as $post)
		update_post_meta($post->post_id,'whoowns_IPA',$post->PA/$total_PA);

	
	//Calculate total sum of PA among ranked enterprises:
	$sql = "SELECT sum(meta_value) FROM ".$wpdb->prefix."postmeta WHERE meta_key='whoowns_PA' AND post_id IN (SELECT post_id FROM ".$wpdb->prefix."postmeta WHERE meta_key='whoowns_rank')";
	$res = $wpdb->get_col($sql);
	$total_PAR = $res[0];
	//Update meta data:
	$sql = "SELECT post_id, meta_value 'PA' FROM ".$wpdb->prefix."postmeta WHERE meta_key='whoowns_PA' AND post_id IN (SELECT post_id FROM ".$wpdb->prefix."postmeta WHERE meta_key='whoowns_rank')";
	$posts = $wpdb->get_results($sql);
	foreach ($posts as $post)
		update_post_meta($post->post_id,'whoowns_IPAR',$post->PA/$total_PAR);
}

