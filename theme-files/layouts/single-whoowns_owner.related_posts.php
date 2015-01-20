<?php

/**
 *
 * Template fragment to show the Owner's section POWER NETWORK GRAPHIC
 *
 **/

whoowns_set_loop_of_related_articles(get_the_ID());

if ( have_posts() ) : ?>
	<header class="archive-header">
		<h1 class="archive-title"><?php printf( __( 'Related posts: %s', 'whoowns' ), get_the_title(get_the_ID()) ); ?></h1>
	</header><!-- .archive-header -->

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', get_post_format() ); ?>
			<?php endwhile; ?>

			<?php /*twentythirteen_paging_nav();*/ ?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

<?php wp_reset_query(); ?>
