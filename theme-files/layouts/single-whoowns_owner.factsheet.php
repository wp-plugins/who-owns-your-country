<?php
/**
 *
 * Template fragment to show the Owner's Factsheet section
 *
 **/

$type_txt = ($owner_data->type->slug == 'person')
	? __('person', 'whoowns')
	: __('enterprise', 'whoowns');
if ($owner_data->is_closed) {
	$first_col = 'column_one';
	$second_col = 'column_two';
} else {
	$first_col = 'whoowns_padding_image';
	$second_col = '';
}
?>
<div id='whoowns_intro'>
	<div id="whoowns_owner_image"><?=$owner_data->image?></div>
	<div class="<?=$first_col?> whoowns_details">
		<p><strong><?=__('Type', 'whoowns')?>:</strong> <a href='<?=$owner_data->type->link?>'><?=$owner_data->type->name?></a></p>
		<?php if ($owner_data->legal_registration) { ?>
			<p><strong><?=__('Legal registration ID', 'whoowns')?>:</strong> <?=$owner_data->legal_registration?></p>
		<?php
		}
		if ($owner_data->rank) { ?>
			<p><strong><?=str_replace('{rank}',$owner_data->rank,__('Enterprise ranked #{rank}', 'whoowns'))?></strong></p>
		<?php
		}
		if ($owner_data->IPA) { ?>
			<p><strong><?=__('Accumulated Power Index (API)', 'whoowns')?>:</strong> <?=number_format($owner_data->IPA,8,',','.')?></p>
		<?php
		}
		if ($owner_data->controlled_by_final) { ?>
			<p><strong><?=__('Ultimate controller', 'whoowns')?>:</strong> <a href="<?=$owner_data->controlled_by_final->link?>"><?=$owner_data->controlled_by_final->name?></a></p>
		<?php
		}
		if ($owner_data->controls_final_top) { ?>
			<p><strong><?=__('Ultimate controlled enterprises', 'whoowns')?>:</strong> <?=$owner_data->controls_final_top?></p>
		<?php
		}
		if ($owner_data->participation_of_reference_owner) { ?>
			<p><strong><?=str_replace('{owner}',$owner_data->participation_of_reference_owner->ref->name,__('Relative participation of {owner}', 'whoowns'))?>:</strong> <?=$owner_data->participation_of_reference_owner->html?></p>
		<?php
		}
		if ($owner_data->revenue['value']) { ?>
			<p><strong><?=__('Revenue', 'whoowns')?>:</strong> <?=get_option('whoowns_currency')?> <?=number_format($owner_data->revenue['value'],2,',','.')?> <?=__('millions', 'whoowns')?></p>
		<?php } ?>
	</div>
	<?php if ($owner_data->is_closed) { ?>
		<div class="<?=$second_col?>">
			<img src="<?=plugins_url( '../../images/open_it.png' , __FILE__ )?>" /> <?=__('We still don\'t know the owners of this enterprise. Do you want to collaborate opening it?', 'whoowns')?> <a href="<?php the_permalink(); ?>/wp-login.php?action=register"><?=__('Click here', 'whoowns')?>.</a>
		</div>
	<?php } ?>
</div>



<div id="whoowns-factsheet">
	<?=whoowns_template_show_factsheet_blocks($owner_data)?>
</div>

<?php
