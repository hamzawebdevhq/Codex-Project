<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/notifications.php';

function spark_vibe_store_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'elementor' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'spark-vibe-store' ),
			'footer'  => __( 'Footer Menu', 'spark-vibe-store' ),
		)
	);

	add_image_size( 'spark-vibe-card', 900, 1125, true );
}
add_action( 'after_setup_theme', 'spark_vibe_store_setup' );

function spark_vibe_store_content_width() {
	$GLOBALS['content_width'] = 1200;
}
add_action( 'after_setup_theme', 'spark_vibe_store_content_width', 0 );

function spark_vibe_store_fonts_url() {
	$families = array(
		'Cormorant Garamond:wght@500;600;700',
		'Manrope:wght@400;500;600;700;800',
	);

	return add_query_arg(
		array(
			'family'  => rawurlencode( implode( '&family=', $families ) ),
			'display' => 'swap',
		),
		'https://fonts.googleapis.com/css2'
	);
}

function spark_vibe_store_enqueue_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );
	$site_css_path = get_template_directory() . '/assets/css/site.css';
	$site_js_path  = get_template_directory() . '/assets/js/theme.js';

	wp_enqueue_style( 'spark-vibe-store-fonts', spark_vibe_store_fonts_url(), array(), null );
	wp_enqueue_style( 'spark-vibe-store-style', get_stylesheet_uri(), array(), $theme_version );

	if ( file_exists( $site_css_path ) ) {
		wp_enqueue_style(
			'spark-vibe-store-site',
			get_template_directory_uri() . '/assets/css/site.css',
			array( 'spark-vibe-store-style' ),
			filemtime( $site_css_path )
		);
	}

	if ( file_exists( $site_js_path ) ) {
		wp_enqueue_script(
			'spark-vibe-store-theme',
			get_template_directory_uri() . '/assets/js/theme.js',
			array(),
			filemtime( $site_js_path ),
			true
		);

		wp_localize_script(
			'spark-vibe-store-theme',
			'sparkVibeStore',
			array(
				'isAdminBarShowing' => is_admin_bar_showing(),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'spark_vibe_store_enqueue_assets' );

add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

function spark_vibe_store_should_load_contact_assets() {
	if ( is_front_page() || is_page( 'contact' ) ) {
		return true;
	}

	$post = get_post();

	return $post instanceof WP_Post && has_shortcode( $post->post_content, 'contact-form-7' );
}

function spark_vibe_store_optimize_assets() {
	foreach ( array( 'wc-blocks-style', 'wc-blocks-vendors-style', 'wc-blocks-packages-style' ) as $style_handle ) {
		wp_dequeue_style( $style_handle );
		wp_deregister_style( $style_handle );
	}

	if ( ! spark_vibe_store_should_load_contact_assets() ) {
		wp_dequeue_style( 'contact-form-7' );
		wp_dequeue_script( 'swv' );
		wp_dequeue_script( 'contact-form-7' );
	}
}
add_action( 'wp_enqueue_scripts', 'spark_vibe_store_optimize_assets', 100 );
add_action( 'wp_print_styles', 'spark_vibe_store_optimize_assets', 100 );

function spark_vibe_store_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.googleapis.com',
			'crossorigin' => '',
		);
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => '',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'spark_vibe_store_resource_hints', 10, 2 );

function spark_vibe_store_register_elementor_locations( $elementor_theme_manager ) {
	if ( method_exists( $elementor_theme_manager, 'register_all_core_location' ) ) {
		$elementor_theme_manager->register_all_core_location();
	}
}
add_action( 'elementor/theme/register_locations', 'spark_vibe_store_register_elementor_locations' );

function spark_vibe_store_body_classes( $classes ) {
	$classes[] = 'spark-vibe-theme';

	if ( is_front_page() ) {
		$classes[] = 'spark-vibe-front-page';
	}

	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		$classes[] = 'spark-vibe-commerce';
	}

	return $classes;
}
add_filter( 'body_class', 'spark_vibe_store_body_classes' );

function spark_vibe_store_image_attributes( $attr ) {
	static $front_page_image_boosted = false;

	$attr['decoding'] = 'async';

	if ( is_front_page() && ! $front_page_image_boosted ) {
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';
		$front_page_image_boosted = true;
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'spark_vibe_store_image_attributes' );

function spark_vibe_store_nav_fallback() {
	echo '<ul class="menu">';
	wp_list_pages(
		array(
			'title_li'    => '',
			'depth'       => 1,
			'sort_column' => 'menu_order,post_title',
		)
	);
	echo '</ul>';
}

function spark_vibe_store_get_cart_count() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	return (int) WC()->cart->get_cart_contents_count();
}

function spark_vibe_store_get_cart_total() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}

	return wp_strip_all_tags( WC()->cart->get_cart_subtotal() );
}

function spark_vibe_store_loop_shop_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'spark_vibe_store_loop_shop_per_page', 20 );

function spark_vibe_store_currency_symbol( $currency_symbol, $currency ) {
	if ( 'PKR' === $currency ) {
		return 'Rs.';
	}

	return $currency_symbol;
}
add_filter( 'woocommerce_currency_symbol', 'spark_vibe_store_currency_symbol', 10, 2 );

function spark_vibe_store_price_decimals( $decimals ) {
	if ( function_exists( 'get_woocommerce_currency' ) && 'PKR' === get_woocommerce_currency() ) {
		return 0;
	}

	return $decimals;
}
add_filter( 'wc_get_price_decimals', 'spark_vibe_store_price_decimals' );

function spark_vibe_store_checkout_fields( $fields ) {
	$placeholder_map = array(
		'billing'  => array(
			'billing_first_name' => 'First name',
			'billing_last_name'  => 'Last name',
			'billing_address_1'  => 'House number and street name',
			'billing_address_2'  => 'Apartment, suite, unit, etc. (optional)',
			'billing_city'       => 'Karachi',
			'billing_postcode'   => '74000',
			'billing_phone'      => '0300 1234567',
			'billing_email'      => 'name@example.com',
		),
		'shipping' => array(
			'shipping_first_name' => 'First name',
			'shipping_last_name'  => 'Last name',
			'shipping_address_1'  => 'House number and street name',
			'shipping_address_2'  => 'Apartment, suite, unit, etc. (optional)',
			'shipping_city'       => 'Karachi',
			'shipping_postcode'   => '74000',
		),
	);

	foreach ( $placeholder_map as $section => $field_placeholders ) {
		if ( empty( $fields[ $section ] ) ) {
			continue;
		}

		foreach ( $field_placeholders as $field_key => $placeholder ) {
			if ( isset( $fields[ $section ][ $field_key ] ) ) {
				$fields[ $section ][ $field_key ]['placeholder'] = $placeholder;
			}
		}
	}

	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = __( 'Delivery notes', 'spark-vibe-store' );
		$fields['order']['order_comments']['placeholder'] = __( 'Add landmark, delivery timing, or gift instructions (optional).', 'spark-vibe-store' );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'spark_vibe_store_checkout_fields' );

function spark_vibe_store_order_button_text( $text ) {
	if ( is_checkout() ) {
		return __( 'Place Secure Order', 'spark-vibe-store' );
	}

	return $text;
}
add_filter( 'woocommerce_order_button_text', 'spark_vibe_store_order_button_text' );

function spark_vibe_store_checkout_summary_note() {
	if ( ! is_checkout() || is_wc_endpoint_url() ) {
		return;
	}
	?>
	<div class="sv-checkout-summary-note">
		<p class="sv-eyebrow"><?php esc_html_e( 'Checkout notes', 'spark-vibe-store' ); ?></p>
		<h3><?php esc_html_e( 'Everything here is ready for a smooth order flow.', 'spark-vibe-store' ); ?></h3>
		<ul class="sv-bullets">
			<li><?php esc_html_e( 'All totals are displayed in PKR for local shopping clarity.', 'spark-vibe-store' ); ?></li>
			<li><?php esc_html_e( 'Choose free shipping or flat rate delivery before placing the order.', 'spark-vibe-store' ); ?></li>
			<li><?php esc_html_e( 'Pay by bank transfer or cash on delivery with customer and admin emails enabled.', 'spark-vibe-store' ); ?></li>
		</ul>
	</div>
	<?php
}
add_action( 'woocommerce_review_order_before_payment', 'spark_vibe_store_checkout_summary_note', 5 );

function spark_vibe_store_related_products_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;

	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'spark_vibe_store_related_products_args' );

function spark_vibe_store_cart_link_fragment( $fragments ) {
	ob_start();
	?>
	<em><?php echo esc_html( spark_vibe_store_get_cart_count() ); ?></em>
	<?php
	$fragments['.header-action--cart em'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'spark_vibe_store_cart_link_fragment' );

function spark_vibe_store_notification_option_settings( $settings ) {
	return array_merge(
		$settings,
		array(
			'sms_endpoint'      => (string) get_option( 'spark_vibe_sms_endpoint', '' ),
			'sms_token'         => (string) get_option( 'spark_vibe_sms_token', '' ),
			'whatsapp_endpoint' => (string) get_option( 'spark_vibe_whatsapp_endpoint', '' ),
			'whatsapp_token'    => (string) get_option( 'spark_vibe_whatsapp_token', '' ),
		)
	);
}
add_filter( 'spark_vibe_store_notification_settings', 'spark_vibe_store_notification_option_settings' );

function spark_vibe_store_enqueue_comment_reply() {
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'spark_vibe_store_enqueue_comment_reply' );

function spark_vibe_store_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'spark_vibe_store_pingback_header' );

function spark_vibe_store_cleanup_wp_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'spark_vibe_store_cleanup_wp_head' );
