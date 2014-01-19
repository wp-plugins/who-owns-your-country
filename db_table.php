<?php
// Table names:
if (!isset($wpdb->whoowns_shares)) {
	$wpdb->whoowns_shares = $wpdb->prefix."whoowns_shares";
}
if (!isset($wpdb->whoowns_networks_cache)) {
	$wpdb->whoowns_networks_cache = $wpdb->prefix."whoowns_networks_cache";
}

// Version of the table
global $whoowns_table_db_version;
$whoowns_table_db_version = '0.6';
$installed_ver = get_option('whoowns_table_db_version');
if (!$installed_ver || $installed_ver != $whoowns_table_db_version) {
	whoowns_table_update($installed_ver);
}

function whoowns_table_update($installed_ver) {
    global $wpdb, $whoowns_table_db_version;
    
	// NOTICE that:
	// 1. each field MUST be in separate line
	// 2. There must be two spaces between PRIMARY KEY and its name
	//    Like this: PRIMARY KEY[space][space](id)
	// otherwise dbDelta will not work
	$sql1 = "CREATE TABLE ".$wpdb->whoowns_shares." (
    	  id int(11) NOT NULL AUTO_INCREMENT,
    	  from_id int(11) NOT NULL,
    	  to_id int(11) NOT NULL,
    	  share float NOT NULL,
    	  relative_share float NOT NULL,
    	  PRIMARY KEY  (id),
    	  KEY from_id  (from_id),
    	  KEY to_id  (to_id)
	)";
	
	$sql2 = "CREATE TABLE ".$wpdb->whoowns_networks_cache." (
    	  post_id int(11) NOT NULL,
    	  post_ids longtext NOT NULL,
    	  nodes longtext NOT NULL,
    	  edges longtext NOT NULL,
    	  cy_list longtext NOT NULL,
    	  news longtext NOT NULL,
    	  PRIMARY KEY  (post_id)
	)";
	// we do not execute sql directly
	// we are calling dbDelta which can migrate the database
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql1);
	dbDelta($sql2);
	//echo $wpdb->last_error; exit;

	if ($installed_ver)
		update_option('whoowns_table_db_version', $whoowns_table_db_version);
	else
		add_option('whoowns_table_db_version', $whoowns_table_db_version);
}



function whoowns_table_uninstall() {
    global $wpdb;
	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->whoowns_shares);
}


?>
