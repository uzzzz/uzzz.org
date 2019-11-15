<?php
/**
 * The template for displaying single posts
 *
 * @package Tortuga
 */

?>
<div id="magazine-homepage-widgets" class="widget-area clearfix">

			<div id="custom_html-3" class="widget_text widget widget_custom_html"><div class="textwidget custom-html-widget"><script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- 展示-横幅-自适应-广告 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-8889449066804352"
     data-ad-slot="6054387901"
     data-ad-format="auto"
     data-full-width-responsive="true"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script></div></div>
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
