<?php
namespace DigiDownloads\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PayPal extends Gateway {

	protected function init() {
		$this->id = 'paypal';
		$this->name = 'PayPal';
		$this->settings = $this->get_settings();

		add_action( 'init', array( $this, 'handle_webhook_request' ) );
	}

	public function create_payment( $data ) {
		$client_id = $this->settings['paypal_client_id'] ?? '';
		$secret = $this->settings['paypal_secret'] ?? '';
		$mode = $this->settings['paypal_mode'] ?? 'sandbox';

		if ( empty( $client_id ) || empty( $secret ) ) {
			return new \WP_Error( 'paypal_not_configured', __( 'PayPal is not configured.', 'digidownloads' ) );
		}

		$api_url = $mode === 'live' 
			? 'https://api-m.paypal.com' 
			: 'https://api-m.sandbox.paypal.com';

		// Get access token
		$token = $this->get_access_token( $api_url, $client_id, $secret );
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		// Create order
		$amount = floatval( $data['amount'] );
		$currency = sanitize_text_field( $data['currency'] ?? 'USD' );
		$metadata = $data['metadata'] ?? array();

		$order_data = array(
			'intent' => 'CAPTURE',
			'purchase_units' => array(
				array(
					'amount' => array(
						'currency_code' => $currency,
						'value' => number_format( $amount, 2, '.', '' ),
					),
					'custom_id' => $metadata['order_id'] ?? '',
				),
			),
			'application_context' => array(
				'return_url' => home_url( '/?digidownloads_paypal=success' ),
				'cancel_url' => home_url( '/?digidownloads_paypal=cancel' ),
			),
		);

		$response = wp_remote_post(
			$api_url . '/v2/checkout/orders',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				),
				'body' => wp_json_encode( $order_data ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->log( 'PayPal Order Error: ' . $response->get_error_message() );
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			$this->log( 'PayPal API Error: ' . $body['error_description'] );
			return new \WP_Error( 'paypal_api_error', $body['error_description'] );
		}

		return $body;
	}

	private function get_access_token( $api_url, $client_id, $secret ) {
		$response = wp_remote_post(
			$api_url . '/v1/oauth2/token',
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $secret ),
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body' => 'grant_type=client_credentials',
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['access_token'] ) ) {
			return $body['access_token'];
		}

		return new \WP_Error( 'paypal_token_error', __( 'Failed to get PayPal access token.', 'digidownloads' ) );
	}

	public function process_payment( $order_id, $data ) {
		// Handled via return URL and webhook
		return true;
	}

	public function handle_webhook_request() {
		if ( isset( $_GET['digidownloads_paypal'] ) ) {
			if ( $_GET['digidownloads_paypal'] === 'success' ) {
				$this->handle_return();
			} elseif ( $_GET['digidownloads_paypal'] === 'cancel' ) {
				wp_redirect( home_url() );
				exit;
			}
		}

		if ( isset( $_GET['digidownloads_webhook'] ) && $_GET['digidownloads_webhook'] === 'paypal' ) {
			$this->handle_webhook();
		}
	}

	private function handle_return() {
		// This would capture the payment and process the order
		// Implementation depends on your flow
		wp_redirect( home_url() );
		exit;
	}

	public function handle_webhook() {
		// PayPal webhook verification and processing
		$payload = @file_get_contents( 'php://input' );
		$data = json_decode( $payload, true );

		$this->log( 'PayPal Webhook: ' . ( $data['event_type'] ?? 'unknown' ) );

		status_header( 200 );
		exit;
	}
}
