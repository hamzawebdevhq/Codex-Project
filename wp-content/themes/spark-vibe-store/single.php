<?php
get_header();
?>

<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-entry' ); ?>>
			<section class="page-banner">
				<div class="site-container">
					<p class="eyebrow"><?php echo esc_html( get_the_date() ); ?></p>
					<h1><?php the_title(); ?></h1>
				</div>
			</section>

			<div class="site-container prose">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="single-entry__media">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>

				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
<?php endif; ?>

<?php
get_footer();

