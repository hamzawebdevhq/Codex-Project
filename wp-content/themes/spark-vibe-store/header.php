<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#primary"><?php esc_html_e( 'Skip to content', 'spark-vibe-store' ); ?></a>
<div class="site-shell">
	<div class="announcement-bar">
		<div class="site-container announcement-bar__inner">
			<p><?php esc_html_e( 'Spark Vibe Store | Luxury artificial jewelry crafted for everyday sparkle', 'spark-vibe-store' ); ?></p>
			<p><?php esc_html_e( 'Free shipping available | Guest checkout enabled', 'spark-vibe-store' ); ?></p>
		</div>
	</div>

	<header class="site-header" id="masthead">
		<div class="site-container site-header__inner">
			<div class="site-branding">
				<?php if ( has_custom_logo() ) : ?>
					<div class="site-logo"><?php the_custom_logo(); ?></div>
				<?php endif; ?>

				<div class="site-branding__text">
					<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php bloginfo( 'name' ); ?>
					</a>
					<p class="site-tagline"><?php bloginfo( 'description' ); ?></p>
				</div>
			</div>

			<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">
				<span></span>
				<span></span>
				<span></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'spark-vibe-store' ); ?></span>
			</button>

			<nav class="primary-navigation" id="site-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'spark-vibe-store' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'menu',
						'fallback_cb'    => 'spark_vibe_store_nav_fallback',
					)
				);
				?>

				<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
					<div class="primary-navigation__mobile-actions">
						<a class="header-action" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
							<span><?php esc_html_e( 'Account', 'spark-vibe-store' ); ?></span>
						</a>
						<a class="header-action header-action--cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
							<span><?php esc_html_e( 'Cart', 'spark-vibe-store' ); ?></span>
							<em><?php echo esc_html( spark_vibe_store_get_cart_count() ); ?></em>
						</a>
					</div>
				<?php endif; ?>
			</nav>

			<div class="header-actions">
				<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
					<a class="header-action" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
						<span><?php esc_html_e( 'Account', 'spark-vibe-store' ); ?></span>
					</a>
					<a class="header-action header-action--cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
						<span><?php esc_html_e( 'Cart', 'spark-vibe-store' ); ?></span>
						<em><?php echo esc_html( spark_vibe_store_get_cart_count() ); ?></em>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<main id="primary" class="site-main">
