<?php


function whoowns_get_owner_data($id=null,$provide_links=false,$extra_data=false) {
	$posts_id = array(false);
	if (is_array($id))
		$posts_id = $id;
		elseif ($id)
			$posts_id = array($id);
			elseif (!$post)
				return;
	$owner_data = array();
	foreach ($posts_id as $i=>$post_id) {
		$post = get_post($post_id);
		$owner_image_size = explode('x',get_option('whoowns_owner_image_size'));
		
		$owner_data[$i] = new stdClass();
		$owner_data[$i]->ID = $post->ID;
		$owner_data[$i]->name = $post->post_title;
		if ($provide_links)
			$owner_data[$i]->link = get_post_permalink( $post->ID );
		$res = get_post_custom($post->ID);
		foreach ($res as $name=>$r) {
			$term = str_replace('whoowns_','',$name);
			switch ($term) {
				default:
					$trad = (!is_serialized($r[0]))
						? __($r[0],'whoowns')
						: maybe_unserialize($r[0]);
					$owner_data[$i]->$term = $trad;
				break;
			}
		}
		$owner_data[$i]->image = get_the_post_thumbnail( $post->ID, $owner_image_size);
		$owner_data[$i]->type = whoowns_get_owner_type($post->ID,$provide_links);
		
		if ($extra_data) {
			if ($owner_data[$i]->controlled_by) {
				$owner_data[$i]->controlled_by = whoowns_get_owner_data( $owner_data[$i]->controlled_by, true);
				$owner_data[$i]->controlled_by_final = ($owner_data[$i]->controlled_by_final == $owner_data[$i]->controlled_by->ID)
					? $owner_data[$i]->controlled_by
					: whoowns_get_owner_data( $owner_data[$i]->controlled_by_final, true);
			}
			if ($owner_data[$i]->controls_final) {
				$controls_final = $PA = array();
				$controls_final_tmp = whoowns_get_owner_data(array_keys($owner_data[$i]->controls_final),true);
				if (!is_array($controls_final_tmp))
					$controls_final_tmp = array( $controls_final_tmp );
				foreach ($controls_final_tmp as $cf) {
					$controls_final[$cf->ID] = $cf;
					$controls_final[$cf->ID]->is_direct_control = $owner_data[$i]->controls_final[$cf->ID]['is_direct'];
					$PAs[$cf->ID] = $cf->PA;
				}
				arsort($PAs);
				$owner_data[$i]->controls_final = array();
				foreach ($PAs as $cf_id=>$PA)
					$owner_data[$i]->controls_final[$cf_id] = $controls_final[$cf_id];
					
				$owner_data[$i]->controls_final_top = whoowns_show_controls_final_top($owner_data[$i]->controls_final, 3, '', true);
			}
			$owner_data[$i]->shareholders = whoowns_get_direct_shareholders( $post->ID, $provide_links,$extra_data );
			$owner_data[$i]->main_actors = whoowns_get_main_actors_in_network( $post->ID, $provide_links );
			#$owner_data[$i]->related_posts = whoowns_get_related_posts( $post->ID );
			if ($ref_id = get_option('whoowns_reference_owner'))
				$owner_data[$i]->participation_of_reference_owner = whoowns_get_final_participation_between_two_owners( $ref_id, $post->ID );
		}
	}

	return (count($owner_data)==1) ? $owner_data[0] : $owner_data;
}

function whoowns_get_owner_type($post_id,$provide_links=false) {
	if (!$post_id)
		return false;
	$type = get_the_terms($post_id,'whoowns_owner_types');
	if (!$type)
		return false;
	$type = array_shift(array_values($type));
	$type->name = __($type->name,'whoowns');
	if ($provide_links)
		$type->link = get_term_link($type);
	return $type;
}

function whoowns_get_direct_shareholders($post_id,$provide_links=false,$full_data=false) {
	if (!$post_id)
		return false;
	global $wpdb;
	$post_ids = (is_array($post_id))
		? $post_id
		: array($post_id);
	$res = array();
	foreach ($post_ids as $i=>$p) {
		$sql = "SELECT b.id as share_id, a.post_title as shareholder_name, b.from_id as shareholder_id, b.share, b.relative_share FROM ".$wpdb->posts." a, ".$wpdb->whoowns_shares." b WHERE a.ID=b.from_id AND to_id='$p' ORDER BY b.share DESC";
		$res[$i] = $wpdb->get_results($sql);
		if ($res[$i]) {
			if ($full_data || $provide_links) {
				foreach ($res[$i] as $j=>$r) {
					if ($full_data) {
						$data = whoowns_get_owner_data($r->shareholder_id,$provide_links);
						unset($data->ID,$data->name);
						foreach ($r as $f=>$v)
							$data->$f = $v;
						$res[$i][$j] = $data;
					} else {
						$res[$i][$j]->shareholder_link = get_permalink($r->shareholder_id);
					}
				}
			}
		}
	}
	if (count($res)==1)
		$res = $res[0];
	return ($res) ? $res : array();
}
function whoowns_get_direct_controller($post_id,$full_data=false) {
	if (!$post_id)
		return false;
	$shareholders = whoowns_get_direct_shareholders($post_id,$full_data,$full_data);
	foreach ($shareholders as $s)
		if ($s->relative_share>50)
			return $s;
	return false;
}
function whoowns_get_controllers($postid, $controllers=array()) {
	// Prevent loop and return false in this case:
	if (in_array($postid, array_slice($controllers,0,count($controllers)-1)))
		return false;

	if ($direct_controller = whoowns_get_direct_controller($postid)) {
		$controllers[] = $direct_controller->shareholder_id;
		return whoowns_get_controllers($direct_controller->shareholder_id, $controllers);
	}
	return $controllers;
}
function whoowns_generate_controllers_list($postid, $format="both") {
	if (!$postid)
		return false;
	if ($controllers = whoowns_get_controllers($postid)) {
		$html = in_array($format, array('html','both'));
		$array = in_array($format, array('array','both'));
		$list = new stdClass();
		if ($array) {
			$list->data = array();
		}
		if ($html) {
			$list->html = "";
		}
		foreach ($controllers as $level=>$controller_id) {
			$data = whoowns_get_owner_data($controller_id,true);
			if ($array) {
				$list->data[$controller_id] = $data;
			}
			if ($html) {
				$spaces = str_repeat("&nbsp;",$level*3);
				$list->html .= "<p>$spaces<span class='whoowns_list_owner_name'><span class='icon-angle-double-right'></span> <a href='#' onClick=\"whoowns_select_node(this,'".$data->ID."','".$data->link."')\">".$data->name."</a></span></p>";
				
			}
		}
		return $list;
	}
	return false;
}
function whoowns_generate_direct_shareholders_list($postid, $format="both") {
	if (!$postid)
		return false;
	if ($direct_shareholders = whoowns_get_direct_shareholders($postid,true)) {
		$html = in_array($format, array('html','both'));
		$array = in_array($format, array('array','both'));
		$list = new stdClass();
		if ($array) {
			$list->data = $direct_shareholders;
		}
		if ($html) {
			$list->html = "";
			foreach ($direct_shareholders as $ds) {
				$share_txt = whoowns_format_share_percentage($ds->share);
				$rel_share_txt = whoowns_format_share_percentage($ds->relative_share);
				$list->html .= "<p><span class='whoowns_list_owner_name'><span class='icon-angle-double-right'></span> <a href='#' onClick=\"whoowns_select_node(this,'".$ds->shareholder_id."','".$ds->shareholder_link."')\">".$ds->shareholder_name."</a></span> <span class='whoowns_list_shares'>($share_txt)</span></span></p>";
			}
		}
		return $list;
	} else {
		$type = whoowns_get_owner_type($postid);
		if ($type->slug=='private-enterprise') {
			$list = new stdClass();
			$list->html = "<p class='description'>".__("Information not available.", "whoowns")."</p>";
		}
		return $list;
	}
	return false;
}
function whoowns_generate_indirect_shareholders_list($postid, $format="both", $list="", $route=array()) {
	if (!$postid)
		return false;
	if ($direct_shareholders = whoowns_get_direct_shareholders($postid,true)) {
		$html = in_array($format, array('html','both'));
		$array = in_array($format, array('array','both'));
		if (!$list) {
			//echo "<b>$postid</b><br>";
			$list = new stdClass();
			$list->level = array($postid => array());
			$list->final_share = array($postid => array());
			if ($html)
				$list->html = "";
			$route = array($postid);
		}
		if ($array) {
			if (!$list->data)
				$list->data = array();	
		}
		if ($html) {
			$lim = (count($route)>1)
				? $list->level[$route[count($route)-2]][$postid]
				: 1;
			for ($i=0;$i<$lim;$i++)
				$spaces .= "|".str_repeat("&nbsp;",3);
			$spaces .= "+";
		}
		foreach ($direct_shareholders as $ds) {
			//Preventing loops:
			if (whoowns_verify_pair_redundance($route,$ds->shareholder_id))
				return $list;
			//$list->final_share[$ds->shareholder_id] = 100*($ds->relative_share/100 * $list->final_share[$postid]/100);
			$list->final_share[$postid][$ds->shareholder_id] = (count($route)>1)
				? 100*($ds->relative_share/100 * $list->final_share[$route[count($route)-2]][$postid]/100)
				: $ds->relative_share;
			/*if (!$list->level[$ds->shareholder_id])
				$list->level[$ds->shareholder_id] = $list->level[$postid]+1;*/
			$list->level[$postid][$ds->shareholder_id] = (count($route)>1)
				? $list->level[$route[count($route)-2]][$postid]+1
				: 1;
			if ($array) {
				$data = new stdClass();
				$data->target_id = $postid;
				$data->shareholder_id = $ds->shareholder_id;
				$data->shareholder_name = $ds->shareholder_name;
				$data->share = $ds->share;
				$data->relative_share = $ds->relative_share;
				$data->final_share = $list->final_share[$postid][$ds->shareholder_id];
				$data->level = $list->level[$postid][$ds->shareholder_id];
				$list->data[$postid][$ds->shareholder_id] = $data;
			}
			if ($html) {
				$share_txt = whoowns_format_share_percentage($ds->share);
				$rel_share_txt = whoowns_format_share_percentage($ds->relative_share);
				$final_share_txt = whoowns_format_share_percentage($list->final_share[$postid][$ds->shareholder_id]);
				$list->html .= "<p>$spaces <span class='whoowns_list_owner_name'><a href='#' onClick=\"whoowns_select_node(this,'".$ds->shareholder_id."','".$ds->shareholder_link."')\">".$ds->shareholder_name."</a></span> (<span class='whoowns_list_shares'><span class='whoowns_list_final_share'>$final_share_txt</span>; $rel_share_txt; $share_txt)</span></p>";
			}
			$list = whoowns_generate_indirect_shareholders_list($ds->shareholder_id,$format,$list, array_merge($route,array($ds->shareholder_id)));
			//pR($direct_shareholders); exit;
		}
	}
	return $list;
}
function whoowns_get_final_participation_between_two_owners( $ref_id, $post_id ) {
	if ($participations =  whoowns_generate_indirect_shareholders_list($post_id, 'array')) {
		$shares = array();
		foreach ($participations->data as $target_id => $shareholders) {
			foreach ($shareholders as $shareholder_id => $data) {
				if ($shareholder_id == $ref_id) {
					$shares[$target_id] = new stdClass();
					$is_direct = ($target_id == $post_id);
					if (!$is_direct)
						$shares[$target_id]->target = whoowns_get_owner_data($target_id,true);
					$shares[$target_id]->final_share = $data->final_share;
					$shares[$target_id]->is_direct = $is_direct;
				}
			}
		}
		if ($shares) {
			$ret = new stdClass();
			$ret->ref = whoowns_get_owner_data($ref_id,true);
			$ret->shares = $shares;
			return $ret;
		}
	}
	return false;
}
function whoowns_update_metakey_controlled_by($postids) {
	if (!$postids)
		return false;
	if (!is_array($postids))
		$postids = array($postids);
	foreach ($postids as $postid) {
		$controllers = whoowns_get_controllers($postid);
		if ($controllers[0]) {
			update_post_meta($postid, 'whoowns_controlled_by', $controllers[0]);
			update_post_meta($postid, 'whoowns_controlled_by_final', end($controllers));
		} else {
			delete_post_meta($postid, 'whoowns_controlled_by');
			delete_post_meta($postid, 'whoowns_controlled_by_final');
		}
	}
}
add_action('whoowns-update-metakey-controlled-by', 'whoowns_update_metakey_controlled_by');



function whoowns_get_direct_participations($post_id,$provide_links=false,$min_share=false) {
	global $wpdb;
	$post_ids = (is_array($post_id))
		? $post_id
		: array($post_id);
	if (is_numeric($min_share))
		$min_share = "AND b.relative_share>$min_share";
	$res = array();
	foreach ($post_ids as $i=>$p) {
		$sql = "SELECT b.id as share_id, a.ID, b.share, b.relative_share FROM ".$wpdb->posts." a, ".$wpdb->whoowns_shares." b WHERE a.ID=b.to_id AND from_id='$p' $min_share ORDER BY a.post_title";
		$res[$i] = $wpdb->get_results($sql);
		if ($res[$i]) {
			foreach ($res[$i] as $j=>$r) {
				$data = whoowns_get_owner_data($r->ID,$provide_links);
				unset($data->ID);
				foreach ($r as $f=>$v)
					$data->$f = $v;
				$res[$i][$j] = $data;
			}
		}
	}
	if (count($res)==1)
		$res = $res[0];
	return ($res) ? $res : array();
}
function whoowns_has_participations($post_id,$min_share=false) {
	global $wpdb;
	$post_ids = (is_array($post_id))
		? $post_id
		: array($post_id);
	if (is_numeric($min_share))
		$min_share = "AND relative_share>$min_share";
	$res = array();
	foreach ($post_ids as $i=>$p) {
		$sql = "SELECT id as share_id FROM ".$wpdb->whoowns_shares." WHERE from_id='$p' $min_share";
		$res[$i] = count($wpdb->get_results($sql));
	}
	if (count($res)==1)
		$res = $res[0];
	return ($res) ? $res : array();
}
function whoowns_get_directly_controlled($post_id) {
	$participations = whoowns_get_direct_participations($post_id,true);
	$res = array();
	foreach ($participations as $s)
		if ($s->relative_share>50)
			$res[$s->ID] = $s;
	return $res;
}
function whoowns_get_controlled($postid, $route=array()) {
	if ($directly_controlled = array_keys(whoowns_get_directly_controlled($postid))) {
		$controlled = $directly_controlled;
		foreach ($directly_controlled as $dc_postid) {
			//check inconsistencies to prevent LOOP:
			if (in_array($dc_postid,$route))
				return array();
			$controlled = array_merge($controlled, whoowns_get_controlled($dc_postid, array_merge($route,$controlled)));
		}
	}
	return ($controlled) ? $controlled : array();
}
function whoowns_update_metakey_controls_final($postids) {
	if (!$postids)
		return false;
	if (!is_array($postids))
		$postids = array($postids);
	foreach ($postids as $postid) {
		$directly_controlled = array_keys(whoowns_get_directly_controlled($postid));
		$controlled = whoowns_get_controlled($postid);
		$controls_final = array();
		foreach ($controlled as $c_postid) {
			$is_directly_controlled = in_array($c_postid, $directly_controlled);
			if (!whoowns_get_directly_controlled($c_postid)) {
				$controls_final[$c_postid] = array( 
					'post_id'=>$c_postid, 
					'is_direct'=>$is_directly_controlled );
			}
		}
		#pR($controls_final);
		if ($controls_final)
			update_post_meta($postid, 'whoowns_controls_final', $controls_final);
		else
			delete_post_meta($postid, 'whoowns_controls_final');
	}
}
add_action('whoowns-update-metakey-controls-final', 'whoowns_update_metakey_controls_final');
function whoowns_generate_controls_list($postid, $format="both") {
	if (!$postid)
		return false;
	if ($controlled = whoowns_get_controlled($postid)) {
		$directly_controlled = array_keys(whoowns_get_directly_controlled($postid));
		foreach ($controlled as $c_postid) {
			$is_directly_controlled = (in_array($c_postid, $directly_controlled))
				? 'direct'
				: 'indirect';
			$controls[$is_directly_controlled][] = whoowns_get_owner_data($c_postid, true);
		}
		$html = in_array($format, array('html','both'));
		$array = in_array($format, array('array','both'));
		$list = new stdClass();
		if ($array) {
			$list->data = new stdClass();
			$list->data->direct = $controls['direct'];
			$list->data->indirect = $controls['indirect'];
		}
		if ($html) {
			$list->html = "";
			foreach ($controls as $is_direct=>$controls_sub) {
				$is_direct_txt = ($is_direct == "direct")
					? __("Directly controlled:", "whoowns")
					: __("Indirectly controlled:", "whoowns");
				$list->html .= "<h4>$is_direct_txt</h4>";
				foreach ($controls_sub as $c) {
					$list->html .= "<p><span class='whoowns_list_owner_name'><span class='icon-angle-double-right'> <a href='#' onClick=\"whoowns_select_node(this,'".$c->ID."','".$c->link."')\">".$c->name."</a></span></span></p>";
				}
			}
		}
	} else {
		$list = new stdClass();
		$list->html = "<p class='description'>".__("None.", "whoowns")."</p>";
	}
	return $list;
}
function whoowns_show_controls_final_top($controls_final, $num, $see_all_url='', $provide_link = false) {
	if (!$controls_final)
		return '';
	
	$controls_final_top = array();
	$k=0;
	foreach($controls_final as $cf_id=>$cf) {
		$controls_final_top[$k] = ($provide_link)
			? '<a href="'.get_permalink($cf_id).'">'.get_the_title($cf_id).'</a>'
			: get_the_title($cf_id);
		$k++; if ($num>0 && $k==$num) break;
	}
	$html = implode(', ',$controls_final_top);
	if ($num && count($controls_final)>$num) {
		$html .= '...';
		if ($see_all_url)
			$html .= " (<a href='$see_all_url'>".__('see all','whoowns')."</a>)";
	}
	return $html;
}
function whoowns_generate_participations_list($postid, $format="both", $list="", $route=array()) {
	if (!$postid)
		return false;
	if ($direct_participations = whoowns_get_direct_participations($postid,true)) {
		$html = in_array($format, array('html','both'));
		$array = in_array($format, array('array','both'));
		if (!$list) {
			$list = new stdClass();
			$list->level = array($postid => array());
			$list->final_share = array($postid => array());
			if ($html)
				$list->html = "";
			$route = array($postid);
		}
		if ($array) {
			if (!$list->data)
				$list->data = array();	
		}
		if ($html) {
			$lim = (count($route)>1)
				? $list->level[$route[count($route)-2]][$postid]
				: 1;
			for ($i=0;$i<$lim;$i++)
				$spaces .= "|".str_repeat("&nbsp;",3);
			$spaces .= "+";
		}
		foreach ($direct_participations as $dp) {
			//Let's prevent loops:
			if (whoowns_verify_pair_redundance($route,$dp->ID))
				return $list;
				
			//$list->final_share[$dp->ID] = 100*($dp->relative_share/100 * $list->final_share[$postid]/100);
			$list->final_share[$postid][$dp->ID] = (count($route)>1)
				? 100*($dp->relative_share/100 * $list->final_share[$route[count($route)-2]][$postid]/100)
				: $dp->relative_share;
			/*if (!$list->level[$dp->ID])
				$list->level[$dp->ID] = $list->level[$postid]+1;*/
			$list->level[$postid][$dp->ID] = (count($route)>1)
				? $list->level[$route[count($route)-2]][$postid]+1
				: 1;
			if ($array) {
				$data = new stdClass();
				$data->shareholder_id = $postid;
				$data->target_id = $dp->ID;
				$data->target_name = $dp->name;
				$data->share = $dp->share;
				$data->relative_share = $dp->relative_share;
				$data->final_share = $list->final_share[$postid][$dp->ID];
				$data->level = $list->level[$postid][$dp->ID];
				$list->data[$postid][$dp->ID] = $data;
			}
			if ($html) {
				$share_txt = whoowns_format_share_percentage($dp->share);
				$rel_share_txt = whoowns_format_share_percentage($dp->relative_share);
				$final_share_txt = whoowns_format_share_percentage($list->final_share[$postid][$dp->ID]);
				$list->html .= "<p>$spaces <span class='whoowns_list_owner_name'><a href='#' onClick=\"whoowns_select_node(this,'".$dp->ID."','".$dp->link."')\">".$dp->name."</a></span> (<span class='whoowns_list_shares'><span class='whoowns_list_final_share'>$final_share_txt</span>; $share_txt; $rel_share_txt)</span></p>";
			}
			$list = whoowns_generate_participations_list($dp->ID,$format,$list,array_merge($route,array($dp->ID)));
		}
	}
	return $list;
}
function whoowns_get_participations_of_controlled_enterprises($postid,$controlled_postid='',$controlled_postids=array()) {
	if (!$postid)
		return false;
	$controlled_enterprises = whoowns_get_controlled($postid);
	if (in_array($controlled_postid,$controlled_enterprises)) {
		if ($direct_participations = whoowns_get_direct_participations($controlled_postid,false,0)) {
			foreach ($direct_participations as $dp) {
				$controlled_postids += whoowns_get_participations_of_controlled_enterprises($controlled_postid,$dp->ID,$controlled_postids);
			}
		} else {
			$controlled_postids[] = $controlled_postid;
		}
	} else {
		if ($direct_participations = whoowns_get_direct_participations($postid,false,0)) {
			foreach ($direct_participations as $dp) {
				if (!$controlled_postid) {
					$controlled_postids += whoowns_get_participations_of_controlled_enterprises($postid,$dp->ID,$controlled_postids);
				} elseif ($dp->relative_share>0 && $dp->relative_share<=50) {
					$controlled_postids[] = $dp->ID;
				}
			}
		} else {
			$controlled_postids[] = $postid;
		}
	}
	return array_unique($controlled_postids);
}


function whoowns_update_shareholders($post_id, $submitted_shareholders = array()) {
	global $wpdb;
	$actual_shareholders = whoowns_get_direct_shareholders($post_id);
	$actual_direct_controller = whoowns_get_direct_controller($post_id);
	$new_shareholders = $submitted_shareholders;
	$changed = false;
	foreach ($actual_shareholders as $actual_shareholder) {
		$it_exists=false;
		foreach ($submitted_shareholders as $i=>$submitted_shareholder) {
			if ( $actual_shareholder->shareholder_id == $submitted_shareholder->shareholder_id ) {
				$affected_rows = $wpdb->update(
					$wpdb->whoowns_shares, 
					array( 
						'from_id' => $actual_shareholder->shareholder_id,
						'to_id' => $post_id,
						'share' => whoowns_set_decimal_symbol($submitted_shareholder->share,'.')
					),
					array( 'id' => $actual_shareholder->share_id ),
					array(
						'%d',
						'%d',
						'%f'
					)
				);
				if (!$changed && $affected_rows>0)
					$changed = true;
				$it_exists = true;
				unset($new_shareholders[$i]);
				break;
			}
		}
		if (!$it_exists) {
			$wpdb->delete(
				$wpdb->whoowns_shares, 
				array( 'id' => $actual_shareholder->share_id )
			);
			$changed = true;
		}
	}
	if (count($new_shareholders)) {
		foreach ($new_shareholders as $new_shareholder) {
			if ($new_shareholder->shareholder_id) {
				$wpdb->insert(
					$wpdb->whoowns_shares, 
					array( 
						'from_id' => $new_shareholder->shareholder_id,
						'to_id' => $post_id,
						'share' => whoowns_set_decimal_symbol($new_shareholder->share,'.')
					),
					array(
						'%d',
						'%d',
						'%f'
					)
				);
				$changed = true;
			}
		}
	}
		
	//After inserting the shareholders, I must calculate and update their relative participation IF there were changes in the values:
	if ($changed) {
		//Calculating and updating relative shares:
		$recalculate_controls = array();
		$relative_shares = whoowns_calculate_relative_shares($post_id);
		$shareholders = whoowns_get_direct_shareholders($post_id);
		foreach ($shareholders as $shareholder) {
			$wpdb->update(
				$wpdb->whoowns_shares, 
				array( 
					'relative_share' => $relative_shares[$shareholder->shareholder_id]
					),
				array('id' => $shareholder->share_id),
				array(
					'%f'
				)
			);
			
			if ($shareholder->shareholder_id == $direct_controller_before->shareholder_id && $relative_shares[$shareholder->shareholder_id]<=50) {
			// If a shareholder who was controller is not controller anymore:
				$recalculate_controls[] = $shareholder->shareholder_id;
			} elseif ($shareholder->shareholder_id != $direct_controller_before->shareholder_id && $relative_shares[$shareholder->shareholder_id]>50) {
			// Or if a shareholder who was not controller is a controller now:
				$recalculate_controls[] = $shareholder->shareholder_id;
				if ($direct_controller_before)
					$recalculate_controls[] = $direct_controller_before->shareholder_id;
			}
		}
		// If there was some change in what shareholder controls this owner, we must update the metakey 'controls_final' of all the direct and indirect shareholders:
		if ($recalculate_controls) {
			$recalculate_controls = array_unique($recalculate_controls);
			foreach ($recalculate_controls as $rc_postid) {
				whoowns_update_metakey_controls_final(whoowns_get_controllers($rc_postid));
			}
		}
	}
	return $changed;
}




//This function prepares the system to update the whole universe related to an owner. It's called when something in the shareholders or revenue of owner $postid was changed.
function whoowns_init_owner_universe_update($postid, $was_deleted=false) {
	global $wpdb;
	$net = whoowns_generate_full_network($postid);
	
	if ($was_deleted)
		$net = array_diff($net, array($postid));
	
	//Erasing the cache of all nodes of the network:
	$wpdb->query( $wpdb->prepare(  "DELETE FROM ".$wpdb->whoowns_networks_cache." WHERE post_id IN (%d)", implode(', ',$net) ) );
	
	whoowns_init_update($net);
}
// This is the function which configures the whoowns-update schedule for the owners in the $net array:
function whoowns_init_update($net) {
	// Scheduling events (every 2 seconds) to re-cache the power network of each node:
	// Note: I've put the metakey updates in the midnight cron (look at 'whoowns_update' function)...
	/*foreach ($net as $i=>$n_postid) {
		wp_schedule_single_event( time()+($i*2), 'whoowns-update-metakey-controlled-by', array($n_postid) );
		wp_schedule_single_event( time()+(($i+1)*2), 'whoowns-update-metakey-controls-final', array($n_postid) );
	}*/
	
	$whoowns_cron = get_option('whoowns_cron');
	if ($whoowns_cron['postids']) {
		$net = array_unique(array_merge($net,$whoowns_cron['postids']));
	}
	$whoowns_cron['stage'] = 1;
	$whoowns_cron['status'] = 'waiting';
	$whoowns_cron['postids'] = $net;
	update_option('whoowns_cron', $whoowns_cron);
}
function whoowns_update() {
	$whoowns_cron = get_option('whoowns_cron');
	if (!$whoowns_cron || !$whoowns_cron['postids'])
		return false;
	
	if ($whoowns_cron['status']=='working') {
		wp_schedule_single_event( time()+300, 'whoowns-update' );
		return false;
	} elseif ($whoowns_cron['status']=='concluded') {
		$whoowns_cron['stage']++;
		if ($whoowns_cron['stage']>5) {
			update_option('whoowns_cron','');
			return true;
		}
	}
		
	$postids = $whoowns_cron['postids'];
	$whoowns_cron['status'] = 'working';
	update_option('whoowns_cron', $whoowns_cron);
	switch ($whoowns_cron['stage']) {
		case 1:
			whoowns_update_metakey_controlled_by($postids);
		break;
		case 2:
			whoowns_update_metakey_controls_final($postids);
		break;
		case 3:
			whoowns_update_accumulated_power($postids,true);
		break;
		case 4:
			whoowns_update_interchainers($postids);
		break;
		case 5:
			whoowns_update_final_controllers($postids);
		break;
		case 6:
			whoowns_batch_update_power_index_and_rank();
		break;
		case 7:
			$conclude = false;
			foreach ($net as $i=>$n_postid) {
				if ($i == (count($net)-1))
					$conclude = true;
				wp_schedule_single_event( time()+($i*30), 'whoowns-update-network-cache', array($n_postid, $conclude) );
			}
		break;
	}
	if ($whoowns_cron['stage']!=7) {
		$whoowns_cron['status'] = 'concluded';
		update_option('whoowns_cron', $whoowns_cron);
		wp_schedule_single_event( time(), 'whoowns-update' );
	}
	return false;
}
add_action( 'whoowns-update','whoowns_update' );


function whoowns_update_network_cache($postid, $conclude) {
	//repopulate 'nodes' and 'edges':
	whoowns_prepare_network_data_for_visualization($postid);
	//repopulate chain of participations and controllers (cy_list):
	ob_start();
	whoowns_show_list_view($postid);
	$html = ob_get_contents();
	ob_end_clean();
	whoowns_save_cached($postid,array('cy_list'=>trim($html)));
	/*//repopulate news:
	whoowns_update_network_related_news($postid);*/
	
	// If this was the last postid of the targets, it's time to go to the next stage of action whoowns-update
	if ($conclude) {
		$whoowns_cron = get_option('whoowns_cron');
		$whoowns_cron['status'] = 'concluded';
		$whoowns_cron['stage']++;
		update_option('whoowns_cron', $whoowns_cron);
		wp_schedule_single_event( time(), 'whoowns-update' );
	}
}
add_action( 'whoowns-update-network-cache','whoowns_update_network_cache' );



//This function gets the whole universe of points related to this reference. This is useful for calculating the accumulated power, interchainer and final controllers, not for the graphic. Use with care...
function whoowns_generate_full_network($postid) {
	$full_net = whoowns_generate_directed_network($postid,array(),'all');
	return array_unique($full_net);
}







//ACCUMULATED POWER:
function whoowns_calculate_accumulated_power($route, $postid) {
	//First, check if it's not already scheduled:
	//use wp_next_scheduled to deleted some next event which will do the same calculation... One other way is to use get_option('cron') and see if there is some scheduled event which falls in the universe of this event, so that it can be prevented with unschedule.
	
	$direct_participations = whoowns_get_direct_participations($postid);
	if (!is_array($direct_participations))
		$direct_participations = array($direct_participations);
	$value = whoowns_calculate_revenue($postid);

	$power = $value;
	#pR($route);
	#pR($power);
	foreach($direct_participations as $p) {
		$route_tmp = $route;
		$route_tmp[]=$p->ID;
		if ($p->controlled_by)
			$accounted_share = ($p->controlled_by == $postid)
				? 1
				: 0;
		else
			$accounted_share = floatval($p->relative_share)/100;
		if ( $accounted_share>0 && !whoowns_verify_pair_redundance($route,$p->ID) ) {
			#echo implode(',',$route).": $power | wB=".number_format($p->relative_share/100,2)."|w=".number_format($accounted_share,2)."<br>";
			$power += $accounted_share * whoowns_calculate_accumulated_power($route_tmp,$p->ID);
			#echo implode(',',$route).": $power | wB=".number_format($p->relative_share/100,2)."|w=".number_format($accounted_share,2)."<br>";
		}
	}
	return $power;
}
function whoowns_update_accumulated_power($postids) {
	if (!$postids)
		return false;
	global $wpdb;
	
	if (!is_array($postids))
		$postids = array($postids);
	
	foreach ($postids as $postid) {
		$PA = whoowns_calculate_accumulated_power(array($postid), $postid);
		if ($PA>0)
			update_post_meta($postid, 'whoowns_PA', $PA);
		else
			delete_post_meta($postid, 'whoowns_PA');
	}
	#pR("PA($postid) = $PA");
}





// INTERCHAINERS:
function whoowns_check_if_interchainer($postids, $save_metadata=false) {
	if (!$postids)
		return false;
	if (!is_array($postids))
		$postids = array($postids);
	$is_interchainer = array();
	foreach ($postids as $postid) {
		//INTERCHAIN PARTICIPATIONS:
		//Definition of non-controlled enterprises with interchain participations: The criteria is that the enterprise is nos controlled by any other enterprise and is not an ultimate controller, but participates directly in more than one different chain (be it by it or by a directly controlled enterprise)
		$type = whoowns_get_owner_type($postid);
		if ($type && $type->slug!='private-enterprise') {
			$is_interchainer[$postid] = false;
			continue;
		}
		if (whoowns_get_direct_controller($postid)) {
			$is_interchainer[$postid] = false;
			continue;
		}
		$participations_of_controlled_enterprises = whoowns_get_participations_of_controlled_enterprises($postid);
		$distincts = $participations_array = array();
		foreach ($participations_of_controlled_enterprises as $pce_postid) {
			$part = whoowns_generate_directed_network($pce_postid,array(),'participation',0.001);
			foreach ($part as $p_postid) {
				if (! whoowns_get_direct_participations($p_postid,false,0)) {
					if (!$participations_array[$pce_postid])
						$participations_array[$pce_postid] = array();
					$participations_array[$pce_postid][] = $p_postid;
					if (!in_array($p_postid,$distincts))
						$distincts[] = $p_postid;
				}
			}
		}
		if ($is_interchainer[$postid] = (count($distincts)>1)) {
			foreach ($participations_array as $p) {
				if (count($p)==count($distincts)) {
					$is_interchainer[$postid]=false;
					break;
				}
			}
		}
	}
	
	// Save the meta post information:
	if ($save_metadata) {
		foreach ($postids as $postid) {
			if ($is_interchainer[$postid])
				update_post_meta($postid,'whoowns_is_interchainer',1);
			else
				delete_post_meta($postid,'whoowns_is_interchainer');
		}
		return true;
	} else
		return (is_array($is_interchainer)) ? $is_interchainer : $is_interchainer[$postids[0]];
}
function whoowns_update_interchainers($postids) {
	whoowns_check_if_interchainer($postids,true);
}
	





//FINAL CONTROLLERS:
/*This function can only be processed AFTER the accumulated power and the interchain definitions have been processed in the whole universe being considered*/
function whoowns_update_final_controllers($postids) {
	if (!$postids || !is_array($postids))
		return false;
	global $wpdb;
	$final_controllers = array();
	
	// Definition of an enterprise as "final controller" - FIRST CRITERIA: 
	// It is not controlled by another enterprise and has no participations, 
	// or, if it has participations, it controls at least one other enterprise.
	foreach ($postids as $postid) {
		$type = whoowns_get_owner_type($postid);
		$is_final_controller = false;
		$direct_controller = whoowns_get_direct_controller($postid);
		$direct_controller_type = whoowns_get_owner_type($direct_controller->shareholder_id);
		if ( (!$type || $type->slug == 'private-enterprise') && (!$direct_controller_type || $direct_controller_type->slug!='private-enterprise') ) {
			$is_final_controller = (
				(whoowns_get_directly_controlled($postid)) || 
				!whoowns_has_participations($postid, 0.0001)
			);
			#pR($is_final_controller);exit;
		}
		if ($is_final_controller) {
			update_post_meta($postid,'whoowns_is_final_controller',1);
			$final_controllers[$postid] = $postid;
		} else
			delete_post_meta($postid,'whoowns_is_final_controller');
	}
	
	$final_controllers_loop = $final_controllers;
	
	// Definition of the enterprise as "final controller" - SECOND CRITERIA: 
	// There can only be one single final controller in a chain. If there is 
	// another final controller with a higher PA, this enterprise is not a 
	// final controller anymore. If one enterprise is an interchainer, then 
	// we should compare the revenue of it and of other final_controllers 
	// in the chain to decide who will be the final controller:
	$removed = $removed2 = array();
	foreach ($final_controllers_loop as $fc_postid) {
		$fc_PA = get_post_meta($fc_postid,'whoowns_PA',true);
		$fc_is_interchainer = get_post_meta($fc_postid,'whoowns_is_interchainer',true);
		$part = whoowns_generate_directed_network($fc_postid,array(),'participation',0.0001);
		unset($part[0]);
		$eliminate = array();
		$t=true;
		foreach ($part as $p_postid) {
			if (in_array($p_postid,$final_controllers_loop)) {
				$p_PA = get_post_meta($p_postid,'whoowns_PA',true);
				$p_is_interchainer = get_post_meta($p_postid,'whoowns_is_interchainer',true);
				$p_revenue = whoowns_calculate_own_and_controlled_revenues($p_postid);
				$fc_revenue = whoowns_calculate_own_and_controlled_revenues($fc_postid);
				//echo "<hr>p_id=$p_postid, p_PA=$p_PA, p_revenue=$p_revenue, p_is_interchainer=$p_is_interchainer<br>fc_id=$fc_postid, fc_PA=$fc_PA, fc_revenue=$fc_revenue, fc_is_interchainer=$fc_is_interchainer<br> < ".$final_controllers[$fc_postid];
				if ( $p_PA>$fc_PA || (
						$p_revenue > $fc_revenue && 
						$fc_is_interchainer && 
						!$p_is_interchainer
					) ) {
					unset($final_controllers[$fc_postid]);
					$removed[] = $fc_postid;
					break;
				} else {
					$eliminate[] = $p_postid;
				}
			}
		}
		if ($final_controllers[$fc_postid]) {
			foreach ($eliminate as $e_postid) {
				$removed2[] = $e_postid;
				unset($final_controllers[$e_postid]);
			}
		}
	}
	
	$sql = "SELECT post_id FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'whoowns_is_final_controller' AND post_id IN (".implode(",",$postids).") AND post_id NOT IN (".implode(",",$final_controllers).")";
	$extra_postids = $wpdb->get_col($sql);
	if ($extra_postids)
		foreach ($extra_postids as $extra_postid)
			delete_post_meta($extra_postid, 'whoowns_is_final_controller');

	foreach ($final_controllers as $fc_postid)
		update_post_meta($fc_postid,'whoowns_is_final_controller',1);
}








function whoowns_calculate_revenue($postids) {
	if (!$postids)
		return false;
	if (!is_array($postids))
		$postids = array($postids);
	$value = array();
	foreach ($postids as $i=>$postid) {
		$revenue = get_post_meta($postid, 'whoowns_revenue', true);
		$months = (intval($revenue['months']))
			? intval($revenue['months'])
			: 12;
		$v = floatval($revenue['value']);
		if ($v && $months<=12)
			$value[$postid] = ($v/$months)*12;
	}
	return (count($postids)>1) ? $value : $value[$postids[0]];
}
function whoowns_calculate_own_and_controlled_revenues($postids) {
	if (!$postids)
		return false;
	if (!is_array($postids))
		$postids = array($postids);
	$value = array();
	foreach ($postids as $i=>$postid) {
		$value[$postid] = whoowns_calculate_revenue($postid);
		//$controlled = array_keys(whoowns_get_directly_controlled($postid));
		if ($controlled_values = whoowns_calculate_revenue(whoowns_get_controlled($postid))) {
			$value[$postid] += (is_array($controlled_values))
				? array_sum($controlled_values)
				: $controlled_values;
		}
	}
	return (count($value)>1) ? $value : $value[$postids[0]];
}




function whoowns_calculate_relative_shares($post_id) {
	$shareholders = whoowns_get_direct_shareholders($post_id);
	$participation_sum=$rel_participation_sum=0;
	foreach ($shareholders as $s) {
		$participation_sum += $s->share;
		$rel_participation_sum += pow($s->share,2);
	}
	$others_num = ceil((100-$participation_sum)/get_option('whoowns_relative_share_for_dummy_shareholders'));
	if ($others_num>0) {
		$others_share = (100-$participation_sum)/$others_num;
		for ($i=1;$i<=$others_num;$i++) {
			$rel_participation_sum += pow($others_share,2);
		}
	}
	foreach ($shareholders as $s)
		$relative_shares[$s->shareholder_id] = 100*(pow($s->share,2)/$rel_participation_sum);
		
	return $relative_shares;
}



function whoowns_set_decimal_symbol($value, $target_symbol='') {
	global $wp_locale;
	if (!$target_symbol)
		$target_symbol=$wp_locale->number_format['decimal_point'];
	$res = ($target_symbol=='.')
		? str_replace(',','.',$value)
		: str_replace('.',',',$value);
	return $res;
}
function whoowns_format_share_percentage($n) {
	global $wp_locale;
	if ($n==0)
		return $n;
	if ($n==100)
		return $n."%";
	return number_format($n,2,$wp_locale->number_format['decimal_point'],$wp_locale->number_format['thousand_point'])."%";
};



function whoowns_get_main_actors_in_network($postid, $provide_links=false) {
	$res = array();
	$nets = whoowns_generate_network($postid,'without_reference',true);
	//pR($nets);exit;
	foreach($nets as $dir=>$net) {
		foreach ($net as $net_postid) {
			if ($dir=='participation'){
				$controller = whoowns_get_direct_controller($net_postid);
				$participations = whoowns_get_direct_participations($net_postid);
				$check_borders = (!$participations && $controller);
			} else {
				$controlled = whoowns_get_directly_controlled($net_postid);
				$mytype = whoowns_get_owner_type($net_postid);
				$check_borders = ($controlled && $mytype->slug=='person');
			}

			if (get_post_meta($net_postid,'whoowns_is_final_controller') ||
				get_post_meta($net_postid,'whoowns_is_interchainer') || 
				$check_borders
			) {
				$res[$dir][] = $net_postid;
			}
		}
		if ($res[$dir])
			$data[$dir] = whoowns_get_owner_data($res[$dir],$provide_links);
	}
	return $data;
}



//$dir is the direction of the network from $postid: upstream is 'participation', downstream is 'composition' and both directions is 'all'
function whoowns_generate_directed_network($postid,$net=array(),$dir,$minimum_share=0) {
	global $wpdb;
	//echo $postid."-";
	if (!in_array($postid,$net)) {
		$net[] = $postid;
	} else
		return $net;

	//echo $postid;exit;
	$minimum_share_sql = ($minimum_share)
		? "AND relative_share>$minimum_share"
		: "";
	
	//Downstream: Chain of composition (shareholding composition)
	if (in_array($dir,array('composition','all'))) {
		$sql = "SELECT from_id FROM ".$wpdb->whoowns_shares." WHERE to_id='$postid' $minimum_share_sql";
		$res = $wpdb->get_results($sql);
		if ($res) {
			foreach ($res as $r) {
				$net += whoowns_generate_directed_network($r->from_id,$net,$dir,$minimum_share);
			}
		}
	}
	
	//Upstream: Chain of participations (shares ownership)
	if (in_array($dir,array('participation','all'))) {
		$sql = "SELECT to_id, relative_share FROM ".$wpdb->whoowns_shares." WHERE from_id='$postid' $minimum_share_sql";
		$res = $wpdb->get_results($sql);
		//echo "$sql<br>";
		if ($res) {
			foreach ($res as $r) {
				//echo $r->to_id.'-';
				$net += whoowns_generate_directed_network($r->to_id,$net,$dir,$minimum_share);
			}
		}
	}
	return $net;
}



function whoowns_generate_network($postid,$mode='unique',$show_dir=false) {
	$cached=true;
	if ($show_dir || !($net = whoowns_retrieve_cached($postid,'post_ids',true))) {
		$net['participation'] = whoowns_generate_directed_network($postid,array(),'participation');
		$net['composition'] = whoowns_generate_directed_network($postid,array(),'composition');
		$cached=false;
	}
	if ($mode=='unique') {
		if ($show_dir) {
			$net['participation'] = array_unique($net['participation']);
			$net['composition'] = array_unique($net['composition']);
		} elseif (!$cached) {
			$net = array_unique(array_merge($net['participation'],$net['composition']));
			whoowns_save_cached($postid,array('post_ids'=>$net));
		}
	}
	if ($mode=='without_reference')
		unset($net[0], $net['participation'][0], $net['composition'][0]);
	//pR($net);
	return $net;
}



function whoowns_get_network_relations($post_ids) {
	global $wpdb;
	if (!is_array($post_ids))
		return false;
	$post_ids = implode(',',$post_ids);
	$sql = "SELECT a.id as share_id, a.from_id as source_id, b.post_title as 'source_name', a.to_id as target_id, c.post_title as 'target_name', a.share, a.relative_share FROM ".$wpdb->whoowns_shares." a, ".$wpdb->posts." b, ".$wpdb->posts." c WHERE a.from_id=b.ID AND a.to_id=c.ID AND a.to_id IN ($post_ids) AND a.from_id IN ($post_ids)";
	$res = $wpdb->get_results($sql);
	return $res;	
}



function whoowns_prepare_network_data_for_visualization($postid='',$net='') {
	//$whoowns_visual_network_colors = get_options(whoowns_visual_network_colors);
	$whoowns_visual_network_colors = array(
		'private-enterprise'=>'#900',
		'person'=>'#090',
		'state'=>'#009',
		'Focus'=>'#000',
	);
	if (!$net && !$postid)
		return false;
	$no_net = (!$net);
	$cached=false;
	$network_data = new stdClass();
	if ($postid && $no_net) {
		if (!($network_data->nodes = whoowns_retrieve_cached($postid,'nodes',true))) {
			$post_ids = whoowns_generate_network($postid);
			$net = whoowns_get_owner_data($post_ids,true,true);
		} else
			$cached=true;
	} else
		$net = whoowns_get_owner_data($net,true,true);
	if (!$cached) {
		//Generate the nodes:
		$network_data->nodes = array();
		foreach ($net as $n) {
			unset($node);
			if ($n->ID==$postid) {
				$name = $n->name;
				$type = $n->type->slug;
			}
			$node->data->id=strval($n->ID);
			$node->data->name=$n->name;
			if ($n->is_final_controller)
				$node->data->rankType="finalController";
			elseif ($n->is_interchainer)
					$node->data->rankType="interChainer";
					else 
						$node->data->rankType="notRanked";
			$node->data->icon = ($n->type->slug)
				? $n->type->slug
				: 'private-enterprise';
			if ($n->ID==$postid)
				$node->data->icon .= "Ref";
				elseif ($n->is_final_controller)
					$node->data->icon .= "UltController";
					elseif ($n->is_interchainer)
						$node->data->icon .= "InterChainer";
			// Now, add to the array:
			$network_data->nodes[]=$node;
		}
		if ($postid && $no_net)
			whoowns_save_cached($postid,array('nodes'=>$network_data->nodes));
	}
	
	//Now the edges:
	$cached=false;
	if ($postid && $no_net) {
		if (!($network_data->edges = whoowns_retrieve_cached($postid,'edges',true))) {
			if (!$post_ids)
				$post_ids = whoowns_generate_network($postid);
			$net = whoowns_get_network_relations($post_ids);
		} else
			$cached=true;
	} else
		$net = whoowns_get_network_relations($net);
	if (!$cached) {
		//Generate the edges:
		$network_data->edges = array();
		foreach ($net as $n) {
			unset($edge);
			$edge->data->id="e".$n->share_id;
			$edge->data->source=$n->source_id;
			$edge->data->target=$n->target_id;
			$edge->data->relShare=floatval($n->relative_share);
			$edge->data->color=($n->relative_share>50)
				? '#f00' : '#bbb';
			$edge->data->weightTxt=number_format($n->relative_share,2,',','.').'%';
			$edge->data->sourceName=$n->source_name;
			$edge->data->targetName=$n->target_name;
			$network_data->edges[]=$edge;
		}
		if ($postid && $no_net)
			whoowns_save_cached($postid,array('edges'=>$network_data->edges));
	}
	
	//pR($network_data);exit;
	$network_data = json_encode($network_data);
	return $network_data;
}
function whoowns_load_network_graphic_view_data_callback() {
	$data = whoowns_prepare_network_data_for_visualization($_POST['post_id']);
	echo $data;
	die();
}
add_action('wp_ajax_whoowns_load_network_graphic_view_data', 'whoowns_load_network_graphic_view_data_callback');
//And below I add the same function as an accepted ajax action for not logged in users:
add_action( 'wp_ajax_nopriv_whoowns_load_network_graphic_view_data', 'whoowns_load_network_graphic_view_data_callback' );







function whoowns_show_list_view($postid) {
	if (!$postid)
		return false;
	
	//Get all data:
	$network_data = new stdClass();
	$list = whoowns_generate_direct_shareholders_list($postid,"html");
	$network_data->direct_shareholders = $list->html;

	$list = whoowns_generate_controls_list($postid,"html");
	$network_data->controls = $list->html;

	$list = whoowns_generate_controllers_list($postid,"html");
	$network_data->controllers = $list->html;

	$list = whoowns_generate_participations_list($postid,"html");
	$network_data->participations = $list->html;

	$list = whoowns_generate_indirect_shareholders_list($postid,"html");
	$network_data->indirect_shareholders = $list->html;
	
	//Output the HTML:
	$owner = whoowns_get_owner_data($_POST['post_id']);
	?>
	<h2><?=__("List view","whoowns")?> <span id="whoowns-expand-list-view" class="open"></span></h2>
	<div id="cy-list-intern">
		<?php if ($network_data->direct_shareholders) { ?>
			<h3><?=__("Direct shareholders:","whoowns")?></h3>
			<div class="whoowns_network_list_view">
				<?=$network_data->direct_shareholders?>
			</div>
		<?php } ?>
		<?php if ($network_data->controls) { ?>
			<h3><?=__("Controlled enterprises:","whoowns")?></h3>
			<div class="whoowns_network_list_view">
				<?=$network_data->controls?>
			</div>
		<?php } ?>
		<?php if ($network_data->controllers) { ?>
			<h3><?=__("Controlled by:","whoowns")?></h3>
			<div class="whoowns_network_list_view">
				<?=$network_data->controllers?>
			</div>
		<?php } ?>
		<?php if ($network_data->participations) { ?>
			<h3><?=__("All Participations:","whoowns")?></h3>
			<p class="description"><?=__("Meaning of the values in parenthesis:","whoowns")?> (<?=__("final relative share","whoowns")?>; <?=__("relative share","whoowns")?>; <?=__("share","whoowns")?>)</p>
			<div class="whoowns_network_list_view">
				<p><?=$owner->name?></p>
				<?=$network_data->participations?>
			</div>
		<?php } ?>
		<?php if ($network_data->indirect_shareholders) { ?>
			<h3><?=__("All Controllers:","whoowns")?></h3>
			<p class="description"><?=__("Meaning of the values in parenthesis:","whoowns")?> (<?=__("final relative share","whoowns")?>; <?=__("relative share","whoowns")?>; <?=__("share","whoowns")?>)</p>
			<div class="whoowns_network_list_view">
				<p><?=$owner->name?></p>
				<?=$network_data->indirect_shareholders?>
			</div>
		<?php } ?>
	</div>
	<?php
}
function whoowns_load_network_list_view_data_callback() {
	//pR(whoowns_retrieve_cached($postid,'cy-list'));exit;
	if (!($html=whoowns_retrieve_cached($_POST['post_id'],'cy_list'))) {
		ob_start();
		whoowns_show_list_view($_POST['post_id']);
		$html = ob_get_contents();
		ob_end_clean();
		whoowns_save_cached($_POST['post_id'],array('cy_list'=>trim($html)));
	}
	echo $html;
	die();
}
add_action('wp_ajax_whoowns_load_network_list_view_data', 'whoowns_load_network_list_view_data_callback');
//And below I add the same function as an accepted ajax action for not logged in users:
add_action( 'wp_ajax_nopriv_whoowns_load_network_list_view_data', 'whoowns_load_network_list_view_data_callback' );






function whoowns_load_owner_info_html_callback() {
	do_action('whoowns-show-owner-info', array($_POST['post_id']));
	die();
}
add_action('wp_ajax_whoowns_load_owner_info_html', 'whoowns_load_owner_info_html_callback');
add_action('wp_ajax_nopriv_whoowns_load_owner_info_html', 'whoowns_load_owner_info_html_callback');




function whoowns_show_owner_info($postid) {
	$data = whoowns_get_owner_data($postid, true);
	?>
	<h2>Informações sobre <a href="<?=$data->link?>"><?=$data->name?></a></h2>
	<p class="description"><a href="<?=$data->link?>">Clique para ver sua rede de poder</a></p>
	<p><span class="cy-label">Tipo: </span><a href="<?=$data->type->link?>"><?=$data->type->name?></a></p>
	<?php if ($data->PA) { ?>
		<p><span class="cy-label">Índice de Poder Acumulado: </span><?=$data->IPA?></p>
	<?php } ?>
	<?php if ($data->R) { ?>
		<p><span class="cy-label">Empresa rankeada. Posição no ranking: </span><?=$data->R?></p>
	<?php } ?>
	<?php
}
add_action('whoowns-show-owner-info','whoowns_show_owner_info',0);




function whoowns_show_legends_callback() {
	$legends = array();
	$owner_types = get_terms('whoowns_owner_types', 'hide_empty=0');
	foreach ($owner_types as $i=>$type) {
		$legends['icons'][$i]['label'] = __($type->name, 'whoowns');
		$legends['icons'][$i]['img'] = $type->slug.'_'.get_option('whoowns_legends_icon_size').'.png';
	}
	$legends['colors'] = array(
		array(
			'label'=>__('Center of <i>this</i> network','whoowns'),
			'img'=>'colorRef_'.get_option('whoowns_legends_icon_size').'.png'
		),
		array(
			'label'=>__('Enterprise with interchain participation','whoowns'),
			'img'=>'colorInterChainer_'.get_option('whoowns_legends_icon_size').'.png'
		),
		array(
			'label'=>__('Ultimate controller','whoowns'),
			'img'=>'colorUltController_'.get_option('whoowns_legends_icon_size').'.png'
		)
	);
	$legends['arrows'] = array(
		array(
			'label'=>__('Control over the enterprise (>50%)','whoowns'),
			'img'=>'arrowControl_'.get_option('whoowns_legends_icon_size').'.png'
		),
		array(
			'label'=>__('Participation of less than 50%','whoowns'),
			'img'=>'arrowNoControl_'.get_option('whoowns_legends_icon_size').'.png'
		)
	);
	switch(get_option('whoowns_legends_format')) {
		case 'horizontal':
			?>
			<h2><?=__('Legends','whoowns')?></h2>
			<table id='cy-legends-table'>
				<tr id='cy-legends-table-subtitle'>
					<th colspan="6"><?=__('Icons','whoowns')?></th>
					<th colspan="6"><?=__('Colors','whoowns')?></th>
					<th colspan="4"><?=__('Arrows','whoowns')?></th>
				</tr>
				<tr id='cy-legends-table-content'>
					<?php
					foreach ($legends as $type=>$sublegends) {
						$first=' whoowns-first';
						foreach ($sublegends as $legend) {
							?>
							<td class="whoowns-icon<?=$first?>"><img src="<?=plugins_url('/images/'.$legend['img'], __FILE__ )?>" /></td>
							<td class="whoowns-label"><?=$legend['label']?></td>
						<?php
							if ($first)
								$first='';
						}
					}
					?>
				</tr>
			</table>
			<?php
		break;
		case 'vertical':
		break;
	}
	
	die();
}
add_action('wp_ajax_whoowns_show_legends', 'whoowns_show_legends_callback');
add_action( 'wp_ajax_nopriv_whoowns_show_legends', 'whoowns_show_legends_callback' );



function whoowns_autocomplete_callback() {
	global $wpdb;
	
	$term=$_REQUEST['term'];
	$sql = "SELECT post_title as label, ID as value FROM ".$wpdb->prefix."posts WHERE post_status='publish' AND post_type='whoowns_owner' AND post_title like '%$term%' order by post_title";
	$res = $wpdb->get_results($sql,OBJECT);
	echo json_encode($res);

	die(); // this is required to return a proper result
}
add_action('wp_ajax_whoowns_autocomplete', 'whoowns_autocomplete_callback');
add_action('wp_ajax_nopriv_whoowns_autocomplete', 'whoowns_autocomplete_callback');



function whoowns_select_owners($filters,$s='',$orderby,$order,$page=0) {
	global $wpdb;
	
	$args = array(
		'post_type' => 'whoowns_owner', 
		'fields' => 'ids'
	);
	
	if ($page>=0)
		$args['posts_per_page'] = get_option('whoowns_owners_per_page');
		else 
			$args['nopaging'] = true;
	
	if (!$filters && !$s) {
		$filters='ranked';
		if (!$orderby) {
			$orderby = 'whoowns_PA';
			$order = 'DESC';
		}
	} elseif (!$orderby) {
		$orderby = 'name';
	}
	/*elseif (is_array($filters)) {
		$args['post__in'] = $filters;
		unset($filters);
	}*/
		
	if ($s) {
		if (strpos($s,':')!==false) {
			list($key,$values)=explode(':',$s);
			if ($key == 'owner_ids') {
				$owner_ids = explode('|',$values);
				$args['post__in'] = $owner_ids;
			}
		} else {
			$args['s'] = $s;
			if ($tmp = intval(trim(str_replace(array('.','/',',','-'),'',$s))))
				$args['meta_query'][] = array(
					'key' => 'whoowns_legal_registration',
					'value' => $tmp
				);
		}
	}
	if ($page>0)
		$args['paged'] = $page;
	switch ($orderby) {
		case 'name':
			$args['orderby'] = 'title';
			$args['order'] = 'ASC';
		break;
		default:
			$args['meta_key'] = $orderby;
			$args['orderby'] = 'meta_value_num';
			$args['order'] = 'DESC';
		break;
	}
	
	if (!is_array($filters))
		$filters = explode(',',$filters);
	foreach ($filters as $filter) {
		switch ($filter) {
			case 'ranked':
				$args['meta_query'][] = array(
					'key' => 'whoowns_rank',
					'value' => 0,
					'compare' => '>'
				);
			break;
			case 'person':
			case 'private-enterprise':
			case 'state':
				if (!$args['tax_query']['relation'])
					$args['tax_query']['relation'] = 'OR';
				$args['tax_query'][] = array(
					'taxonomy' => 'whoowns_owner_types',
					'field' => 'slug',
					'terms' => $filter
				);
			break;
		}
	}
	#pR($args);
	$res = new WP_Query( $args );
	if (!$res->posts)
		return false;
	
	$owners = array();
	foreach ($res->posts as $i=>$r) {
		$owners[] = whoowns_get_owner_data($r,true);
	}
	$return->found_posts = $res->found_posts;
	$return->max_num_pages = $res->max_num_pages;
	$return->owners = $owners;
	return $return;
}



function whoowns_retrieve_cached($postid,$fields,$decoded=false) {
	global $wpdb;

	if (!$postid || !$fields)
		return false;
	$fields_sql = (is_array($fields))
		? implode(',',$fields)
		: $fields;
	$sql = "SELECT $fields_sql FROM ".$wpdb->whoowns_networks_cache." WHERE post_id='$postid'";
	if (!($res = $wpdb->get_results($sql)))
		return false;
	if ($decoded) {
		if (is_array($fields)) {
			foreach ($fields as $field)
				$cached->$field = json_decode($res[0]->$field,true);
		} else
			$cached = json_decode($res[0]->$fields,true);
	} else {
		$cached = $res[0]->$fields;
	}
	return $cached;
}



function whoowns_save_cached($postid,$values) {
	global $wpdb;
	foreach ($values as $col=>$val)
		if (is_array($val) || is_object($val))
			$values[$col]=json_encode($val);
	$sql = "SELECT post_id FROM ".$wpdb->whoowns_networks_cache." WHERE post_id='$postid'";
	$res = $wpdb->get_results($sql);
	$res = ($res[0])
		? $wpdb->update( $wpdb->whoowns_networks_cache, $values, array('post_id'=>$postid))
		: $wpdb->insert( $wpdb->whoowns_networks_cache, array_merge(array('post_id'=>$postid),$values));
	return $res;
}



function whoowns_update_network_related_news($postid) {
	$post_ids = whoowns_generate_network($postid);
	$owners = whoowns_get_owner_data($post_ids);
		//Generate the names for the news search api:
	/*foreach ($owners as $o) {
		$names[]='"'.whoowns_clean_owner_name($o->name).'"';
	}*/
	$news_sources_tmp = explode("\n",get_option('whoowns_news_sources'));
	$news_sources = array();
	foreach ($news_sources_tmp as $news_source)
		$news_sources[] = "site:".trim($news_source);
	$news_sources = implode(" | ",$news_sources);
		
	$news_date_format = get_option('whoowns_news_date_format');
	switch (get_option('whoowns_news_search_api')) {
		case 'uol':
			$search_url = "http://busca.uol.com.br/web/?q=";
			//$query_names = implode(' OR ',$names);
			$news = array();
			$news['news'] = array();
			foreach ($owners as $i=>$owner) {
			//foreach ($news_sources as $news_source) {
				$query = '"'.whoowns_clean_owner_name($owner->name).'"';
				$url = $search_url . urlencode("$query ($news_sources)");
				#lg($url);
				if ($tmp = whoowns_extract_uol_news($url,$news_date_format,$news['news']))
					$news['news'] = $news['news'] + $tmp;
			}
			if ($news['news']) {
				krsort($news['news']);
			}
		break;
		case 'bing':
		default:
			$search_url = "http://www.bing.com/search?q={query}&format=rss";
			//$query_names = implode(' OR ',$names);
			$news=array();
			$news['news'] = array();
			foreach ($owners as $owner) {
			//foreach ($news_sources as $news_source) {
				$query = '"'.whoowns_clean_owner_name($owner->name).'"';
				$url = str_replace('{query}',urlencode("$query ($news_sources)"),$search_url);
				#lg($url);
				$retrieved_news = simplexml_load_file($url);
				if ($retrieved_news->channel->item) {
					foreach($retrieved_news->channel->item as $item) {
						$data = array();
						$data['date'] = date($news_date_format, strtotime($item->pubDate));
						$data['link'] = strval($item->link);
						$data['title'] = strval($item->title);
						$data['body'] = strval($item->description);
	    				$news['news'][strtotime($item->pubDate)]=$data;
	    				#lg($data);
				   	}
    			}
			}
			if ($news['news']) {
				krsort($news['news']);
			}
		break;
	}
	#lg($news['news']);
	$news['updated_at'] = strtotime("now");
	whoowns_save_cached($postid,array('news'=>$news));
}
add_action( 'whoowns-update-network-related-news','whoowns_update_network_related_news' );



function whoowns_extract_uol_news($url, $news_date_format, $news) {
	$months = array(
		'jan.' => '01',
		'fev.' => '02',
		'mar.' => '03',
		'abr.' => '04',
		'mai.' => '05',
		'jun.' => '06',
		'jul.' => '07',
		'ago.' => '08',
		'set.' => '09',
		'out.' => '10',
		'nov.' => '11',
		'dez.' => '12'
	);
	lg($url);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$html = curl_exec($ch);
	curl_close($ch);
	lg($html);
	$dom = new DOMDocument();
	$dom->loadHTML(utf8_decode($html));
	$uls = $dom->getElementsByTagName('ul');
	foreach ($uls as $ul) {
		#lg($ul->nodeValue); lg($ul->getAttribute('id'));
		if ($ul->getAttribute('id')=='results') {
			$items = $ul->getElementsByTagName('dl');
			break;
		}
	}
	if ($items->length==0) {
		return false;
	}
	foreach ($items as $item) {
		$data = array();
		$dds = $item->getElementsByTagName('dd');
		foreach ($dds as $dd) {
			$value = $dd->nodeValue;
			#lg($dd->getAttribute('class'));
			if ($dd->getAttribute('class')=='descricao' && ($month = $months[substr($value,3,4)])) {
				$end = ($value[11]==' ')
					? 11
					: 12;
				$date = strtotime(substr($value,8,4).'-'.$months[substr($value,3,4)].'-'.substr($value,0,$end-10));
				$value = '';
				foreach ($dd->childNodes as $node)
					$value .= $dom->saveHTML($node);
				$data['body'] = substr(str_replace('<br>','',$value),$end);
				$data['date'] = date($news_date_format, $date);
			}
		}
		if ($data) {
			$dts = $item->getElementsByTagName('dt');
			foreach ($dts as $dt) {
				$data['title'] = $dt->nodeValue;
				$links = $dt->getElementsByTagName('a');
				foreach ($links as $link)
					$data['link'] = $link->getAttribute('href');
				break;
			}
			for ($i=0;$i<1000;$i++) {
    			if (!$news[$date+$i]) {
    				$news[$date+$i] = $data;
    				break;
    			}
    		}
    	}
	}
	#lg($news);exit;
	return $news;
}



function whoowns_get_network_related_news($postid) {
	if (!$postid)
		return false;
	$news = whoowns_retrieve_cached($postid,'news',true);
	#whoowns_log($news);
	$week = 7*24*3600;
	if (!$news['updated_at'] || (strtotime("now")-$news['updated_at'])>$week) {
		wp_schedule_single_event( time(), 'whoowns-update-network-related-news', array($postid) );
		if (!$news['updated_at'])
			return array("msg"=>__('The news have not been loaded yet. Please come back in a couple of minutes.', 'whoowns'));
	}
	return ($news['news'])
		? $news
		: array("msg"=>__('Sorry, but we couldn\'t find any news related to this owner\'s power network.', 'whoowns'));;
}



function whoowns_get_network_related_articles($postid, $post_type='any', $extra_args, $page=0) {
	if (!$postid)
		return false;
	$net = whoowns_generate_network($postid);
	$args = array(
		'posts_per_page' => get_option('whoowns_owners_per_page'),
		'post_type' => $post_type, 
		'fields' => 'ids',
		'meta_query' => array(
			array(
				'key' => 'whoowns_related_owner',
				'value' => $net,
				'compare' => 'IN'
			)
		)
	);
	if ($page)
		$args['paged'] = $page;
	if ($extra_args)
		$args = $extra_args + $args;
	#pR($args);pR(whoowns_get_owner_data(get_posts($args)));
	return get_posts( $args );
}



//General useful small functions:

function whoowns_verify_pair_redundance($route,$b) {
	$a = end($route);
	//pR($a.' - '.$b);exit;
	$flag=$pair=false;
	foreach ($route as $r) {
		if ($flag && $r==$b) {
			$pair=true;
			break;
		}
		$flag=($r==$a);
	}
	return $pair;
}
function pR($txt) {
	?><pre><?php
	print_r($txt)
	?></pre><hr><?php
}
function whoowns_clean_owner_name($name) {
	return trim(str_replace(array(' SA ',' LTDA ',' LTD ',' INC ', ' SARL '),'',str_replace(array(',',';',':','-','_','.','/','(',')'),'',$name.' ')));
}
function whoowns_log($log) {
	if (!WP_DEBUG)
		return false;
	$f = fopen(plugin_dir_path( __FILE__ )."debug.log", "a+");
	fwrite($f, print_r($log,true)."\n-----------------\n");
	fclose($f);
	return true;
}
function lg($log) {
	return whoowns_log($log);
}
function whoowns_text_limiter($text,$limit) {
	if (strlen($text)<=$limit)
		return $text;
	return substr($text,0,$limit)."...";
}
function whoowns_get_scale($v) {
		if ($v > 1000000000)
			return 1000000000;
		if ($v > 1000000)
			return 1000000;
		if ($v > 1000)
			return 1000;
		return 1;
}
function whoowns_get_scale_txt($v) {
		if ($v > 1000000000)
			return __('billions','whoowns');
		if ($v > 1000000)
			return __('millions','whoowns');
		if ($v > 1000)
			return __('thousand','whoowns');
		return '';
}
function whoowns_format_scaled_value($v) {
	return number_format_i18n($v/whoowns_get_scale($v),2)." ".whoowns_get_scale_txt($v);
}
function dummy_for_translators() {
	__('State','whoowns');
	__('Private enterprise','whoowns');
	__('Person','whoowns');
}
?>
