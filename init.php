<?php

load_plugin_textdomain('whoowns', false, basename( dirname( __FILE__ ) ) . '/languages' );

function create_whoowns_taxonomies() {
	$labels = array(
		'name' => _x( 'Owner types', 'taxonomy general name', 'whoowns' ),
		'singular_name' => _x( 'Owner type', 'taxonomy singular name', 'whoowns' ),
		'search_items' =>  __( 'Search owner types', 'whoowns' ),
		'all_items' => __( 'All owner types', 'whoowns' ),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __( 'Edit Owner Type', 'whoowns' ),
		'update_item' => __( 'Update Owner Type', 'whoowns' ),
		'add_new_item' => __( 'Add New Owner Type', 'whoowns' ),
		'new_item_name' => __( 'New Owner Type', 'whoowns' ),
		'menu_name' => __( 'Owner Types', 'whoowns' ),
	);   

	// Now register the taxonomy

	register_taxonomy('whoowns_owner_types',array('whoowns_owner'), array(
		'hierarchical' => false,
		'labels' => $labels,
		'show_ui' => true,
		'show_admin_column' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'owner-type' ),
	));
	
	$labels = array(
		'name' => _x( 'Source types', 'taxonomy general name', 'whoowns' ),
		'singular_name' => _x( 'Source type', 'taxonomy singular name', 'whoowns' ),
		'search_items' =>  __( 'Search source types', 'whoowns' ),
		'all_items' => __( 'All source types', 'whoowns' ),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __( 'Edit Source Type', 'whoowns' ),
		'update_item' => __( 'Update Source Type', 'whoowns' ),
		'add_new_item' => __( 'Add New Source Type', 'whoowns' ),
		'new_item_name' => __( 'New Source Type', 'whoowns' ),
		'menu_name' => __( 'Source Types', 'whoowns' ),
	);   

	// Now register the taxonomy

	register_taxonomy('whoowns_source_types',array('whoowns_owner'), array(
		'hierarchical' => false,
		'labels' => $labels,
		'show_ui' => true,
		'show_admin_column' => false,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'source-type' ),
	));
	
	
	$labels = array(
		'name' => _x( 'State or Province', 'taxonomy general name', 'whoowns' ),
		'singular_name' => _x( 'State or Province', 'taxonomy singular name', 'whoowns' ),
		'search_items' =>  __( 'Search states', 'whoowns' ),
		'all_items' => __( 'All states', 'whoowns' ),
		'parent_item' => _x( 'Region', 'taxonomy general name', 'whoowns' ),
		'parent_item_colon' => _x( 'Region', 'taxonomy general name', 'whoowns' ),
		'edit_item' => __( 'Edit state', 'whoowns' ),
		'update_item' => __( 'Update state', 'whoowns' ),
		'add_new_item' => __( 'Add New state', 'whoowns' ),
		'new_item_name' => __( 'New state', 'whoowns' ),
		'menu_name' => __( 'States', 'whoowns' ),
	);   

	// Now register the taxonomy

	register_taxonomy('whoowns_geo',array('whoowns_geo'), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'show_admin_column' => false,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'UF' )
	));
}
add_action( 'init', 'create_whoowns_taxonomies', 0 );

function create_whoowns_owner_post_type() {
	$labels = array(
		'name'                => __( 'Owners', 'whoowns' ),
		'singular_name'       => __( 'Owner', 'whoowns' ),
		'menu_name'           => __( 'Owners', 'whoowns' ),
		'parent_item_colon'   => __( 'Parent Owner:', 'whoowns' ),
		'all_items'           => __( 'All Owners', 'whoowns' ),
		'view_item'           => __( 'View Owner', 'whoowns' ),
		'add_new_item'        => __( 'Add New Owner', 'whoowns' ),
		'add_new'             => __( 'New Owner', 'whoowns' ),
		'edit_item'           => __( 'Edit Owner', 'whoowns' ),
		'update_item'         => __( 'Update Owner', 'whoowns' ),
		'search_items'        => __( 'Search owners', 'whoowns' ),
		'not_found'           => __( 'No owners found', 'whoowns' ),
		'not_found_in_trash'  => __( 'No owners found in Trash', 'whoowns' ),
	);
	$rewrite = array(
		'slug'                => get_option('whoowns_owner_slug'),
		'with_front'          => true,
		'pages'               => true,
		'feeds'               => true,
	);
	$args = array(
		'label'               => 'whoowns_owner',
		'description'         => __( 'Facts-sheet about an Owner', 'whoowns' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'author', 'thumbnail', 'comments', 'revisions'),
		'taxonomies'          => array(
									'whoowns_owner_types', 
									'whoowns_source_types', 
									'whoowns_geo'
								),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => get_template_directory_uri().'/favicon.ico',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'query_var'           => 'whoowns',
		'rewrite'             => $rewrite,
		'capability_type'	  => 'post'
		/*'capability_type'     => 'whoowns_owner',
		'map_meta_cap'        => true*/
	);
	register_post_type( 'whoowns_owner', $args );
}
add_action( 'init', 'create_whoowns_owner_post_type', 1 );

function add_whoowns_owner_caps_to_users() {
  $whoowns_capabilities=get_option('whoowns_capabilities');
  
  // Para uso somente como devel!
  #whoowns_clean_caps();exit;

  add_role( 'whoowns_owner_author', __('Owner Author', 'whoowns') );
  
  # Author capabilities:
  $caps = array_merge(array('read'),$whoowns_capabilities['contributor']);

  $roles = array(
    get_role( 'author' ),
    get_role( 'contributor' ),
    get_role( 'whoowns_owner_author' ),
  );
  foreach ($roles as $role) {
    foreach ($caps as $cap) {
      $role->add_cap( $cap );
    }
  }
  
  #admin and editors
  $caps = array_merge($caps,$whoowns_capabilities['admin']);

  $roles = array(
    get_role( 'administrator' ),
    get_role( 'editor' ),
  );

  foreach ($roles as $role) {
    foreach ($caps as $cap) {
      $role->add_cap( $cap );
    }
  }
}
#add_action( 'init', 'add_whoowns_owner_caps_to_users',10 );

function whoowns_clean_caps(){
	global $wp_roles;
	$whoowns_capabilities=get_option('whoowns_capabilities');
    $delete_caps = array_merge($whoowns_capabilities['contributor'],$whoowns_capabilities['admin']);
    foreach ($delete_caps as $cap) {
        foreach (array_keys($wp_roles->roles) as $role) {
            $wp_roles->remove_cap($role, $cap);
        }
    }
    remove_role('whoowns_owner_author');
}

//Add cytoscape javascript for owners pages
function whoowns_add_visual_network_script() {
	global $post;
	if (!is_singular('whoowns_owner'))
		return;
    wp_enqueue_script( 'whoowns-cytoscape-script', plugins_url('/includes/cytoscape-js/cytoscape.js', __FILE__ ), array('jquery') );
    wp_enqueue_script( 'whoowns-cytoscape-script-arbor', plugins_url('/includes/cytoscape-js/arbor.js', __FILE__ ), array('jquery') );
    wp_enqueue_script( 'whoowns-cytoscape-script-panzoom', plugins_url('/includes/cytoscape-js/jquery.cytoscape-panzoom.min.js', __FILE__ ), array('jquery') );
    wp_enqueue_style( 'whoowns-cytoscape-panzoom-css', plugins_url('/includes/cytoscape-js/jquery.cytoscape-panzoom.css', __FILE__ ) );
    wp_enqueue_script( 'whoowns-network-data-loader-script', plugins_url('/show_visual_network.js', __FILE__ ), array('jquery','whoowns-cytoscape-script', 'whoowns-cytoscape-script-arbor') );
    wp_localize_script( 'whoowns-network-data-loader-script', 'ajax_object', 
    	array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 
    		'post_id' => $post->ID, 
    		'img_url' => plugins_url('/images/', __FILE__ ), 
    		'threshold_nodes_with_names' => get_option('whoowns_threshold_show_names_in_network'), 
    		'threshold_moving_edges' => get_option('whoowns_threshold_show_arrows_in_network_when_move')
    	)
    );
}
add_action('wp_enqueue_scripts', 'whoowns_add_visual_network_script');

function add_whoowns_script($hook) {
	wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_script( 'whoowns-script', plugins_url('/utils.js', __FILE__ ), array('jquery', 'jquery-ui-autocomplete') );
    wp_localize_script( 'whoowns-script', 'whoowns_ajax_object', 
    	array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 
    		'autocomplete_allow_non_listed_value' => !( strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php'))
    	)
    );
}
add_action('wp_enqueue_scripts', 'add_whoowns_script');
add_action('admin_enqueue_scripts', 'add_whoowns_script');

function add_whoowns_styles() {
	wp_enqueue_style( 'whoowns-css', plugins_url('/theme-files/whoowns.css', __FILE__ ) 
);
	wp_enqueue_style( 'font-awesome-styles', plugins_url('/theme-files/fontello.css', __FILE__ ) );
}
add_action('wp_enqueue_scripts', 'add_whoowns_styles',0);

function whoowns_redirect_after_login( $redirect_to, $request, $user ) {
	global $user;
    if( isset( $user->roles ) && is_array( $user->roles ) ) {
        //check for owner contributors
        if( in_array( "whoowns_owner_author", $user->roles ) ) {
            return admin_url( '/edit.php?post_type=whoowns_owner' );
            //return plugins_url( 'independent_scripts/get_owner_images.php' , __FILE__ );
        } else {
            return $redirect_to;
        }
    } else {
        return $redirect_to;
    }
}
add_filter("login_redirect", "whoowns_redirect_after_login",10,3);

function include_template_files($template) {
    $plugin_dir = dirname( __FILE__ );
    $theme_dir = get_template_directory();

    if (is_post_type_archive( 'whoowns_owner' )) {
        $template_filename = 'archive-whoowns_owner.php';
        
    } elseif (get_post_type() == 'whoowns_owner' ){
        $template_filename = 'single-whoowns_owner.php';
    }
    
    if ($template_filename && !file_exists($theme_dir.'/'.$template_filename)) {
        	$plugin_template = $plugin_dir . '/theme-files/' . $template_filename;
        	return $plugin_template;
    }
    
    return $template;
}
add_filter( 'template_include', 'include_template_files' );
?>
