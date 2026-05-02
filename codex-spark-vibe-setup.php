<?php
/**
 * One-shot WordPress bootstrap for the Spark Vibe Store demo storefront.
 */

declare(strict_types=1);

define( 'WP_USE_THEMES', false );

require __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/theme.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

set_time_limit( 0 );

function sv_log( string $message ): void {
	echo $message . PHP_EOL;
}

function sv_seed_post_id( string $post_type, string $seed_key ): int {
	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => array( 'publish', 'draft', 'private', 'pending', 'trash' ),
			'posts_per_page' => 1,
			'meta_key'       => '_sv_seed_key',
			'meta_value'     => $seed_key,
			'fields'         => 'ids',
		)
	);

	return $posts ? (int) $posts[0] : 0;
}

function sv_upsert_page( string $seed_key, string $title, string $slug, string $content = '' ): int {
	$page_id = sv_seed_post_id( 'page', $seed_key );

	if ( ! $page_id ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		$page_id  = $existing ? (int) $existing->ID : 0;
	}

	$page_args = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_content' => $content,
	);

	if ( $page_id ) {
		$page_args['ID'] = $page_id;
		wp_update_post( $page_args );
	} else {
		$page_id = wp_insert_post( $page_args );
	}

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		throw new RuntimeException( 'Unable to create page: ' . $title );
	}

	update_post_meta( $page_id, '_sv_seed_key', $seed_key );

	return (int) $page_id;
}

function sv_seed_form_id( string $seed_key ): int {
	$posts = get_posts(
		array(
			'post_type'      => 'wpcf7_contact_form',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 1,
			'meta_key'       => '_sv_seed_key',
			'meta_value'     => $seed_key,
			'fields'         => 'ids',
		)
	);

	return $posts ? (int) $posts[0] : 0;
}

function sv_default_cf7_messages(): array {
	$default_form = get_posts(
		array(
			'post_type'      => 'wpcf7_contact_form',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( $default_form ) {
		$messages = get_post_meta( (int) $default_form[0], '_messages', true );
		if ( is_array( $messages ) && $messages ) {
			return $messages;
		}
	}

	return array(
		'mail_sent_ok'                => 'Thank you for your message. It has been sent.',
		'mail_sent_ng'                => 'There was an error trying to send your message. Please try again later.',
		'validation_error'            => 'One or more fields have an error. Please check and try again.',
		'spam'                        => 'There was an error trying to send your message. Please try again later.',
		'accept_terms'                => 'You must accept the terms and conditions before sending your message.',
		'invalid_required'            => 'The field is required.',
		'invalid_too_long'            => 'The field is too long.',
		'invalid_too_short'           => 'The field is too short.',
		'upload_failed'               => 'There was an unknown error uploading the file.',
		'upload_file_type_invalid'    => 'You are not allowed to upload files of this type.',
		'upload_file_too_large'       => 'The file is too big.',
		'quiz_answer_not_correct'     => 'The answer to the quiz is incorrect.',
		'captcha_not_match'           => 'Your entered code is incorrect.',
		'unaccepted'                  => 'You must accept the terms and conditions before sending your message.',
	);
}

function sv_upsert_cf7_form( string $seed_key, string $title, string $slug, string $form_markup, array $mail, array $mail_2 = array() ): int {
	$form_id = sv_seed_form_id( $seed_key );

	if ( ! $form_id ) {
		$existing = get_posts(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 1,
				'name'           => $slug,
				'fields'         => 'ids',
			)
		);
		$form_id = $existing ? (int) $existing[0] : 0;
	}

	$args = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_type'    => 'wpcf7_contact_form',
		'post_status'  => 'publish',
		'post_content' => $form_markup,
	);

	if ( $form_id ) {
		$args['ID'] = $form_id;
		wp_update_post( $args );
	} else {
		$form_id = wp_insert_post( $args );
	}

	if ( is_wp_error( $form_id ) || ! $form_id ) {
		throw new RuntimeException( 'Unable to create contact form: ' . $title );
	}

	update_post_meta( $form_id, '_sv_seed_key', $seed_key );
	update_post_meta( $form_id, '_locale', 'en_US' );
	update_post_meta( $form_id, '_form', $form_markup );
	update_post_meta( $form_id, '_mail', $mail );
	update_post_meta( $form_id, '_mail_2', $mail_2 + array( 'active' => false ) );
	update_post_meta( $form_id, '_messages', sv_default_cf7_messages() );
	update_post_meta( $form_id, '_additional_settings', '' );

	return (int) $form_id;
}

function sv_term_id( string $taxonomy, string $slug, string $name, array $args = array() ): int {
	$term = get_term_by( 'slug', $slug, $taxonomy );

	if ( $term instanceof WP_Term ) {
		wp_update_term(
			$term->term_id,
			$taxonomy,
			array(
				'name'        => $name,
				'description' => $args['description'] ?? '',
			)
		);

		return (int) $term->term_id;
	}

	$result = wp_insert_term(
		$name,
		$taxonomy,
		array_merge(
			$args,
			array(
				'slug' => $slug,
			)
		)
	);

	if ( is_wp_error( $result ) ) {
		throw new RuntimeException( 'Unable to create term: ' . $name );
	}

	return (int) $result['term_id'];
}

function sv_shipping_zone_id( string $zone_name ): int {
	foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
		if ( isset( $zone['zone_name'] ) && $zone_name === $zone['zone_name'] ) {
			return (int) $zone['zone_id'];
		}
	}

	return 0;
}

function sv_ensure_shipping_zone( string $zone_name, array $locations ): int {
	$zone_id = sv_shipping_zone_id( $zone_name );
	$zone    = $zone_id ? new WC_Shipping_Zone( $zone_id ) : new WC_Shipping_Zone();

	$zone->set_zone_name( $zone_name );
	$zone->save();

	if ( 0 !== $zone->get_id() ) {
		$zone->clear_locations();
		foreach ( $locations as $location ) {
			$zone->add_location( $location['code'], $location['type'] );
		}
		$zone->save();
	}

	return (int) $zone->get_id();
}

function sv_ensure_shipping_method( int $zone_id, string $method_id, array $settings ): void {
	$zone    = new WC_Shipping_Zone( $zone_id );
	$methods = $zone->get_shipping_methods( false, 'admin' );

	foreach ( $methods as $method ) {
		if ( isset( $method->id ) && $method_id === $method->id ) {
			$option_name = sprintf( 'woocommerce_%s_%d_settings', $method_id, (int) $method->instance_id );
			update_option( $option_name, array_merge( (array) get_option( $option_name, array() ), $settings ) );
			return;
		}
	}

	$instance_id = $zone->add_shipping_method( $method_id );

	if ( $instance_id ) {
		update_option( sprintf( 'woocommerce_%s_%d_settings', $method_id, (int) $instance_id ), $settings );
	}
}

function sv_resolve_remote_image( string $source ): string {
	$host = wp_parse_url( $source, PHP_URL_HOST );

	if ( is_string( $host ) && false !== strpos( $host, 'images.pexels.com' ) ) {
		return $source;
	}

	if ( is_string( $host ) && false !== strpos( $host, 'pexels.com' ) ) {
		$path = (string) wp_parse_url( $source, PHP_URL_PATH );

		if ( preg_match( '#/photo/.+-(\d+)/?$#', $path, $matches ) ) {
			$photo_id = (int) $matches[1];

			if ( $photo_id > 0 ) {
				return sprintf(
					'https://images.pexels.com/photos/%1$d/pexels-photo-%1$d.jpeg?auto=compress&cs=tinysrgb&w=1600',
					$photo_id
				);
			}
		}
	}

	$response = wp_remote_get(
		$source,
		array(
			'timeout' => 25,
			'headers' => array(
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123 Safari/537.36',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return '';
	}

	$body = (string) wp_remote_retrieve_body( $response );
	if ( preg_match( '/<meta\s+property="og:image"\s+content="([^"]+)"/i', $body, $matches ) ) {
		return html_entity_decode( $matches[1], ENT_QUOTES );
	}

	if ( preg_match( '/<meta\s+name="twitter:image"\s+content="([^"]+)"/i', $body, $matches ) ) {
		return html_entity_decode( $matches[1], ENT_QUOTES );
	}

	return '';
}

function sv_attachment_from_source( string $source, string $title, int $parent_id = 0 ): int {
	$source_key = md5( $source . '|' . sanitize_title( $title ) );

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'meta_key'       => '_sv_source_key',
			'meta_value'     => $source_key,
			'fields'         => 'ids',
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$image_url = sv_resolve_remote_image( $source );
	if ( ! $image_url ) {
		return 0;
	}

	$tmp_file = download_url( $image_url, 30 );
	if ( is_wp_error( $tmp_file ) ) {
		return 0;
	}

	$extension = pathinfo( wp_parse_url( $image_url, PHP_URL_PATH ) ?: 'image.jpg', PATHINFO_EXTENSION );
	$extension = $extension ? $extension : 'jpg';
	$filename  = sanitize_title( $title ) . '.' . $extension;

	$file_array = array(
		'name'     => $filename,
		'tmp_name' => $tmp_file,
	);

	$attachment_id = media_handle_sideload( $file_array, $parent_id, $title );

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp_file );
		return 0;
	}

	update_post_meta( (int) $attachment_id, '_sv_source_url', $source );
	update_post_meta( (int) $attachment_id, '_sv_source_key', $source_key );
	update_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', $title );

	return (int) $attachment_id;
}

function sv_element_id(): string {
	return substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 8 );
}

function sv_elementor_widget( string $type, array $settings ): array {
	return array(
		'id'         => sv_element_id(),
		'elType'     => 'widget',
		'widgetType' => $type,
		'settings'   => $settings,
		'elements'   => array(),
	);
}

function sv_elementor_section( array $widgets ): array {
	return array(
		'id'       => sv_element_id(),
		'elType'   => 'section',
		'settings' => array(
			'layout'        => 'full_width',
			'content_width' => 'boxed',
			'gap'           => 'extended',
		),
		'elements' => array(
			array(
				'id'       => sv_element_id(),
				'elType'   => 'column',
				'settings' => array(
					'_column_size' => 100,
				),
				'elements' => $widgets,
				'isInner'  => false,
			),
		),
		'isInner'  => false,
	);
}

function sv_set_elementor_content( int $post_id, array $sections ): void {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}

	$elementor_version = defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '4.0.0';

	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => '',
		)
	);

	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $sections ) ) );
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_elementor_version', $elementor_version );
	update_post_meta( $post_id, '_elementor_page_settings', array() );
}

function sv_menu_id( string $menu_name ): int {
	$menu = wp_get_nav_menu_object( $menu_name );
	if ( $menu ) {
		return (int) $menu->term_id;
	}

	return (int) wp_create_nav_menu( $menu_name );
}

function sv_sync_menu( int $menu_id, array $items ): void {
	$existing_items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

	if ( $existing_items ) {
		foreach ( $existing_items as $item ) {
			wp_delete_post( (int) $item->ID, true );
		}
	}

	$order = 1;
	foreach ( $items as $item ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $item['title'],
				'menu-item-object-id' => $item['object_id'],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $order,
			)
		);
		$order++;
	}
}

function sv_product_description( string $name, string $category, string $tone ): array {
	$description = sprintf(
		'<p>%1$s brings a polished luxury finish to everyday styling with a light feel, refined detailing, and a radiant %2$s-inspired look.</p><p>Designed for gifting and repeat wear, this piece pairs beautifully with soft glam, festive dressing, and elevated daily outfits.</p>',
		esc_html( $name ),
		esc_html( $tone )
	);

	$short = sprintf(
		'%s with a premium artificial jewelry finish, elegant shine, and versatile styling.',
		ucfirst( $category )
	);

	return array(
		'description'       => $description,
		'short_description' => $short,
	);
}

function sv_upsert_product( array $product_data, int $category_id ): int {
	$product_id = wc_get_product_id_by_sku( $product_data['sku'] );

	if ( $product_id ) {
		wp_update_post(
			array(
				'ID'           => $product_id,
				'post_title'   => $product_data['name'],
				'post_name'    => $product_data['slug'],
				'post_status'  => 'publish',
				'post_type'    => 'product',
				'post_content' => $product_data['description'],
				'post_excerpt' => $product_data['short_description'],
			)
		);
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			$product = new WC_Product_Simple( $product_id );
		}
	} else {
		$product_id = wp_insert_post(
			array(
				'post_title'   => $product_data['name'],
				'post_name'    => $product_data['slug'],
				'post_status'  => 'publish',
				'post_type'    => 'product',
				'post_content' => $product_data['description'],
				'post_excerpt' => $product_data['short_description'],
			)
		);

		if ( is_wp_error( $product_id ) || ! $product_id ) {
			throw new RuntimeException( 'Unable to create product: ' . $product_data['name'] );
		}

		$product = new WC_Product_Simple( $product_id );
	}

	$product->set_name( $product_data['name'] );
	$product->set_slug( $product_data['slug'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_regular_price( (string) $product_data['price'] );
	$product->set_price( (string) $product_data['price'] );
	$product->set_sku( $product_data['sku'] );
	$product->set_manage_stock( false );
	$product->set_stock_status( 'instock' );
	$product->set_featured( ! empty( $product_data['featured'] ) );
	$product->set_reviews_allowed( true );
	$product->set_short_description( $product_data['short_description'] );
	$product->set_description( $product_data['description'] );
	$product->save();

	wp_set_object_terms( $product_id, array( $category_id ), 'product_cat', false );
	update_post_meta( $product_id, 'total_sales', (string) $product_data['sales'] );

	$image_id = sv_attachment_from_source( $product_data['image'], $product_data['name'], $product_id );
	if ( $image_id ) {
		set_post_thumbnail( $product_id, $image_id );
	}

	return (int) $product_id;
}

$administrators = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
		'fields' => array( 'ID' ),
	)
);

if ( $administrators ) {
	wp_set_current_user( (int) $administrators[0]->ID );
}

sv_log( 'Activating required plugins and theme...' );

activate_plugin( 'woocommerce/woocommerce.php', '', false, true );
activate_plugin( 'elementor/elementor.php', '', false, true );
activate_plugin( 'contact-form-7/wp-contact-form-7.php', '', false, true );

switch_theme( 'spark-vibe-store' );

update_option( 'blogname', 'Spark Vibe Store' );
update_option( 'blogdescription', 'Luxury Artificial Jewelry Store' );
update_option( 'timezone_string', 'Asia/Karachi' );
update_option( 'permalink_structure', '/%postname%/' );

$wp_rewrite->set_permalink_structure( '/%postname%/' );

update_option( 'woocommerce_currency', 'PKR' );
update_option( 'woocommerce_currency_pos', 'left_space' );
update_option( 'woocommerce_price_num_decimals', '0' );
update_option( 'woocommerce_price_thousand_sep', ',' );
update_option( 'woocommerce_price_decimal_sep', '.' );
update_option( 'woocommerce_default_country', 'PK' );
update_option( 'woocommerce_default_customer_address', 'base' );
update_option( 'woocommerce_allowed_countries', 'all' );
update_option( 'woocommerce_coming_soon', 'no' );
update_option( 'woocommerce_store_pages_only', 'no' );
update_option( 'woocommerce_ship_to_countries', '' );
update_option( 'woocommerce_ship_to_destination', 'billing' );
update_option( 'woocommerce_enable_guest_checkout', 'yes' );
update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'no' );
update_option( 'woocommerce_calc_shipping', 'yes' );
update_option( 'woocommerce_shipping_hide_rates_when_free', 'no' );
update_option( 'spark_vibe_sms_endpoint', '' );
update_option( 'spark_vibe_sms_token', '' );
update_option( 'spark_vibe_whatsapp_endpoint', '' );
update_option( 'spark_vibe_whatsapp_token', '' );

$new_order_settings = (array) get_option( 'woocommerce_new_order_settings', array() );
$new_order_settings['enabled']   = 'yes';
$new_order_settings['recipient'] = get_option( 'admin_email' );
update_option( 'woocommerce_new_order_settings', $new_order_settings );

$processing_settings = (array) get_option( 'woocommerce_customer_processing_order_settings', array() );
$processing_settings['enabled'] = 'yes';
update_option( 'woocommerce_customer_processing_order_settings', $processing_settings );

$on_hold_settings = (array) get_option( 'woocommerce_customer_on_hold_order_settings', array() );
$on_hold_settings['enabled'] = 'yes';
update_option( 'woocommerce_customer_on_hold_order_settings', $on_hold_settings );

$cod_settings = (array) get_option( 'woocommerce_cod_settings', array() );
$cod_settings['enabled']            = 'yes';
$cod_settings['title']              = 'Cash on Delivery (PKR)';
$cod_settings['instructions']       = 'Pay in cash in PKR when your Spark Vibe Store order arrives.';
$cod_settings['enable_for_methods'] = '';
update_option( 'woocommerce_cod_settings', $cod_settings );

$bacs_settings = (array) get_option( 'woocommerce_bacs_settings', array() );
$bacs_settings['enabled']      = 'yes';
$bacs_settings['title']        = 'Bank Transfer (PKR)';
$bacs_settings['instructions'] = 'Use your order number as the payment reference. Orders are prepared once your PKR payment is received.';
update_option( 'woocommerce_bacs_settings', $bacs_settings );

sv_log( 'Creating core pages...' );

$home_id      = sv_upsert_page( 'sv-home', 'Home', 'home' );
$shop_id      = sv_upsert_page( 'sv-shop', 'Shop', 'shop', '' );
$cart_id      = sv_upsert_page( 'sv-cart', 'Cart', 'cart', '[woocommerce_cart]' );
$checkout_id  = sv_upsert_page( 'sv-checkout', 'Checkout', 'checkout', '[woocommerce_checkout]' );
$account_id   = sv_upsert_page( 'sv-account', 'My Account', 'my-account', '[woocommerce_my_account]' );
$about_id     = sv_upsert_page( 'sv-about', 'About', 'about' );
$contact_id   = sv_upsert_page( 'sv-contact', 'Contact', 'contact' );

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', 0 );

update_option( 'woocommerce_shop_page_id', $shop_id );
update_option( 'woocommerce_cart_page_id', $cart_id );
update_option( 'woocommerce_checkout_page_id', $checkout_id );
update_option( 'woocommerce_myaccount_page_id', $account_id );

sv_log( 'Creating contact forms...' );

$admin_email = (string) get_option( 'admin_email' );

$contact_form_id = sv_upsert_cf7_form(
	'sv-contact-form',
	'Spark Vibe Contact Form',
	'spark-vibe-contact-form',
	'<label>Full name[text* your-name autocomplete:name]</label>
<label>Email address[email* your-email autocomplete:email]</label>
<label>Phone number[tel your-phone autocomplete:tel]</label>
<label>Subject[text* your-subject]</label>
<label>Your message[textarea* your-message]</label>
[submit "Send Message"]',
	array(
		'subject'            => 'New Spark Vibe inquiry: [your-subject]',
		'sender'             => sprintf( 'Spark Vibe Store <%s>', $admin_email ),
		'body'               => "Full name: [your-name]\nEmail: [your-email]\nPhone: [your-phone]\nSubject: [your-subject]\n\nMessage:\n[your-message]",
		'recipient'          => $admin_email,
		'additional_headers' => 'Reply-To: [your-email]',
		'attachments'        => '',
		'use_html'           => false,
		'exclude_blank'      => true,
	),
	array(
		'active'             => true,
		'subject'            => 'Spark Vibe Store received your message',
		'sender'             => sprintf( 'Spark Vibe Store <%s>', $admin_email ),
		'body'               => "Hi [your-name],\n\nThank you for contacting Spark Vibe Store. We received your message and will get back to you shortly.\n\nSubject: [your-subject]\n\nWarmly,\nSpark Vibe Store",
		'recipient'          => '[your-email]',
		'additional_headers' => '',
		'attachments'        => '',
		'use_html'           => false,
		'exclude_blank'      => true,
	)
);

$newsletter_form_id = sv_upsert_cf7_form(
	'sv-newsletter-form',
	'Spark Vibe Newsletter Form',
	'spark-vibe-newsletter-form',
	'<p class="sv-mini-note">Join our list for styling edits, new arrivals, and launch-day offers.</p>
<label>Email address[email* newsletter-email placeholder "Enter your email address"]</label>
[submit "Join the List"]',
	array(
		'subject'            => 'Spark Vibe newsletter signup',
		'sender'             => sprintf( 'Spark Vibe Store <%s>', $admin_email ),
		'body'               => "New newsletter signup:\n[newsletter-email]",
		'recipient'          => $admin_email,
		'additional_headers' => 'Reply-To: [newsletter-email]',
		'attachments'        => '',
		'use_html'           => false,
		'exclude_blank'      => true,
	)
);

sv_log( 'Creating product categories...' );

$categories = array(
	'rings' => array(
		'name'        => 'Rings',
		'description' => 'Statement rings with premium shine and a luxury-inspired finish.',
		'image'       => 'https://www.pexels.com/photo/crop-woman-demonstrating-earring-and-ring-2740658/',
	),
	'necklaces' => array(
		'name'        => 'Necklaces',
		'description' => 'Layered chains, pendants, and standout pieces for polished styling.',
		'image'       => 'https://www.pexels.com/photo/necklace-and-earrings-16082502/',
	),
	'earrings' => array(
		'name'        => 'Earrings',
		'description' => 'Elegant studs, drops, and hoops designed to brighten every look.',
		'image'       => 'https://www.pexels.com/photo/woman-wearing-silver-earrings-and-necklace-8028163/',
	),
	'bracelets' => array(
		'name'        => 'Bracelets',
		'description' => 'Layer-friendly bracelets and cuffs with refined sparkle.',
		'image'       => 'https://www.pexels.com/photo/necklace-and-bracelet-and-earrings-16210460/',
	),
);

$category_ids = array();
foreach ( $categories as $slug => $data ) {
	$category_id = sv_term_id(
		'product_cat',
		$slug,
		$data['name'],
		array(
			'description' => $data['description'],
		)
	);

	$category_ids[ $slug ] = $category_id;

	$image_id = sv_attachment_from_source( $data['image'], $data['name'] . ' Category', 0 );
	if ( $image_id ) {
		update_term_meta( $category_id, 'thumbnail_id', $image_id );
	}
}

sv_log( 'Creating shipping zones...' );

$pakistan_zone_id = sv_ensure_shipping_zone(
	'Pakistan',
	array(
		array(
			'code' => 'PK',
			'type' => 'country',
		),
	)
);

$global_zone_id = sv_ensure_shipping_zone( 'Worldwide', array() );

$free_shipping_settings = array(
	'title'    => 'Free Shipping',
	'tax_status' => 'taxable',
	'requires' => '',
	'min_amount' => '',
);

$flat_rate_settings = array(
	'title'      => 'Standard Delivery',
	'tax_status' => 'taxable',
	'cost'       => '350',
);

sv_ensure_shipping_method( $pakistan_zone_id, 'free_shipping', $free_shipping_settings );
sv_ensure_shipping_method( $pakistan_zone_id, 'flat_rate', $flat_rate_settings );
sv_ensure_shipping_method( $global_zone_id, 'free_shipping', $free_shipping_settings );
sv_ensure_shipping_method( $global_zone_id, 'flat_rate', $flat_rate_settings );

sv_log( 'Creating media assets and products...' );

$home_visual_id  = sv_attachment_from_source( 'https://www.pexels.com/photo/portrait-photo-of-woman-with-rings-earring-and-necklace-3793817/', 'Spark Vibe Hero', $home_id );
$about_visual_id = sv_attachment_from_source( 'https://www.pexels.com/photo/a-woman-wearing-necklace-and-earrings-12725977/', 'Spark Vibe About', $about_id );

$home_visual_url  = $home_visual_id ? wp_get_attachment_image_url( $home_visual_id, 'full' ) : '';
$about_visual_url = $about_visual_id ? wp_get_attachment_image_url( $about_visual_id, 'full' ) : '';

function sv_convert_demo_price_to_pkr( int $base_price ): int {
	return (int) ( round( ( $base_price * 285 ) / 50 ) * 50 );
}

$products = array(
	array( 'sku' => 'SVS-R-001', 'slug' => 'aurora-halo-ring', 'name' => 'Aurora Halo Ring', 'category' => 'rings', 'price' => 48, 'sales' => 61, 'featured' => true, 'image' => 'https://www.pexels.com/photo/crop-woman-demonstrating-earring-and-ring-2740658/', 'tone' => 'glow-forward' ),
	array( 'sku' => 'SVS-R-002', 'slug' => 'celeste-pearl-ring', 'name' => 'Celeste Pearl Ring', 'category' => 'rings', 'price' => 42, 'sales' => 39, 'featured' => false, 'image' => 'https://www.pexels.com/photo/woman-wearing-rings-and-earrings-886603/', 'tone' => 'pearl-lit' ),
	array( 'sku' => 'SVS-R-003', 'slug' => 'luxe-knot-ring', 'name' => 'Luxe Knot Ring', 'category' => 'rings', 'price' => 45, 'sales' => 52, 'featured' => false, 'image' => 'https://www.pexels.com/photo/a-woman-wearing-a-gold-necklace-and-a-gold-ring-16346890/', 'tone' => 'modern-luxe' ),
	array( 'sku' => 'SVS-R-004', 'slug' => 'rosette-crystal-ring', 'name' => 'Rosette Crystal Ring', 'category' => 'rings', 'price' => 52, 'sales' => 47, 'featured' => true, 'image' => 'https://www.pexels.com/photo/crop-woman-demonstrating-earring-and-ring-2740658/', 'tone' => 'event-ready' ),
	array( 'sku' => 'SVS-R-005', 'slug' => 'moonlit-duo-ring', 'name' => 'Moonlit Duo Ring', 'category' => 'rings', 'price' => 44, 'sales' => 36, 'featured' => false, 'image' => 'https://www.pexels.com/photo/woman-wearing-rings-and-earrings-886603/', 'tone' => 'soft-gold' ),
	array( 'sku' => 'SVS-R-006', 'slug' => 'serene-bloom-ring', 'name' => 'Serene Bloom Ring', 'category' => 'rings', 'price' => 49, 'sales' => 58, 'featured' => false, 'image' => 'https://www.pexels.com/photo/a-woman-wearing-a-gold-necklace-and-a-gold-ring-16346890/', 'tone' => 'floral-sparkle' ),
	array( 'sku' => 'SVS-N-001', 'slug' => 'vienna-charm-necklace', 'name' => 'Vienna Charm Necklace', 'category' => 'necklaces', 'price' => 72, 'sales' => 55, 'featured' => true, 'image' => 'https://www.pexels.com/photo/necklace-and-earrings-16082502/', 'tone' => 'runway-inspired' ),
	array( 'sku' => 'SVS-N-002', 'slug' => 'golden-reverie-pendant', 'name' => 'Golden Reverie Pendant', 'category' => 'necklaces', 'price' => 68, 'sales' => 41, 'featured' => false, 'image' => 'https://www.pexels.com/photo/a-woman-wearing-a-gold-necklace-and-earrings-27954777/', 'tone' => 'gilded' ),
	array( 'sku' => 'SVS-N-003', 'slug' => 'opal-whisper-necklace', 'name' => 'Opal Whisper Necklace', 'category' => 'necklaces', 'price' => 74, 'sales' => 64, 'featured' => false, 'image' => 'https://www.pexels.com/photo/young-brunette-woman-wearing-a-chain-link-necklace-and-bracelet-17505427/', 'tone' => 'luminous' ),
	array( 'sku' => 'SVS-N-004', 'slug' => 'imperial-layered-necklace', 'name' => 'Imperial Layered Necklace', 'category' => 'necklaces', 'price' => 79, 'sales' => 62, 'featured' => true, 'image' => 'https://www.pexels.com/photo/portrait-photo-of-woman-with-rings-earring-and-necklace-3793817/', 'tone' => 'stacked-glam' ),
	array( 'sku' => 'SVS-N-005', 'slug' => 'pearl-orbit-necklace', 'name' => 'Pearl Orbit Necklace', 'category' => 'necklaces', 'price' => 76, 'sales' => 43, 'featured' => false, 'image' => 'https://www.pexels.com/photo/a-woman-wearing-necklace-and-earrings-12725977/', 'tone' => 'polished' ),
	array( 'sku' => 'SVS-N-006', 'slug' => 'nightfall-luxe-collar', 'name' => 'Nightfall Luxe Collar', 'category' => 'necklaces', 'price' => 89, 'sales' => 69, 'featured' => false, 'image' => 'https://www.pexels.com/photo/happy-brunette-woman-with-necklace-19218663/', 'tone' => 'dramatic' ),
	array( 'sku' => 'SVS-E-001', 'slug' => 'starlit-drop-earrings', 'name' => 'Starlit Drop Earrings', 'category' => 'earrings', 'price' => 38, 'sales' => 63, 'featured' => true, 'image' => 'https://www.pexels.com/photo/woman-wearing-silver-earrings-and-necklace-8028163/', 'tone' => 'light-catching' ),
	array( 'sku' => 'SVS-E-002', 'slug' => 'velvet-glow-hoops', 'name' => 'Velvet Glow Hoops', 'category' => 'earrings', 'price' => 36, 'sales' => 40, 'featured' => false, 'image' => 'https://www.pexels.com/photo/woman-wearing-rings-and-earrings-886603/', 'tone' => 'soft-shine' ),
	array( 'sku' => 'SVS-E-003', 'slug' => 'noor-cascade-earrings', 'name' => 'Noor Cascade Earrings', 'category' => 'earrings', 'price' => 41, 'sales' => 54, 'featured' => false, 'image' => 'https://www.pexels.com/photo/a-woman-wearing-a-gold-necklace-and-earrings-27954777/', 'tone' => 'festive' ),
	array( 'sku' => 'SVS-E-004', 'slug' => 'rose-gleam-studs', 'name' => 'Rose Gleam Studs', 'category' => 'earrings', 'price' => 32, 'sales' => 31, 'featured' => false, 'image' => 'https://www.pexels.com/photo/woman-wearing-silver-earrings-and-necklace-8028163/', 'tone' => 'refined' ),
	array( 'sku' => 'SVS-E-005', 'slug' => 'aurora-chandelier-earrings', 'name' => 'Aurora Chandelier Earrings', 'category' => 'earrings', 'price' => 46, 'sales' => 67, 'featured' => true, 'image' => 'https://www.pexels.com/photo/portrait-photo-of-woman-with-rings-earring-and-necklace-3793817/', 'tone' => 'occasion-ready' ),
	array( 'sku' => 'SVS-E-006', 'slug' => 'crystal-dew-earrings', 'name' => 'Crystal Dew Earrings', 'category' => 'earrings', 'price' => 39, 'sales' => 44, 'featured' => false, 'image' => 'https://www.pexels.com/photo/a-woman-wearing-necklace-and-earrings-12725977/', 'tone' => 'fresh-glam' ),
	array( 'sku' => 'SVS-B-001', 'slug' => 'royal-spark-tennis-bracelet', 'name' => 'Royal Spark Tennis Bracelet', 'category' => 'bracelets', 'price' => 58, 'sales' => 57, 'featured' => true, 'image' => 'https://www.pexels.com/photo/necklace-and-bracelet-and-earrings-16210460/', 'tone' => 'elevated' ),
	array( 'sku' => 'SVS-B-002', 'slug' => 'blush-orbit-bracelet', 'name' => 'Blush Orbit Bracelet', 'category' => 'bracelets', 'price' => 54, 'sales' => 38, 'featured' => false, 'image' => 'https://www.pexels.com/photo/young-brunette-woman-wearing-a-chain-link-necklace-and-bracelet-17505427/', 'tone' => 'romantic' ),
	array( 'sku' => 'SVS-B-003', 'slug' => 'dawn-charm-bracelet', 'name' => 'Dawn Charm Bracelet', 'category' => 'bracelets', 'price' => 49, 'sales' => 46, 'featured' => false, 'image' => 'https://www.pexels.com/photo/happy-brunette-woman-with-necklace-19218663/', 'tone' => 'soft-luxe' ),
	array( 'sku' => 'SVS-B-004', 'slug' => 'lumiere-link-bracelet', 'name' => 'Lumiere Link Bracelet', 'category' => 'bracelets', 'price' => 57, 'sales' => 60, 'featured' => true, 'image' => 'https://www.pexels.com/photo/necklace-and-bracelet-and-earrings-16210460/', 'tone' => 'editorial' ),
	array( 'sku' => 'SVS-B-005', 'slug' => 'celestial-cuff-bracelet', 'name' => 'Celestial Cuff Bracelet', 'category' => 'bracelets', 'price' => 61, 'sales' => 66, 'featured' => false, 'image' => 'https://www.pexels.com/photo/a-woman-wearing-a-gold-necklace-and-earrings-27954777/', 'tone' => 'bold-polish' ),
	array( 'sku' => 'SVS-B-006', 'slug' => 'grace-pearl-bracelet', 'name' => 'Grace Pearl Bracelet', 'category' => 'bracelets', 'price' => 53, 'sales' => 35, 'featured' => false, 'image' => 'https://www.pexels.com/photo/happy-brunette-woman-with-necklace-19218663/', 'tone' => 'gift-ready' ),
);

foreach ( $products as $product ) {
	$product['price'] = sv_convert_demo_price_to_pkr( (int) $product['price'] );
	$copy = sv_product_description( $product['name'], $product['category'], $product['tone'] );
	$product['description']       = $copy['description'];
	$product['short_description'] = $copy['short_description'];

	sv_upsert_product( $product, $category_ids[ $product['category'] ] );
}

sv_log( 'Creating navigation menus...' );

$primary_menu_id = sv_menu_id( 'Spark Vibe Primary Menu' );
$footer_menu_id  = sv_menu_id( 'Spark Vibe Footer Menu' );

sv_sync_menu(
	$primary_menu_id,
	array(
		array( 'title' => 'Home', 'object_id' => $home_id ),
		array( 'title' => 'Shop', 'object_id' => $shop_id ),
		array( 'title' => 'About', 'object_id' => $about_id ),
		array( 'title' => 'Contact', 'object_id' => $contact_id ),
		array( 'title' => 'My Account', 'object_id' => $account_id ),
	)
);

sv_sync_menu(
	$footer_menu_id,
	array(
		array( 'title' => 'Home', 'object_id' => $home_id ),
		array( 'title' => 'Shop', 'object_id' => $shop_id ),
		array( 'title' => 'Cart', 'object_id' => $cart_id ),
		array( 'title' => 'Checkout', 'object_id' => $checkout_id ),
		array( 'title' => 'Contact', 'object_id' => $contact_id ),
	)
);

set_theme_mod(
	'nav_menu_locations',
	array(
		'primary' => $primary_menu_id,
		'footer'  => $footer_menu_id,
	)
);

sv_log( 'Building Elementor pages...' );

$shop_url            = get_permalink( $shop_id );
$about_url           = get_permalink( $about_id );
$contact_url         = get_permalink( $contact_id );
$cart_url            = wc_get_cart_url();
$checkout_url        = wc_get_checkout_url();
$account_url         = get_permalink( $account_id );
$rings_url           = get_term_link( $category_ids['rings'], 'product_cat' );
$necklaces_url       = get_term_link( $category_ids['necklaces'], 'product_cat' );
$earrings_url        = get_term_link( $category_ids['earrings'], 'product_cat' );
$bracelets_url       = get_term_link( $category_ids['bracelets'], 'product_cat' );
$contact_form_code   = sprintf( '[contact-form-7 id="%d" title="Spark Vibe Contact Form"]', $contact_form_id );
$newsletter_form_code = sprintf( '[contact-form-7 id="%d" title="Spark Vibe Newsletter Form"]', $newsletter_form_id );

$home_sections = array(
	sv_elementor_section(
		array(
			sv_elementor_widget(
				'html',
				array(
					'html' => sprintf(
						'<section class="sv-hero"><div class="sv-hero__grid"><div class="sv-hero__copy"><span class="sv-eyebrow">Luxury artificial jewelry</span><h1>Jewelry that catches the light and the room.</h1><p>Discover gift-worthy rings, necklaces, earrings, and bracelets with a premium look, smooth checkout, and a polished brand experience from homepage to order confirmation.</p><div class="sv-hero__actions"><a class="sv-button" href="%1$s">Shop the Collection</a><a class="sv-button sv-button--ghost" href="%2$s">About the Brand</a></div><div class="sv-hero__stats"><div class="sv-stat"><strong>24</strong><span>ready-to-sell demo products</span></div><div class="sv-stat"><strong>Guest</strong><span>checkout enabled for faster orders</span></div><div class="sv-stat"><strong>PK</strong><span>default checkout country, fully editable</span></div></div></div><div class="sv-hero__visual">%3$s<div class="sv-floating-card"><strong>Premium finish</strong><span>Curated artificial jewelry with luxe detail, elevated packaging, and styling versatility.</span></div></div></div></section>',
						esc_url( $shop_url ),
						esc_url( $about_url ),
						$home_visual_url ? '<img src="' . esc_url( $home_visual_url ) . '" alt="Spark Vibe Store luxury jewelry hero">' : ''
					),
				)
			),
		)
	),
	sv_elementor_section(
		array(
			sv_elementor_widget(
				'html',
				array(
					'html' => '<section class="sv-card sv-section"><div class="sv-section__head"><div><span class="sv-eyebrow">Featured products</span><h2>Featured Products</h2><p>Our most polished edits for gifting, occasion wear, and everyday glamour.</p></div><a class="sv-button sv-button--ghost" href="' . esc_url( $shop_url ) . '">Browse All Products</a></div></section>',
				)
			),
			sv_elementor_widget(
				'shortcode',
				array(
					'shortcode' => '[products limit="4" columns="4" visibility="featured" orderby="date" order="DESC"]',
				)
			),
		)
	),
	sv_elementor_section(
		array(
			sv_elementor_widget(
				'html',
				array(
					'html' => sprintf(
						'<section class="sv-card sv-section"><div class="sv-section__head"><div><span class="sv-eyebrow">Shop by category</span><h2>Shop by Category</h2><p>Explore our signature edit across rings, necklaces, earrings, and bracelets.</p></div></div><div class="sv-category-grid"><article class="sv-category-card"><h3>Rings</h3><p>Statement sparkle with a soft luxury finish.</p><a href="%1$s">Explore Rings</a></article><article class="sv-category-card"><h3>Necklaces</h3><p>Layered looks, pendants, and polished shine.</p><a href="%2$s">Explore Necklaces</a></article><article class="sv-category-card"><h3>Earrings</h3><p>Studs, drops, and hoops for subtle drama.</p><a href="%3$s">Explore Earrings</a></article><article class="sv-category-card"><h3>Bracelets</h3><p>Elegant links and cuffs that complete the look.</p><a href="%4$s">Explore Bracelets</a></article></div></section>',
						esc_url( is_string( $rings_url ) ? $rings_url : $shop_url ),
						esc_url( is_string( $necklaces_url ) ? $necklaces_url : $shop_url ),
						esc_url( is_string( $earrings_url ) ? $earrings_url : $shop_url ),
						esc_url( is_string( $bracelets_url ) ? $bracelets_url : $shop_url )
					),
				)
			),
		)
	),
	sv_elementor_section(
		array(
			sv_elementor_widget(
				'html',
				array(
					'html' => '<section class="sv-card sv-section"><div class="sv-section__head"><div><span class="sv-eyebrow">Best sellers</span><h2>Best Sellers</h2><p>Top-selling styles with a premium finish and reliable shine.</p></div><a class="sv-button sv-button--ghost" href="' . esc_url( $shop_url ) . '">See More</a></div></section>',
				)
			),
			sv_elementor_widget(
				'shortcode',
				array(
					'shortcode' => '[products limit="4" columns="4" best_selling="true"]',
				)
			),
		)
	),
	sv_elementor_section(
		array(
			sv_elementor_widget(
				'html',
				array(
					'html' => '<section class="sv-panel sv-section"><div class="sv-section__head"><div><span class="sv-eyebrow">Testimonials</span><h2>Testimonials</h2><p>Luxury feel, easy styling, and checkout that works without friction.</p></div></div><div class="sv-testimonial-grid sv-feature-grid"><article class="sv-testimonial"><div class="sv-rating">&#9733;&#9733;&#9733;&#9733;&#9733;</div><p>"The finish looks so premium in person. My order felt gift-ready the moment it arrived."</p><div class="sv-author">Sana, Karachi</div></article><article class="sv-testimonial"><div class="sv-rating">&#9733;&#9733;&#9733;&#9733;&#9733;</div><p>"I checked out on mobile in minutes. The earrings gave exactly the polished look I wanted."</p><div class="sv-author">Mina, Lahore</div></article><article class="sv-testimonial"><div class="sv-rating">&#9733;&#9733;&#9733;&#9733;&#9733;</div><p>"The necklaces layer beautifully and the packaging feels elevated, not generic."</p><div class="sv-author">Areeba, Islamabad</div></article></div></section>',
				)
			),
		)
	),
	sv_elementor_section(
		array(
			sv_elementor_widget(
				'html',
				array(
					'html' => '<section class="sv-panel sv-section"><div class="sv-section__head"><div><span class="sv-eyebrow">Why choose us</span><h2>Why Choose Us</h2><p>Every part of the storefront is designed to feel polished on desktop, tablet, and mobile.</p></div></div><div class="sv-feature-grid"><article class="sv-feature"><h3>Luxury-first design</h3><p>Gold accents, refined typography, soft pink highlights, and confident spacing create a jewelry brand feel.</p></article><article class="sv-feature"><h3>Fast checkout</h3><p>Guest checkout, editable country fields, and responsive forms keep the purchase flow friction-free.</p></article><article class="sv-feature"><h3>Ready for growth</h3><p>WooCommerce product, shipping, email, and notification structure are already set up and easy to extend.</p></article></div></section>',
				)
			),
		)
	),
	sv_elementor_section(
		array(
			sv_elementor_widget(
				'html',
				array(
					'html' => '<section class="sv-newsletter"><div class="sv-newsletter__grid"><div><span class="sv-eyebrow">Newsletter</span><h2>Newsletter</h2><p>Join the Spark Vibe list for curated launches, styling inspiration, and first access to new jewelry arrivals.</p><ul class="sv-inline-list"><li>Launch alerts</li><li>Style notes</li><li>Exclusive offers</li></ul></div><div class="sv-contact-card"><h3>Join the list</h3><p class="sv-note">A quick signup that keeps your next favorite piece one email away.</p></div></div></section>',
				)
			),
			sv_elementor_widget(
				'shortcode',
				array(
					'shortcode' => $newsletter_form_code,
				)
			),
		)
	),
);

$about_sections = array(
	sv_elementor_section(
		array(
			sv_elementor_widget(
				'html',
				array(
					'html' => sprintf(
						'<section class="sv-panel sv-section"><div class="sv-split"><div><span class="sv-eyebrow">About Spark Vibe</span><h1>Luxury-inspired jewelry for modern celebrations and everyday glow.</h1><p>Spark Vibe Store was created for shoppers who want elevated style without sacrificing comfort, versatility, or ease of purchase. We curate artificial jewelry that looks occasion-ready, layers beautifully, and feels polished across both festive and everyday wardrobes.</p><p>From homepage storytelling to the final order email, the experience is designed to feel premium, calm, and beautifully considered.</p><ul class="sv-bullets"><li>Thoughtful product mix across rings, necklaces, earrings, and bracelets</li><li>Responsive storefront built for mobile-first shopping</li><li>Order-ready structure with shipping, account, and notification support</li></ul></div><div class="sv-split__media">%s</div></div></section>',
						$about_visual_url ? '<img src="' . esc_url( $about_visual_url ) . '" alt="Spark Vibe Store jewelry lifestyle">' : ''
					),
				)
			),
		)
	),
	sv_elementor_section(
		array(
			sv_elementor_widget(
				'html',
				array(
					'html' => '<section class="sv-panel sv-section"><div class="sv-section__head"><div><span class="sv-eyebrow">Brand values</span><h2>Refined style, practical experience</h2><p>We balance visual luxury with store usability so discovery, cart updates, and checkout feel equally polished.</p></div></div><div class="sv-feature-grid"><article class="sv-feature"><h3>Elegant curation</h3><p>Every product is presented with realistic naming, clear pricing, and category structure shoppers can understand instantly.</p></article><article class="sv-feature"><h3>Responsive by default</h3><p>The layout stacks cleanly from 320px mobile screens through tablet and desktop without horizontal scroll.</p></article><article class="sv-feature"><h3>Operationally ready</h3><p>WooCommerce emails, shipping methods, guest checkout, and API-ready notification hooks are already in place.</p></article></div></section>',
				)
			),
		)
	),
);

$contact_sections = array(
	sv_elementor_section(
		array(
			sv_elementor_widget(
				'html',
				array(
					'html' => sprintf(
						'<section class="sv-panel sv-section"><div class="sv-contact-grid"><div class="sv-contact-card"><span class="sv-eyebrow">Contact us</span><h1>Need styling help or order support?</h1><p>Reach out for product questions, gift suggestions, shipping support, or order follow-up.</p><div class="sv-contact-card__list"><div class="sv-contact-card__item"><strong>Email</strong><span>%1$s</span></div><div class="sv-contact-card__item"><strong>Store links</strong><span><a href="%2$s">Shop</a> &middot; <a href="%3$s">Cart</a> &middot; <a href="%4$s">Checkout</a> &middot; <a href="%5$s">My Account</a></span></div><div class="sv-contact-card__item"><strong>Hours</strong><span>Mon-Sat, 10:00 AM to 8:00 PM PKT</span></div></div></div><div class="sv-contact-card"><h3>Send a message</h3><p class="sv-note">Use the form and we will follow up with order or product details.</p></div></div></section>',
						esc_html( $admin_email ),
						esc_url( $shop_url ),
						esc_url( $cart_url ),
						esc_url( $checkout_url ),
						esc_url( $account_url )
					),
				)
			),
			sv_elementor_widget(
				'shortcode',
				array(
					'shortcode' => $contact_form_code,
				)
			),
		)
	),
);

sv_set_elementor_content( $home_id, $home_sections );
sv_set_elementor_content( $about_id, $about_sections );
sv_set_elementor_content( $contact_id, $contact_sections );

if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}

flush_rewrite_rules();
wc_delete_product_transients();

sv_log( 'Spark Vibe Store setup complete.' );
