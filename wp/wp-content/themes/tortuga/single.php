<?php
/**
 * The template for displaying all single posts.
 *
 * @package Tortuga
 */

get_header(); ?>

	<section id="primary" class="content-single content-area">
		<main id="main" class="site-main" role="main">
				
		<?php while ( have_posts() ) : the_post();

			get_template_part( 'template-parts/content', 'single' );

			tortuga_related_posts();

			comments_template();

		endwhile; ?>
			
			<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js">	</script>
			<ins class="adsbygoogle"
				 style="display:block"
				 data-ad-format="autorelaxed"
				 data-ad-client="ca-pub-8889449066804352"
				 data-ad-slot="1928667997"></ins>
			<script>
				 (adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		
		</main><!-- #main -->
	</section><!-- #primary -->
	
	<?php get_sidebar(); ?>
	
<?php get_footer(); ?>
