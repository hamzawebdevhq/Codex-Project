<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$admin_email = get_option( 'admin_email' );
$contact_url = home_url( '/contact/' );
$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$cart_url    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
?>
	</main>

	<footer class="site-footer" id="colophon">
		<div class="site-container site-footer__grid">
			<div>
				<h3><?php esc_html_e( 'About', 'spark-vibe-store' ); ?></h3>
				<p><?php esc_html_e( 'Spark Vibe Store curates statement-making artificial jewelry with a luxury look, elegant finish, and gifting-ready presentation.', 'spark-vibe-store' ); ?></p>
			</div>

			<div>
				<h3><?php esc_html_e( 'Links', 'spark-vibe-store' ); ?></h3>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'spark-vibe-store' ); ?></a></li>
					<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop', 'spark-vibe-store' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'spark-vibe-store' ); ?></a></li>
					<li><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact', 'spark-vibe-store' ); ?></a></li>
				</ul>
			</div>

			<div>
				<h3><?php esc_html_e( 'Support', 'spark-vibe-store' ); ?></h3>
				<ul>
					<li><a href="<?php echo esc_url( $cart_url ); ?>"><?php esc_html_e( 'Cart', 'spark-vibe-store' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/checkout/' ) ); ?>"><?php esc_html_e( 'Checkout', 'spark-vibe-store' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>"><?php esc_html_e( 'My Account', 'spark-vibe-store' ); ?></a></li>
					<li><a href="mailto:<?php echo esc_attr( $admin_email ); ?>"><?php echo esc_html( $admin_email ); ?></a></li>
				</ul>
			</div>

			<div>
				<h3><?php esc_html_e( 'Social', 'spark-vibe-store' ); ?></h3>
				<ul>
					<li><a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Instagram', 'spark-vibe-store' ); ?></a></li>
					<li><a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Facebook', 'spark-vibe-store' ); ?></a></li>
					<li><a href="https://www.pinterest.com/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Pinterest', 'spark-vibe-store' ); ?></a></li>
					<li><a href="https://wa.me/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WhatsApp', 'spark-vibe-store' ); ?></a></li>
				</ul>
			</div>
		</div>

		<div class="site-container site-footer__bottom">
			<p><?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.</p>
			<p><?php esc_html_e( 'Designed for elegant gifting, everyday glamour, and smooth checkout on every screen.', 'spark-vibe-store' ); ?></p>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>

