<?php
/**
 * The template for displaying single posts
 *
 * @package Tortuga
 */

?>
<div id="magazine-homepage-widgets" class="widget-area clearfix">

			<div id="custom_html-3" class="widget_text widget widget_custom_html"><div class="textwidget custom-html-widget">


<iframe src="https://rcm-cn.amazon-adsystem.com/e/cm?o=28&p=48&l=ur1&category=udio_video&banner=1N8F0FSTZ71W0Z5VNYR2&f=ifr&linkID=7a8baaa56abbc727b6a3dc3d17f3f4c0&t=uzzz08-23&tracking_id=uzzz08-23" width="728" height="90" scrolling="no" border="0" marginwidth="0" style="border:none;" frameborder="0"></iframe>
				
				</div></div>
		</div>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php tortuga_post_image_single(); ?>

	<header class="entry-header">

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<?php tortuga_entry_meta(); ?>

	</header><!-- .entry-header -->

	<div class="entry-content clearfix">

		<?php the_content(); ?>

		<?php wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'tortuga' ),
			'after'  => '</div>',
		) ); ?>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php tortuga_entry_tags(); ?>
		<?php do_action( 'tortuga_author_bio' ); ?>
		<?php tortuga_post_navigation(); ?>

	</footer><!-- .entry-footer -->

</article>
