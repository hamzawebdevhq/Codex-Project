<?php
get_header();
?>

<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<?php $is_elementor = (bool) get_post_meta( get_the_ID(), '_elementor_edit_mode', true ); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if ( ! $is_elementor ) : ?>
				<section class="page-banner">
					<div class="site-container">
						<p class="eyebrow"><?php esc_html_e( 'Spark Vibe Store', 'spark-vibe-store' ); ?></p>
						<h1><?php the_title(); ?></h1>
					</div>
				</section>
			<?php endif; ?>

			<div class="<?php echo $is_elementor ? 'page-content page-content--builder' : 'page-content'; ?>">
				<?php if ( ! $is_elementor ) : ?>
					<div class="site-container prose">
						<?php the_content(); ?>
					</div>
				<?php else : ?>
					<?php the_content(); ?>
				<?php endif; ?>
			</div>
		</article>
	<?php endwhile; ?>
<?php endif; ?>

<?php
get_footer();

