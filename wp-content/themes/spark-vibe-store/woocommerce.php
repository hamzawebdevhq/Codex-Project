<?php
get_header();
?>

<?php if ( ! is_product() ) : ?>
	<section class="page-banner page-banner--shop">
		<div class="site-container">
			<p class="eyebrow"><?php esc_html_e( 'Spark Vibe Store', 'spark-vibe-store' ); ?></p>
			<h1>
				<?php
				if ( is_shop() ) {
					esc_html_e( 'Shop Jewelry', 'spark-vibe-store' );
				} elseif ( is_product_category() ) {
					single_term_title();
				} elseif ( is_cart() ) {
					esc_html_e( 'Your Cart', 'spark-vibe-store' );
				} elseif ( is_checkout() ) {
					esc_html_e( 'Secure Checkout', 'spark-vibe-store' );
				} elseif ( is_account_page() ) {
					esc_html_e( 'My Account', 'spark-vibe-store' );
				} else {
					woocommerce_page_title();
				}
				?>
			</h1>
		</div>
	</section>
<?php endif; ?>

<div class="site-container woocommerce-shell">
	<?php if ( function_exists( 'woocommerce_breadcrumb' ) && ! is_cart() && ! is_checkout() ) : ?>
		<?php woocommerce_breadcrumb(); ?>
	<?php endif; ?>

	<?php woocommerce_content(); ?>
</div>

<?php
get_footer();

