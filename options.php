<?php

//Create settings for the plugin
function whoowns_register_settings() {
	register_setting( 'whoowns', 'whoowns_default_shareholders_number' );
	register_setting( 'whoowns', 'whoowns_default_related_owners_number' );
	register_setting( 'whoowns', 'whoowns_supported_file_types' );
	register_setting( 'whoowns', 'whoowns_owners_per_page' );
	register_setting( 'whoowns', 'whoowns_owner_image_size' );
	register_setting( 'whoowns', 'whoowns_owner_slug' );
	register_setting( 'whoowns', 'whoowns_currency' );
	register_setting( 'whoowns', 'whoowns_legends_icon_size' );
	register_setting( 'whoowns', 'whoowns_legends_format' );
	register_setting( 'whoowns', 'whoowns_threshold_show_names_in_network' );
	register_setting( 'whoowns', 'whoowns_threshold_show_arrows_in_network_when_move' );
	register_setting( 'whoowns', 'whoowns_news_search_api' );
	register_setting( 'whoowns', 'whoowns_news_sources' );
	register_setting( 'whoowns', 'whoowns_news_date_format' );
	register_setting( 'whoowns', 'whoowns_reference_owner' );
	register_setting( 'whoowns', 'whoowns_cron_ref_hour' );
	register_setting( 'whoowns', 'whoowns_cron_frequency' );
}

function whoowns_set_defaults() {
	update_option('whoowns_owner_slug','owners');
	update_option('whoowns_currency','US$');
	update_option('whoowns_default_shareholders_number',15);
	update_option('whoowns_default_related_owners_number',15);
	update_option('whoowns_relative_share_for_dummy_shareholders',5);
	update_option('whoowns_owners_per_page',400);
	update_option('whoowns_owner_image_size','150x150');
	update_option('whoowns_legends_icon_size','16');
	update_option('whoowns_legends_format','horizontal');
	update_option('whoowns_threshold_show_names_in_network',15);
	update_option('whoowns_threshold_show_arrows_in_network_when_move',20);
	update_option('whoowns_news_search_api','bing');
	update_option('whoowns_news_sources',"valor.com.br\nbrasildefato.com.br");
	update_option('whoowns_news_date_format','d/m/Y');
	update_option('whoowns_reference_owner','');
	update_option('whoowns_cron_ref_hour','0');
	update_option('whoowns_cron_frequency','hourly');
	
	update_option('whoowns_factsheet_sections', array(
			10 => array('ord'=>10, 'slug'=>'factsheet','title'=>'global vision', 'path'=>plugin_dir_path(__FILE__)."theme-files/layouts/single-whoowns_owner.factsheet.php"),
			20 => array('ord'=>20, 'slug'=>'power_network','title'=>'power network', 'path'=>plugin_dir_path(__FILE__)."theme-files/layouts/single-whoowns_owner.power_network.php"),
			30 => array('ord'=>30, 'slug'=>'related_posts','title'=>'related articles', 'path'=>plugin_dir_path(__FILE__)."theme-files/layouts/single-whoowns_owner.related_posts.php"),
			40 => array('ord'=>40, 'slug'=>'news','title'=>'in the media', 'path'=>plugin_dir_path(__FILE__)."theme-files/layouts/single-whoowns_owner.news.php")
		)
	);
		
	update_option('whoowns_supported_file_types',array('application/pdf', 'application/postscript', 'text/plain', 'image/bmp', 'application/msword', 'image/gif', 'text/html', 'image/jpeg', 'application/vnd.ms-powerpoint', 'text/richtext', 'image/tiff', 'application/zip', 'application/x-abiword', 'text/csv', 'message/rfc822', 'application/x-gnumeric', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.presentation', 'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.text', 'image/vnd.adobe.photoshop', 'image/png', 'text/richtext', 'application/rtf', 'image/svg+xml'));
	update_option('whoowns_capabilities',array (
		'contributor'=>array(
			'read_whoowns_owners',
			'edit_whoowns_owners',
			'delete_whoowns_owners',
			'edit_private_whoowns_owners',
			'edit_others_whoowns_owners'
		),
		'admin'=>array(
			'read_private_whoowns_owners',
			'edit_published_whoowns_owners',
			'publish_whoowns_owners',
			'delete_private_whoowns_owners',
			'delete_published_whoowns_owners',
			'delete_others_whoowns_owners'
		)
	));
}

function whoowns_settings_page() {
	// If settings are updated, the rewrite rules cache must be cleared and the update schedule initialized:
	if(isset($_GET['settings-updated']) && $_GET['settings-updated']) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules( false );
		whoowns_initialize_update_schedule();
	}
	?>
	<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?=__('Settings of the Who Owns Plugin','whoowns')?></h2>
	<form method="post" action="options.php">
	<?php
	settings_fields( 'whoowns' );
	do_settings_sections( 'myoption-group' );
	?>
	<table class="form-table">
		<tr valign="top">
		<th scope="row" colspan="2"><h2><?=__('General settings','whoowns')?></h2></th>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Slug for the owners\' section of the website','whoowns')?></th>
		<td><input type="text" name="whoowns_owner_slug" value="<?=get_option('whoowns_owner_slug')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Currency','whoowns')?></th>
		<td><input type="text" name="whoowns_currency" value="<?=get_option('whoowns_currency')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Maximum number of shareholders to display in the edit form','whoowns')?></th>
		<td><input type="text" name="whoowns_default_shareholders_number" value="<?=get_option('whoowns_default_shareholders_number')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Maximum number of related owners to display in the edit form','whoowns')?></th>
		<td><input type="text" name="whoowns_default_related_owners_number" value="<?=get_option('whoowns_default_related_owners_number')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Number of owners to display per page in the ranking table','whoowns')?></th>
		<td><input type="text" name="whoowns_owners_per_page" value="<?=get_option('whoowns_owners_per_page')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Reference enterprise/person to track','whoowns')?></th>
		<td><input class="whoowns_auto_label" type="text" name="whoowns_reference_owner_name" alt="whoowns_autocomplete" id="whoowns_reference_owner_name" value="<?=get_the_title(get_option('whoowns_reference_owner'))?>" size="30"/><input class="whoowns_auto_id" type="hidden" name="whoowns_reference_owner" id="whoowns_reference_owner" value="<?=get_option('whoowns_reference_owner')?>"/>
			<p class="description"><?=__("Is there any of the enterprises or persons of the database which participations should be tracked? If so, for each owner the plugin will provide [through function whoowns_get_owner_data] the final participation of the chosen reference in its controllers chain", "whoowns")?></p>
		</td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Default size of the owner image. Put width and height separated by an "x". For example: 300x300','whoowns')?></th>
		<td><input type="text" name="whoowns_owner_image_size" value="<?=get_option('whoowns_owner_image_size')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row" colspan="2"><h2><?=__('News related to network','whoowns')?></h2></th>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('News search engine','whoowns')?></th>
		<td><select name="whoowns_news_search_api">
			<option value="bing"<?php if (get_option('whoowns_news_search_api')=='bing') echo " selected='selected'"; ?>">Bing</option>
			<option value="uol"<?php if (get_option('whoowns_news_search_api')=='uol') echo " selected='selected'"; ?>">Uol</option>
			<!--<option value="entireweb"<?php if (get_option('whoowns_news_search_api')=='entireweb') echo " selected='selected'"; ?>">EntireWeb</option>-->
			</select>
		</td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Date format for showing the  news','whoowns')?></th>
		<td><input type="text" name="whoowns_news_date_format" value="<?=get_option('whoowns_news_date_format')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('News sources from internet (one per line)','whoowns')?></th>
		<td><textarea name="whoowns_news_sources" rows='6' cols='40'><?=get_option('whoowns_news_sources')?></textarea></td>
		</tr>
		
		<tr valign="top">
		<th scope="row" colspan="2"><h2><?=__('Power network graphic','whoowns')?></h2></th>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Maximum number of nodes for a power network graph to show all names','whoowns')?></th>
		<td><input type="text" name="whoowns_threshold_show_names_in_network" value="<?=get_option('whoowns_threshold_show_names_in_network')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Maximum number of edges for a power network graph to show arrows while moving canvas','whoowns')?></th>
		<td><input type="text" name="whoowns_threshold_show_arrows_in_network_when_move" value="<?=get_option('whoowns_threshold_show_arrows_in_network_when_move')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Format of the legends in the network graphic','whoowns')?></th>
		<td><input type="text" name="whoowns_legends_format" value="<?=get_option('whoowns_legends_format')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Size of the icons of the legends of the network graphic','whoowns')?></th>
		<td><input type="text" name="whoowns_legends_icon_size" value="<?=get_option('whoowns_legends_icon_size')?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row" colspan="2"><h2><?=__('Settings for scheduling network updates','whoowns')?></h2></th>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('How often should the plugin check for changes in the database and recalculate the power indexes and ranking?','whoowns')?></th>
		<td>
			<select name="whoowns_cron_frequency">
				<?php foreach (wp_get_schedules() as $frequency_slug=>$frequency) { ?>
					<option value="<?=$frequency_slug?>"<?php if (get_option('whoowns_cron_frequency')==$frequency_slug) echo " selected='selected'"; ?>><?=__($frequency['display'])?></option>
				<?php } ?>
			</select>
			<p class="description"><?=__('Take care to not put it too frequently, because there is a good quantity of relatively heavy calculations. If the server is good and you have a small database, it\'s ok to have it hourly, but we recommend the frequency to be once or twice daily','whoowns')?></p>
		</td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><?=__('Reference local time for the scheduled updates (24h format)','whoowns')?></th>
		<td>
			<input type="text" name="whoowns_cron_ref_hour" value="<?=get_option('whoowns_cron_ref_hour')?>" />
			<p class="description"><?=__('If the frequency is once daily, we suggest that you choose some time in late night, like midnight (0). Important: this time depends on the local time zone that you chose in the website configuration in "general settings".','whoowns')?></p>
		</td>
		</tr>
	</table>
	<?php submit_button(); ?>
	</form>
	</div>
	<?php
}
?>
