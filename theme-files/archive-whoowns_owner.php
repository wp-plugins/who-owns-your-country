<?php
// If the user chose an owner from the autocomplete, let him/her see directly its page!
if ($_GET['whoowns_auto_id']) {
	$redirect = get_permalink($_GET['whoowns_auto_id']);
	if (!is_wp_error($redirect)) {
		header('Location: '.$redirect);
		exit;
	}
}

$whoowns = whoowns_prepare_owner_selection();
get_header();
?>


 
<section class="subheader">
	<h1 class="main-title"><?=__('Look for owners', 'whoowns')?></h1>
</section>
 

<div id="mainbody"><div id="whoowns">
	<div id="whoowns_search_intro">
		<p>
			<?=__('To search our database, you can use the filters, change the ordering, and also directly type the name or registration id of a person, enterprise or state institution.', 'whoowns')?>
		</p>
	</div>

<form name="whoowns_search_form" method="GET" action="">
<div id="whoowns_search">
	<div id="whoowns_search_options">
		<p class="whoowns_search_options_title"><?=__('Search Options', 'whoowns')?></p>
				<ul>
					<?php foreach ($whoowns->filters_available as $slug=>$label) { ?>
						<li>
						<input type="checkbox" name="whoowns_filters[]" id="checkbox-<?=$slug?>" class="whoowns-css-checkbox whoowns-css-checkbox-<?=$slug?>" value="<?=$slug?>"<?=$whoowns->filter_selected[$slug]?> onClick="whoowns_submit_search(this);" />
						<label for="checkbox-<?=$slug?>" class="whoowns-css-label whoowns-css-label-<?=$slug?>"><?=$label?></label>
						</li>
					<?php } ?>
				</ul>
					<div id="whoowns_search_input">
						<img src="<?=get_stylesheet_directory_uri()?>/images/whoowns/search01.png" id="whoowns_search_button" alt="<?=__('Search', 'whoowns')?>" title="<?=__('Search', 'whoowns')?>" onClick="whoowns_submit_search(this);" />
						<input type="text" name="whoowns_search" value="<?=$whoowns->search_alias?>" class="whoowns_auto_label" alt="whoowns_autocomplete" trigger="submit" onblur="if(this.value=='') this.value='<?=$whoowns->default_search_text?>';" onfocus="if(this.value=='<?=$whoowns->default_search_text?>') this.value='';" />
						<input class="whoowns_auto_id" type="hidden" name="whoowns_auto_id" value=""/>
					</div>
		</div>
	</div>
		

	
	<div id="whoowns_search_orderby_options">
		<table id="whoowns-radio">
			<tr class="radio">
			<td width="10%">
			<p class="opcoes"><?=__('Order by:', 'whoowns')?></p>	
			</td>
			<?php foreach ($whoowns->orderby_options as $orderby=>$label) { ?>		
				<td>
				<input type="radio" name="whoowns_orderby" id="whoowns-radio-<?=$orderby?>" class="css-checkbox" value="<?=$orderby?>"<?=$whoowns->orderby_selected[$orderby]?> onClick="whoowns_submit_search(this);" />
				<label for="whoowns-radio-<?=$orderby?>" class="css-label"><?=$label?></label>
				</td>
			<?php } ?>
			</tr>
		</table>
		</form>
	</div>
		<!-- show the results table -->
		<?php
			if ($owners = whoowns_select_owners($whoowns->filters,$whoowns->search,$whoowns->orderby,$whoowns->order,$whoowns->page)) {
				$final_url = $_SERVER['REDIRECT_URL']
					."?whoowns_filters=".implode(', ',$whoowns->filters)
					."&whoowns_search=".$whoowns->search
					."&whoowns_orderby=".$whoowns->orderby
					."&whoowns_order=".$whoowns->order;
				
				$hide_columns = array( 'rank'=>true, 'controlled_by_final'=>false );
				foreach ($whoowns->filters as $tmp_filter) {
					if (in_array($tmp_filter,array('ranked','private-enterprise')))
						$hide_columns['rank'] = false;
					if (in_array($tmp_filter,array('ranked','person')))
						$hide_columns['controlled_by_final'] = true;
				}
					
				?>
					<div id="nav_page_ranking">
	    				<ul class="item-nav-ranking">
           					<?php if ($whoowns->page<($owners->max_num_pages-1)) { ?>
           			 			<li class='next'> <a href="<?=$final_url?>&whoowns_page=<?=($whoowns->page+1)?>" rel="next"></a></li>
           					<?php } ?>
           					<?php if ($whoowns->page>0) { ?>
           						<li class='prev'> <a href="<?=$final_url?>&whoowns_page=<?=($whoowns->page-1)?>" rel="prev"></a></li>
           					<?php } ?>
			        	</ul>
					</div>
					<?php if ($whoowns->subtitle) { ?>
						<div id="whoowns_applied_filters"><?=$whoowns->subtitle?></div>
					<?php } ?>
					<div id="whoowns_summary">
						<?=str_replace('{num}',$owners->found_posts,__('{num} results found', 'whoowns'))?> - <?=str_replace(array('{pg}','{total_pg}'),array($whoowns->page+1, $owners->max_num_pages),__('Page #{pg} of {total_pg}', 'whoowns'))?>
					</div>

				<?php
				whoowns_template_show_owners($owners->owners, $hide_columns);
				?>
				<div id="nav_page_ranking">
    				<ul class="item-nav-ranking">
       					<?php if ($whoowns->page<($owners->max_num_pages-1)) { ?>
       			 			<li class='next'> <a href="<?=$final_url?>&whoowns_page=<?=($whoowns->page+1)?>" rel="next"></a></li>
       					<?php } ?>
       					<?php if ($whoowns->page>0) { ?>
       						<li class='prev'> <a href="<?=$final_url?>&whoowns_page=<?=($whoowns->page-1)?>" rel="prev"></a></li>
       					<?php } ?>
		        	</ul>
				</div>
				<div id="whoowns_summary">
					<?=str_replace('{num}',$owners->found_posts,__('{num} results found', 'whoowns'))?> - <?=str_replace(array('{pg}','{total_pg}'),array($whoowns->page+1, $owners->max_num_pages),__('Page #{pg} of {total_pg}', 'whoowns'))?>
				</div>
				<?php
			} else {
				?>
				<div id="whoowns_noresults"><?=__("Sorry, but nothing matched your search criteria. Please try again with some different keywords.", 'whoowns')?></div>
				<?php
			}
		?>
	</div>
</div></div>


<?php get_footer(); ?>
