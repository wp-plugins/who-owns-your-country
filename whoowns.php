<?php
/*
Plugin Name: Who owns Your Country?
Plugin URI: http://github.com/dtygel/whoowns
Description: Wordpress Plugin to calculate and show the Economic Power Networks in your country
Author: Daniel Tygel
Version: 0.8
Author URI: http://cirandas.net/dtygel
*/

require_once dirname( __FILE__ ) . '/utils.php';
require_once dirname( __FILE__ ) . '/utils_batch.php';
require_once dirname( __FILE__ ) . '/init.php';
require_once dirname( __FILE__ ) . '/db_table.php';

if ( is_admin() ) {
	require_once dirname( __FILE__ ) . '/init_admin.php';
	require_once dirname( __FILE__ ) . '/options.php';
	require_once dirname( __FILE__ ) . '/admin.php';
}

function whoowns_activate () {
	if ( ! current_user_can( 'activate_plugins' ) )
		return;
	create_whoowns_owner_post_type();
	whoowns_set_defaults();
	create_whoowns_taxonomies();
	whoowns_populate_taxonomies();
	whoowns_initialize_update_schedule();
}
register_activation_hook(__FILE__, 'whoowns_activate');

?>
