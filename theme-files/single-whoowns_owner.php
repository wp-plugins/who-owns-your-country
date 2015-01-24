<?php
	/*
	Template Name: Owners 
	*/
	
	//whoowns_update();exit;
	//whoowns_init_owner_universe_update(get_the_ID());exit;
	
	$section = ($_GET['section'])
		? $_GET['section']
		: "factsheet";

	$owner_data = whoowns_template_get_owner_data(get_the_ID(), $section);
	$section_path = whoowns_get_factsheet_section_path($section);

	get_header();

	#pR($owner_data);exit;
?>
<h1 class="entry-title"><?php the_title(); ?></h1>
<section id="mainbody">
	<div id="whoowns">
		<?=whoowns_template_show_factsheet_sections_submenu($section)?>
		<?php require( $section_path ); ?>
		<br style="clear:both" />
		<?php
		if ($section!='power_network')
			comments_template();
		?>
	</div>
</section>

<?php if ($section!='power_network') {
	get_sidebar();
	get_footer();
}
?>
