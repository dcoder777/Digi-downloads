<?php
namespace DigiDownloads\PublicFacing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PublicFacing {

	public function __construct() {
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'template_redirect', array( $this, 'handle_download' ) );
		add_action( 'wp_ajax_digidownloads_create_checkout', array( $this, 'create_checkout' ) );
		add_action( 'wp_ajax_nopriv_digidownloads_create_checkout', array( $this, 'create_checkout' ) );
		add_action( 'wp_ajax_digidownloads_confirm_stripe_payment', array( $this, 'confirm_stripe_payment' ) );
		add_action( 'wp_ajax_nopriv_digidownloads_confirm_stripe_payment', array( $this, 'confirm_stripe_payment' ) );
		add_action( 'wp_ajax_digidownloads_test_update_order', array( $this, 'test_update_order' ) );
		add_action( 'wp_ajax_nopriv_digidownloads_test_update_order', array( $this, 'test_update_order' ) );
		add_action( 'wp_ajax_digidownloads_verify_razorpay', array( $this, 'verify_razorpay' ) );
		add_action( 'wp_ajax_nopriv_digidownloads_verify_razorpay', array( $this, 'verify_razorpay' ) );
		add_action( 'wp_ajax_digidownloads_test', array( $this, 'test_ajax' ) );
		add_action( 'wp_ajax_nopriv_digidownloads_test', array( $this, 'test_ajax' ) );
		
	}

	public function register_shortcodes() {
		add_shortcode( 'digidownloads_product', array( $this, 'product_shortcode' ) );
		add_shortcode( 'digidownloads_checkout', array( $this, 'checkout_shortcode' ) );
		add_shortcode( 'digidownloads_products', array( $this, 'products_list_shortcode' ) );
	}

	public function enqueue_scripts() {
		global $post;
		
		// Check if any DigiDownloads shortcode is present
		$has_shortcode = false;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'digidownloads_product' ) ) {
			$has_shortcode = true;
		}
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'digidownloads_checkout' ) ) {
			$has_shortcode = true;
		}
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'digidownloads_products' ) ) {
			$has_shortcode = true;
		}
		
		if ( ! $has_shortcode ) {
			return;
		}
			
		wp_enqueue_style( 'digidownloads-public', DIGIDOWNLOADS_PLUGIN_URL . 'public/css/public.css', array(), DIGIDOWNLOADS_VERSION );
		wp_enqueue_script( 'digidownloads-public', DIGIDOWNLOADS_PLUGIN_URL . 'public/js/public.js', array( 'jquery' ), DIGIDOWNLOADS_VERSION, true );
		
		$settings = get_option( 'digidownloads_settings', array() );
		$gateway = isset( $settings['payment_gateway'] ) ? $settings['payment_gateway'] : 'stripe';
		
		// Load gateway-specific scripts
		if ( $gateway === 'stripe' ) {
			wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v3/', array(), null, true );
			wp_add_inline_script( 'digidownloads-public', 'var digidownloadsStripeKey = "' . esc_js( isset( $settings['stripe_publishable_key'] ) ? $settings['stripe_publishable_key'] : '' ) . '";', 'before' );
		} elseif ( $gateway === 'razorpay' ) {
			wp_enqueue_script( 'razorpay-checkout', 'https://checkout.razorpay.com/v1/checkout.js', array(), null, true );
			wp_add_inline_script( 'digidownloads-public', 'var digidownloadsRazorpayKey = "' . esc_js( isset( $settings['razorpay_key_id'] ) ? $settings['razorpay_key_id'] : '' ) . '";', 'before' );
		}
		
		wp_localize_script( 'digidownloads-public', 'digidownloads', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'digidownloads_checkout' ),
			'gateway' => $gateway,
		) );
	}

	public function product_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => 0,
		), $atts );

		$product_id = absint( $atts['id'] );
		if ( ! $product_id ) {
			return '<p>' . esc_html__( 'Invalid product ID.', 'digidownloads' ) . '</p>';
		}

		$product = \DigiDownloads\Product::get( $product_id );

		if ( ! $product || $product->status !== 'active' ) {
			return '<p>' . esc_html__( 'Product not found or unavailable.', 'digidownloads' ) . '</p>';
		}

		ob_start();
		include DIGIDOWNLOADS_PLUGIN_DIR . 'public/views/product.php';
		return ob_get_clean();
	}

	public function checkout_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => 0,
		), $atts );

		$product_id = absint( $atts['id'] );
		if ( ! $product_id ) {
			return '<p>' . esc_html__( 'Invalid product ID.', 'digidownloads' ) . '</p>';
		}

		$product = \DigiDownloads\Product::get( $product_id );

		if ( ! $product || $product->status !== 'active' ) {
			return '<p>' . esc_html__( 'Product not found or unavailable.', 'digidownloads' ) . '</p>';
		}

		$settings = get_option( 'digidownloads_settings', array() );

		ob_start();
		include DIGIDOWNLOADS_PLUGIN_DIR . 'public/views/checkout.php';
		return ob_get_clean();
	}

	public function products_list_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'columns' => 3,
			'limit' => -1,
		), $atts );

		$columns = absint( $atts['columns'] );
		$limit = absint( $atts['limit'] );

		// Get all active products
		global $wpdb;
		$table = $wpdb->prefix . 'digidownloads_products';
		
		if ( $limit > 0 ) {
			$products = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM $table WHERE status = 'active' ORDER BY id DESC LIMIT %d",
				$limit
			) );
		} else {
			$products = $wpdb->get_results( "SELECT * FROM $table WHERE status = 'active' ORDER BY id DESC" );
		}

		if ( empty( $products ) ) {
			return '<p class="digidownloads-no-products">' . esc_html__( 'No products available at the moment.', 'digidownloads' ) . '</p>';
		}

		ob_start();
		include DIGIDOWNLOADS_PLUGIN_DIR . 'public/views/products-list.php';
		return ob_get_clean();
	}

	public function test_ajax() {
		wp_send_json_success( array( 'message' => 'AJAX is working!' ) );
	}

	public function test_update_order() {
		// Simple test endpoint to verify order updates work
		if ( ! isset( $_POST['order_id'] ) ) {
			wp_send_json_error( array( 'message' => 'No order_id provided' ) );
			return;
		}

		$order_id = sanitize_text_field( $_POST['order_id'] );
		
		// Get the order
		$order = \DigiDownloads\Order::get_by_order_id( $order_id );
		if ( ! $order ) {
			wp_send_json_error( array( 'message' => 'Order not found: ' . $order_id ) );
			return;
		}

		// Try to update it
		$result = \DigiDownloads\Order::update_status( $order_id, 'completed', 'test-update' );
		
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => 'Update failed: ' . $result->get_error_message() ) );
			return;
		}

		// Verify the update
		$updated_order = \DigiDownloads\Order::get_by_order_id( $order_id );
		
		wp_send_json_success( array(
			'message' => 'Order updated successfully',
			'original_status' => $order->payment_status,
			'new_status' => $updated_order->payment_status,
		) );
	}

	public function create_checkout() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_send_json_error( array( 'message' => 'Nonce missing' ) );
			return;
		}

		if ( ! wp_verify_nonce( $_POST['nonce'], 'digidownloads_checkout' ) ) {
			wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
			return;
		}

		// Get POST data
		if ( ! isset( $_POST['product_id'] ) || ! isset( $_POST['email'] ) ) {
			wp_send_json_error( array( 'message' => 'Missing required fields' ) );
			return;
		}

		$product_id = absint( $_POST['product_id'] );
		$email = sanitize_email( $_POST['email'] );

		if ( ! $product_id || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => 'Invalid product or email' ) );
			return;
		}

		$product = \DigiDownloads\Product::get( $product_id );

		if ( ! $product || $product->status !== 'active' ) {
			wp_send_json_error( array( 'message' => __( 'Product not available.', 'digidownloads' ) ) );
			return;
		}

		// Check if payment gateway is configured
		$settings = get_option( 'digidownloads_settings', array() );
		$gateway = isset( $settings['payment_gateway'] ) ? $settings['payment_gateway'] : 'stripe';
		
		if ( $gateway === 'stripe' && ( empty( $settings['stripe_publishable_key'] ) || empty( $settings['stripe_secret_key'] ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Payment gateway not configured. Please contact the site administrator.', 'digidownloads' ) ) );
		} elseif ( $gateway === 'paypal' && ( empty( $settings['paypal_client_id'] ) || empty( $settings['paypal_secret'] ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Payment gateway not configured. Please contact the site administrator.', 'digidownloads' ) ) );
		} elseif ( $gateway === 'razorpay' && ( empty( $settings['razorpay_key_id'] ) || empty( $settings['razorpay_key_secret'] ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Payment gateway not configured. Please contact the site administrator.', 'digidownloads' ) ) );
		}

		// Create order
		$selected_gateway = ! empty( $settings['payment_gateway'] ) ? $settings['payment_gateway'] : 'stripe';
		$order_id = 'DD-' . strtoupper( wp_generate_password( 12, false ) );
		$order_db_id = \DigiDownloads\Order::create( array(
			'order_id' => $order_id,
			'product_id' => $product_id,
			'buyer_email' => $email,
			'amount' => $product->price,
			'payment_status' => 'pending',
			'payment_gateway' => $selected_gateway,
		) );

		if ( is_wp_error( $order_db_id ) ) {
			wp_send_json_error( array( 'message' => $order_db_id->get_error_message() ) );
		}

		// Create Stripe payment intent
		$settings = get_option( 'digidownloads_settings', array() );
		
		$currency = ! empty( $settings['currency'] ) ? $settings['currency'] : 'USD';

		if ( $selected_gateway === 'stripe' ) {
			if ( empty( $settings['stripe_secret_key'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Payment gateway not configured.', 'digidownloads' ) ) );
			}

			$stripe = new \DigiDownloads\Gateways\Stripe();
			$payment_intent = $stripe->create_payment_intent( array(
				'amount' => $product->price,
				'currency' => $currency,
				'metadata' => array(
					'order_id' => $order_id,
					'product_id' => $product_id,
					'buyer_email' => $email,
				),
			) );

			if ( is_wp_error( $payment_intent ) ) {
				wp_send_json_error( array( 'message' => $payment_intent->get_error_message() ) );
			}

			wp_send_json_success( array(
				'client_secret' => $payment_intent['client_secret'],
				'order_id' => $order_id,
			) );
		} elseif ( $selected_gateway === 'paypal' ) {
			if ( empty( $settings['paypal_client_id'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Payment gateway not configured.', 'digidownloads' ) ) );
			}

			$paypal = new \DigiDownloads\Gateways\PayPal();
			$payment = $paypal->create_payment( array(
				'amount' => $product->price,
				'currency' => $currency,
				'metadata' => array(
					'order_id' => $order_id,
					'product_id' => $product_id,
					'buyer_email' => $email,
				),
			) );

			if ( is_wp_error( $payment ) ) {
				wp_send_json_error( array( 'message' => $payment->get_error_message() ) );
			}

			// Return approval URL for redirect
			$approval_url = '';
			foreach ( $payment['links'] as $link ) {
				if ( $link['rel'] === 'approve' ) {
					$approval_url = $link['href'];
					break;
				}
			}

			wp_send_json_success( array(
				'redirect_url' => $approval_url,
				'order_id' => $order_id,
			) );
		} elseif ( $selected_gateway === 'razorpay' ) {
			if ( empty( $settings['razorpay_key_id'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Payment gateway not configured.', 'digidownloads' ) ) );
			}

			$razorpay = new \DigiDownloads\Gateways\Razorpay();
			$payment = $razorpay->create_payment( array(
				'amount' => $product->price,
				'currency' => $currency,
				'metadata' => array(
					'order_id' => $order_id,
					'product_id' => $product_id,
					'buyer_email' => $email,
				),
			) );

			if ( is_wp_error( $payment ) ) {
				wp_send_json_error( array( 'message' => $payment->get_error_message() ) );
			}

			wp_send_json_success( array(
				'razorpay_order_id' => $payment['id'],
				'razorpay_key' => $settings['razorpay_key_id'],
				'order_id' => $order_id,
				'amount' => $payment['amount'],
				'currency' => $payment['currency'],
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid payment gateway selected.', 'digidownloads' ) ) );
		}
	}

	public function verify_razorpay() {
		check_ajax_referer( 'digidownloads_checkout', 'nonce' );

		$payment_id = isset( $_POST['razorpay_payment_id'] ) ? sanitize_text_field( $_POST['razorpay_payment_id'] ) : '';
		$order_id = isset( $_POST['razorpay_order_id'] ) ? sanitize_text_field( $_POST['razorpay_order_id'] ) : '';
		$signature = isset( $_POST['razorpay_signature'] ) ? sanitize_text_field( $_POST['razorpay_signature'] ) : '';

		if ( empty( $payment_id ) || empty( $order_id ) || empty( $signature ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid payment data.', 'digidownloads' ) ) );
		}

		$settings = get_option( 'digidownloads_settings', array() );
		
		if ( ! isset( $settings['razorpay_key_secret'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Payment gateway not configured.', 'digidownloads' ) ) );
		}

		// Verify signature
		$expected_signature = hash_hmac( 'sha256', $order_id . '|' . $payment_id, $settings['razorpay_key_secret'] );
		
		if ( $signature !== $expected_signature ) {
			wp_send_json_error( array( 'message' => __( 'Invalid payment signature.', 'digidownloads' ) ) );
		}

		// Find order by Razorpay order ID and update it
		global $wpdb;
		$table = $wpdb->prefix . 'digidownloads_orders';
		$order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE payment_intent_id = %s", $order_id ) );

		if ( ! $order ) {
			wp_send_json_error( array( 'message' => __( 'Order not found.', 'digidownloads' ) ) );
		}

		// Update order status
		\DigiDownloads\Order::update( $order->id, array(
			'status' => 'completed',
		) );

		// Generate download token
		$token = \DigiDownloads\Download::generate_token( $order->id );

		// Send email
		\DigiDownloads\Email::send_purchase_confirmation( $order->buyer_email, $order->order_id, $token );

		wp_send_json_success( array(
			'message' => __( 'Payment verified successfully.', 'digidownloads' ),
		) );
	}

	public function confirm_stripe_payment() {
		// Debug file
		$debug_file = DIGIDOWNLOADS_PLUGIN_DIR . 'stripe-confirm-debug.log';
		$log = function( $msg ) use ( $debug_file ) {
			file_put_contents( $debug_file, '[' . date( 'Y-m-d H:i:s' ) . '] ' . $msg . "\n", FILE_APPEND );
			error_log( $msg );
		};

		$log( '=== confirm_stripe_payment START ===' );
		$log( 'POST Data: ' . json_encode( $_POST ) );

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'digidownloads_checkout' ) ) {
			$log( 'FAIL: Nonce check failed' );
			wp_send_json_error( array( 'message' => 'Nonce check failed' ) );
			return;
		}

		// Get order ID - accept from POST or from payment intent
		$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : null;
		$payment_intent_id = isset( $_POST['payment_intent_id'] ) ? sanitize_text_field( $_POST['payment_intent_id'] ) : null;

		if ( ! $order_id ) {
			$log( 'FAIL: No order_id in POST' );
			wp_send_json_error( array( 'message' => 'Order ID missing from request' ) );
			return;
		}

		$log( 'Processing: order_id=' . $order_id . ', payment_intent_id=' . $payment_intent_id );

		// Lookup order
		$order = \DigiDownloads\Order::get_by_order_id( $order_id );
		if ( ! $order ) {
			$log( 'FAIL: Order not found - ' . $order_id );
			wp_send_json_error( array( 'message' => 'Order not found in database' ) );
			return;
		}

		$log( 'Found order: id=' . $order->id . ', status=' . $order->payment_status . ', email=' . $order->buyer_email );

		// Update status
		$result = \DigiDownloads\Order::update_status( $order_id, 'completed', $payment_intent_id );
		
		if ( is_wp_error( $result ) ) {
			$log( 'FAIL: Update failed - ' . $result->get_error_message() );
			wp_send_json_error( array( 'message' => 'Failed to update order: ' . $result->get_error_message() ) );
			return;
		}

		$log( 'SUCCESS: Order status updated to completed' );

		// Verify update
		$check_order = \DigiDownloads\Order::get_by_order_id( $order_id );
		$log( 'Verification: new status = ' . $check_order->payment_status );

		// Generate token
		$settings = get_option( 'digidownloads_settings', array(
			'download_expiry_hours' => 48,
			'max_downloads' => 5,
		) );

		$token = \DigiDownloads\Download::generate_token(
			$order_id,
			$order->product_id,
			intval( $settings['download_expiry_hours'] ?? 48 ),
			intval( $settings['max_downloads'] ?? 5 )
		);

		if ( is_wp_error( $token ) ) {
			$log( 'WARN: Token generation failed - ' . $token->get_error_message() );
			// Order is updated, so we return success even if token fails
		} else {
			$log( 'Token generated: ' . $token );

			// Send email
			$email_sender = new \DigiDownloads\Email();
			$sent = $email_sender->send_download_email( $order->buyer_email, $order_id, $token );
			
			$log( 'Email sent: ' . ( $sent ? 'yes' : 'no' ) );
		}

		$log( '=== confirm_stripe_payment SUCCESS ===' . "\n" );

		wp_send_json_success( array(
			'message' => 'Payment confirmed and processed',
			'order_id' => $order_id,
		) );
	}

	public function handle_download() {
		if ( isset( $_GET['dd_download'] ) ) {
			$token = sanitize_text_field( $_GET['dd_download'] );
			\DigiDownloads\Download::serve_file( $token );
		}
	}
}
