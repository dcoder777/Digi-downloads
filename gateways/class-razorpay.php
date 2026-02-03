<?php
namespace DigiDownloads\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Razorpay extends Gateway {

	protected function init() {
		$this->id = 'razorpay';
		$this->name = 'Razorpay';
		$this->settings = $this->get_settings();

		add_action( 'init', array( $this, 'handle_webhook_request' ) );
	}

	public function create_payment( $data ) {
		$key_id = $this->settings['razorpay_key_id'] ?? '';
		$key_secret = $this->settings['razorpay_key_secret'] ?? '';

		if ( empty( $key_id ) || empty( $key_secret ) ) {
			return new \WP_Error( 'razorpay_not_configured', __( 'Razorpay is not configured.', 'digidownloads' ) );
		}

		$amount = floatval( $data['amount'] ) * 100; // Razorpay uses paise
		$currency = sanitize_text_field( $data['currency'] ?? 'INR' );
		$metadata = $data['metadata'] ?? array();

		$order_data = array(
			'amount' => intval( $amount ),
			'currency' => $currency,
			'receipt' => $metadata['order_id'] ?? '',
			'notes' => array(
				'order_id' => $metadata['order_id'] ?? '',
				'product_id' => $metadata['product_id'] ?? '',
				'buyer_email' => $metadata['buyer_email'] ?? '',
			),
		);

		$response = wp_remote_post(
			'https://api.razorpay.com/v1/orders',
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $key_id . ':' . $key_secret ),
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode( $order_data ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->log( 'Razorpay Order Error: ' . $response->get_error_message() );
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			$this->log( 'Razorpay API Error: ' . $body['error']['description'] );
			return new \WP_Error( 'razorpay_api_error', $body['error']['description'] );
		}

		return $body;
	}

	public function verify_payment( $razorpay_order_id, $razorpay_payment_id, $razorpay_signature ) {
		$key_secret = $this->settings['razorpay_key_secret'] ?? '';
		
		$generated_signature = hash_hmac(
			'sha256',
			$razorpay_order_id . '|' . $razorpay_payment_id,
			$key_secret
		);

		return hash_equals( $generated_signature, $razorpay_signature );
	}

	public function process_payment( $order_id, $data ) {
		// Verify payment signature
		$is_valid = $this->verify_payment(
			$data['razorpay_order_id'] ?? '',
			$data['razorpay_payment_id'] ?? '',
			$data['razorpay_signature'] ?? ''
		);

		if ( ! $is_valid ) {
			return new \WP_Error( 'razorpay_verification_failed', __( 'Payment verification failed.', 'digidownloads' ) );
		}

		return true;
	}

	public function handle_webhook_request() {
		if ( ! isset( $_GET['digidownloads_webhook'] ) || $_GET['digidownloads_webhook'] !== 'razorpay' ) {
			return;
		}

		$this->handle_webhook();
	}

	public function handle_webhook() {
		$key_secret = $this->settings['razorpay_key_secret'] ?? '';

		if ( empty( $key_secret ) ) {
			$this->log( 'Webhook secret not configured' );
			status_header( 400 );
			exit;
		}

		$payload = @file_get_contents( 'php://input' );
		$webhook_signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

		// Verify webhook signature
		$generated_signature = hash_hmac( 'sha256', $payload, $key_secret );

		if ( ! hash_equals( $generated_signature, $webhook_signature ) ) {
			$this->log( 'Webhook signature verification failed' );
			status_header( 400 );
			exit;
		}

		$data = json_decode( $payload, true );
		$event = $data['event'] ?? '';

		$this->log( 'Webhook received: ' . $event );

		switch ( $event ) {
			case 'payment.captured':
				$this->handle_payment_success( $data['payload']['payment']['entity'] );
				break;

			case 'payment.failed':
				$this->handle_payment_failed( $data['payload']['payment']['entity'] );
				break;
		}

		status_header( 200 );
		exit;
	}

	private function handle_payment_success( $payment ) {
		$order_id = $payment['notes']['order_id'] ?? '';
		$transaction_id = $payment['id'] ?? '';

		if ( empty( $order_id ) ) {
			$this->log( 'No order_id in payment notes' );
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

	private function handle_payment_failed( $payment ) {
		$order_id = $payment['notes']['order_id'] ?? '';

		if ( empty( $order_id ) ) {
			return;
		}

		\DigiDownloads\Order::update_status( $order_id, 'failed' );

		$this->log( 'Payment failed for order: ' . $order_id );
	}
}
