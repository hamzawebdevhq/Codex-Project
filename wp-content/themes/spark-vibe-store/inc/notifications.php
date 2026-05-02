<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Spark_Vibe_Store_Notifications {
	public static function init() {
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'maybe_dispatch_order_notifications' ), 20, 3 );
	}

	public static function maybe_dispatch_order_notifications( $order_id, $posted_data, $order ) {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		$payload = self::build_payload( $order );

		/**
		 * Extensibility point for future channel-specific integrations.
		 */
		do_action( 'spark_vibe_store_order_notification_payload', $payload, $order );

		self::maybe_send_remote( 'sms_endpoint', $payload );
		self::maybe_send_remote( 'whatsapp_endpoint', $payload );
	}

	protected static function build_payload( WC_Order $order ) {
		$items = array();

		foreach ( $order->get_items() as $item ) {
			$items[] = array(
				'name'     => $item->get_name(),
				'quantity' => $item->get_quantity(),
				'total'    => wp_strip_all_tags( wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) ) ),
			);
		}

		return array(
			'store' => get_bloginfo( 'name' ),
			'order' => array(
				'id'             => $order->get_id(),
				'number'         => $order->get_order_number(),
				'status'         => $order->get_status(),
				'total'          => wp_strip_all_tags( $order->get_formatted_order_total() ),
				'payment_method' => $order->get_payment_method_title(),
				'created_at'     => $order->get_date_created() ? $order->get_date_created()->date( DATE_ATOM ) : '',
			),
			'customer' => array(
				'name'  => trim( $order->get_formatted_billing_full_name() ),
				'email' => $order->get_billing_email(),
				'phone' => $order->get_billing_phone(),
			),
			'items' => $items,
		);
	}

	protected static function maybe_send_remote( $setting_key, array $payload ) {
		$settings = apply_filters(
			'spark_vibe_store_notification_settings',
			array(
				'sms_endpoint'      => '',
				'sms_token'         => '',
				'whatsapp_endpoint' => '',
				'whatsapp_token'    => '',
			)
		);

		if ( empty( $settings[ $setting_key ] ) ) {
			return;
		}

		$token_key = str_replace( '_endpoint', '_token', $setting_key );
		$headers   = array(
			'Content-Type' => 'application/json',
		);

		if ( ! empty( $settings[ $token_key ] ) ) {
			$headers['Authorization'] = 'Bearer ' . $settings[ $token_key ];
		}

		wp_remote_post(
			esc_url_raw( $settings[ $setting_key ] ),
			array(
				'timeout' => 15,
				'headers' => $headers,
				'body'    => wp_json_encode( $payload ),
			)
		);
	}
}

Spark_Vibe_Store_Notifications::init();

