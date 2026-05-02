<?php
get_header();
?>

<div class="site-container content-shell">
	<?php if ( have_posts() ) : ?>
		<div class="content-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'content-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="content-card__image" href="<?php the_permalink(); ?>">
							<?php the_post_thumbnail( 'large' ); ?>
						</a>
					<?php endif; ?>
					<div class="content-card__body">
						<p class="content-card__meta"><?php echo esc_html( get_the_date() ); ?></p>
						<h2 class="content-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="content-card__excerpt"><?php the_excerpt(); ?></div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<div class="content-card content-card--empty">
			<h1><?php esc_html_e( 'Nothing to display yet.', 'spark-vibe-store' ); ?></h1>
			<p><?php esc_html_e( 'Add posts or products to start telling your brand story.', 'spark-vibe-store' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php
get_footer();

