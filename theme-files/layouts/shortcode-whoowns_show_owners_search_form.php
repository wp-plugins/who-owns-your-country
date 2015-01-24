<form name="whoowns_search_form" method="GET" action="<?= get_post_type_archive_link( 'whoowns_owner' ) ?>">
<div id="whoowns_search">
	<div id="whoowns_search_options">
		<?php if ($show_filters) { ?>
			<?php if ($show_titles) { ?>
				<p class="whoowns_search_options_title"><?=__('Search Options', 'whoowns')?></p>
			<?php } ?>
			<ul>
				<?php foreach ($whoowns->filters_available as $slug=>$label) { ?>
					<li>
						<input type="checkbox" name="whoowns_filters[]" id="checkbox-<?=$slug?>" class="whoowns-css-checkbox whoowns-css-checkbox-<?=$slug?>" value="<?=$slug?>"<?=$whoowns->filter_selected[$slug]?> onClick="whoowns_submit_search(this);" />
						<label for="checkbox-<?=$slug?>" class="whoowns-css-label whoowns-css-label-<?=$slug?>"><?=$label?></label>
					</li>
				<?php } ?>
			</ul>
		<?php } ?>
		<div id="whoowns_search_input">
			<img src="<?=get_stylesheet_directory_uri()?>/images/whoowns/search01.png" id="whoowns_search_button" alt="<?=__('Search', 'whoowns')?>" title="<?=__('Search', 'whoowns')?>" onClick="whoowns_submit_search(this);" />
			<input type="text" name="whoowns_search" value="<?=$whoowns->search_alias?>" class="whoowns_auto_label" alt="whoowns_autocomplete" trigger="submit" onblur="if(this.value=='') this.value='<?=$whoowns->default_search_text?>';" onfocus="if(this.value=='<?=$whoowns->default_search_text?>') this.value='';" />
			<input class="whoowns_auto_id" type="hidden" name="whoowns_auto_id" value=""/>
		</div>
		
	</div>
</div>

<?php if ($show_orderby) { ?>
	<div id="whoowns_search_orderby_options">
		<table id="whoowns-radio">
			<tr class="radio">
			<?php if ($show_titles) { ?>
				<td width="10%">
					<p class="opcoes"><?=__('Order by:', 'whoowns')?></p>	
				</td>
			<?php } ?>
			<?php foreach ($whoowns->orderby_options as $orderby=>$label) { ?>
				<td>
					<input type="radio" name="whoowns_orderby" id="whoowns-radio-<?=$orderby?>" class="css-checkbox" value="<?=$orderby?>"<?=$whoowns->orderby_selected[$orderby]?> onClick="whoowns_submit_search(this);" />
					<label for="whoowns-radio-<?=$orderby?>" class="css-label"><?=$label?></label>
				</td>
			<?php } ?>
			</tr>
		</table>
	</div>
<?php } ?>

</form>
