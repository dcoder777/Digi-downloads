<?php
namespace DigiDownloads\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Stripe extends Gateway {

	protected function init() {
		$this->id = 'stripe';
		$this->name = 'Stripe';
		$this->settings = $this->get_settings();

		add_action( 'init', array( $this, 'handle_webhook_request' ) );
	}

	public function create_payment_intent( $data ) {
		$secret_key = $this->settings['stripe_secret_key'] ?? '';

		if ( empty( $secret_key ) ) {
			return new \WP_Error( 'stripe_not_configured', __( 'Stripe is not configured.', 'digidownloads' ) );
		}

		$amount = floatval( $data['amount'] ) * 100; // Convert to cents
		$currency = isset( $data['currency'] ) ? strtolower( sanitize_text_field( $data['currency'] ) ) : 'usd';
		$metadata = $data['metadata'] ?? array();

		$body = array(
			'amount' => intval( $amount ),
			'currency' => $currency,
			'metadata' => $metadata,
			'payment_method_types' => array( 'card' ),
		);

		$response = wp_remote_post(
			'https://api.stripe.com/v1/payment_intents',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $secret_key,
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body' => http_build_query( $body ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->log( 'Payment Intent Error: ' . $response->get_error_message() );
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			$this->log( 'Stripe API Error: ' . $body['error']['message'] );
			return new \WP_Error( 'stripe_api_error', $body['error']['message'] );
		}

		return $body;
	}

	public function process_payment( $order_id, $data ) {
		// This is handled via webhook
		return true;
	}

	public function handle_webhook_request() {
		if ( ! isset( $_GET['digidownloads_webhook'] ) || $_GET['digidownloads_webhook'] !== 'stripe' ) {
			return;
		}

		$this->handle_webhook();
	}

	public function handle_webhook() {
		$webhook_secret = $this->settings['stripe_webhook_secret'] ?? '';

		if ( empty( $webhook_secret ) ) {
			$this->log( 'Webhook secret not configured' );
			status_header( 400 );
			exit;
		}

		$payload = @file_get_contents( 'php://input' );
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

		try {
			$event = $this->verify_webhook_signature( $payload, $sig_header, $webhook_secret );
		} catch ( \Exception $e ) {
			$this->log( 'Webhook signature verification failed: ' . $e->getMessage() );
			status_header( 400 );
			exit;
		}

		$this->log( 'Webhook received: ' . $event['type'] );

		switch ( $event['type'] ) {
			case 'payment_intent.succeeded':
				$this->handle_payment_success( $event['data']['object'] );
				break;

			case 'payment_intent.payment_failed':
				$this->handle_payment_failed( $event['data']['object'] );
				break;
		}

		status_header( 200 );
		exit;
	}

	private function verify_webhook_signature( $payload, $sig_header, $secret ) {
		if ( empty( $sig_header ) ) {
			throw new \Exception( 'No signature header' );
		}

		$elements = explode( ',', $sig_header );
		$timestamp = 0;
		$signature = '';

		foreach ( $elements as $element ) {
			list( $key, $value ) = explode( '=', $element, 2 );
			if ( $key === 't' ) {
				$timestamp = intval( $value );
			} elseif ( $key === 'v1' ) {
				$signature = $value;
			}
		}

		if ( empty( $timestamp ) || empty( $signature ) ) {
			throw new \Exception( 'Invalid signature format' );
		}

		// Check timestamp tolerance (5 minutes)
		if ( abs( time() - $timestamp ) > 300 ) {
			throw new \Exception( 'Timestamp outside tolerance' );
		}

		$signed_payload = $timestamp . '.' . $payload;
		$expected_signature = hash_hmac( 'sha256', $signed_payload, $secret );

		if ( ! hash_equals( $expected_signature, $signature ) ) {
			throw new \Exception( 'Invalid signature' );
		}

		return json_decode( $payload, true );
	}

	private function handle_payment_success( $payment_intent ) {
		$order_id = $payment_intent['metadata']['order_id'] ?? '';
		$transaction_id = $payment_intent['id'] ?? '';

		if ( empty( $order_id ) ) {
			$this->log( 'No order_id in payment_intent metadata' );
			return;
		}

		$order = \DigiDownloads\Order::get_by_order_id( $order_id );

		if ( ! $order ) {
			$this->log( 'Order not found: ' . $order_id );
			return;
		}

		// Update order status
		\DigiDownloads\Order::update_status( $order_id, 'completed', $transaction_id );

		// Generate download token
		$settings = get_option( 'digidownloads_settings', array(
			'download_expiry_hours' => 48,
			'max_downloads' => 5,
		) );

		$token = \DigiDownloads\Download::generate_token(
			$order_id,
			$order->product_id,
			$settings['download_expiry_hours'],
			$settings['max_downloads']
		);

		if ( ! is_wp_error( $token ) ) {
			// Send email
			$email_sender = new \DigiDownloads\Email();
			$email_sender->send_download_email( $order->buyer_email, $order_id, $token );
		}

		$this->log( 'Payment completed for order: ' . $order_id );
	}

	private function handle_payment_failed( $payment_intent ) {
		$order_id = $payment_intent['metadata']['order_id'] ?? '';

		if ( empty( $order_id ) ) {
			return;
		}

		\DigiDownloads\Order::update_status( $order_id, 'failed' );

		$this->log( 'Payment failed for order: ' . $order_id );
	}
}
