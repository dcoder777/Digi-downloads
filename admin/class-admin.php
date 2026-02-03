<?php
namespace DigiDownloads\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
	}

	public function add_menu() {
		add_menu_page(
			__( 'DigiDownloads', 'digidownloads' ),
			__( 'DigiDownloads', 'digidownloads' ),
			'manage_options',
			'digidownloads',
			array( $this, 'render_products_page' ),
			'dashicons-download',
			56
		);

		add_submenu_page(
			'digidownloads',
			__( 'Products', 'digidownloads' ),
			__( 'Products', 'digidownloads' ),
			'manage_options',
			'digidownloads',
			array( $this, 'render_products_page' )
		);

		add_submenu_page(
			'digidownloads',
			__( 'Add Product', 'digidownloads' ),
			__( 'Add Product', 'digidownloads' ),
			'manage_options',
			'digidownloads-add-product',
			array( $this, 'render_add_product_page' )
		);

		add_submenu_page(
			'digidownloads',
			__( 'Orders', 'digidownloads' ),
			__( 'Orders', 'digidownloads' ),
			'manage_options',
			'digidownloads-orders',
			array( $this, 'render_orders_page' )
		);

		add_submenu_page(
			'digidownloads',
			__( 'Settings', 'digidownloads' ),
			__( 'Settings', 'digidownloads' ),
			'manage_options',
			'digidownloads-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'digidownloads',
			__( 'Shortcodes', 'digidownloads' ),
			__( 'Shortcodes', 'digidownloads' ),
			'manage_options',
			'digidownloads-shortcodes',
			array( $this, 'render_shortcodes_page' )
		);
	}

	public function enqueue_scripts( $hook ) {
		if ( strpos( $hook, 'digidownloads' ) === false ) {
			return;
		}

		wp_enqueue_style( 'digidownloads-admin', DIGIDOWNLOADS_PLUGIN_URL . 'admin/css/admin.css', array(), DIGIDOWNLOADS_VERSION );
	}

	public function handle_actions() {
		if ( ! isset( $_REQUEST['action'] ) || ! isset( $_REQUEST['page'] ) || strpos( $_REQUEST['page'], 'digidownloads' ) === false ) {
			return;
		}

		$action = sanitize_text_field( $_REQUEST['action'] );

		switch ( $action ) {
			case 'add_product':
				$this->handle_add_product();
				break;
			case 'edit_product':
				$this->handle_edit_product();
				break;
			case 'delete_product':
				$this->handle_delete_product();
				break;
			case 'save_settings':
				$this->handle_save_settings();
				break;
		}
	}

	private function handle_add_product() {
		if ( ! isset( $_POST['digidownloads_add_product_nonce'] ) || ! wp_verify_nonce( $_POST['digidownloads_add_product_nonce'], 'digidownloads_add_product' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'digidownloads' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'digidownloads' ) );
		}

		$name = sanitize_text_field( $_POST['name'] );
		$description = wp_kses_post( $_POST['description'] );
		$price = floatval( $_POST['price'] );
		$status = sanitize_text_field( $_POST['status'] );

		$file_path = null;
		$file_name = null;

		// Handle file upload
		if ( ! empty( $_FILES['product_file']['name'] ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );

			$upload_dir = wp_upload_dir();
			$digidownloads_dir = $upload_dir['basedir'] . '/digidownloads';

			$uploaded_file = $_FILES['product_file'];
			$file_name = sanitize_file_name( $uploaded_file['name'] );
			$file_path = $digidownloads_dir . '/' . wp_unique_filename( $digidownloads_dir, $file_name );

			if ( move_uploaded_file( $uploaded_file['tmp_name'], $file_path ) ) {
				// File uploaded successfully
			} else {
				wp_die( esc_html__( 'Failed to upload file.', 'digidownloads' ) );
			}
		}

		$result = \DigiDownloads\Product::create( array(
			'name' => $name,
			'description' => $description,
			'price' => $price,
			'file_path' => $file_path,
			'file_name' => $file_name,
			'status' => $status,
		) );

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) );
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'digidownloads', 'message' => 'product_added' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	private function handle_edit_product() {
		if ( ! isset( $_POST['digidownloads_edit_product_nonce'] ) || ! wp_verify_nonce( $_POST['digidownloads_edit_product_nonce'], 'digidownloads_edit_product' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'digidownloads' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'digidownloads' ) );
		}

		$product_id = absint( $_POST['product_id'] );
		$name = sanitize_text_field( $_POST['name'] );
		$description = wp_kses_post( $_POST['description'] );
		$price = floatval( $_POST['price'] );
		$status = sanitize_text_field( $_POST['status'] );

		$update_data = array(
			'name' => $name,
			'description' => $description,
			'price' => $price,
			'status' => $status,
		);

		// Handle file upload
		if ( ! empty( $_FILES['product_file']['name'] ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );

			$upload_dir = wp_upload_dir();
			$digidownloads_dir = $upload_dir['basedir'] . '/digidownloads';

			$uploaded_file = $_FILES['product_file'];
			$file_name = sanitize_file_name( $uploaded_file['name'] );
			$file_path = $digidownloads_dir . '/' . wp_unique_filename( $digidownloads_dir, $file_name );

			if ( move_uploaded_file( $uploaded_file['tmp_name'], $file_path ) ) {
				$update_data['file_path'] = $file_path;
				$update_data['file_name'] = $file_name;

				// Delete old file
				$product = \DigiDownloads\Product::get( $product_id );
				if ( $product && ! empty( $product->file_path ) && file_exists( $product->file_path ) ) {
					wp_delete_file( $product->file_path );
				}
			}
		}

		$result = \DigiDownloads\Product::update( $product_id, $update_data );

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) );
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'digidownloads', 'message' => 'product_updated' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	private function handle_delete_product() {
		if ( ! isset( $_GET['digidownloads_delete_nonce'] ) || ! wp_verify_nonce( $_GET['digidownloads_delete_nonce'], 'digidownloads_delete_product_' . $_GET['product_id'] ) ) {
			wp_die( esc_html__( 'Security check failed.', 'digidownloads' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'digidownloads' ) );
		}

		$product_id = absint( $_GET['product_id'] );
		$result = \DigiDownloads\Product::delete( $product_id );

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) );
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'digidownloads', 'message' => 'product_deleted' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	private function handle_save_settings() {
		if ( ! isset( $_POST['digidownloads_settings_nonce'] ) || ! wp_verify_nonce( $_POST['digidownloads_settings_nonce'], 'digidownloads_save_settings' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'digidownloads' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'digidownloads' ) );
		}

		$settings = array(
			'currency' => sanitize_text_field( $_POST['currency'] ),
			'payment_gateway' => sanitize_text_field( $_POST['payment_gateway'] ),
			'stripe_publishable_key' => sanitize_text_field( $_POST['stripe_publishable_key'] ),
			'stripe_secret_key' => sanitize_text_field( $_POST['stripe_secret_key'] ),
			'stripe_webhook_secret' => sanitize_text_field( $_POST['stripe_webhook_secret'] ),
			'paypal_client_id' => sanitize_text_field( $_POST['paypal_client_id'] ),
			'paypal_secret' => sanitize_text_field( $_POST['paypal_secret'] ),
			'paypal_mode' => sanitize_text_field( $_POST['paypal_mode'] ),
			'razorpay_key_id' => sanitize_text_field( $_POST['razorpay_key_id'] ),
			'razorpay_key_secret' => sanitize_text_field( $_POST['razorpay_key_secret'] ),
			'download_expiry_hours' => absint( $_POST['download_expiry_hours'] ),
			'max_downloads' => absint( $_POST['max_downloads'] ),
			'from_email' => sanitize_email( $_POST['from_email'] ),
			'from_name' => sanitize_text_field( $_POST['from_name'] ),
		);

		update_option( 'digidownloads_settings', $settings );

		wp_safe_redirect( add_query_arg( array( 'page' => 'digidownloads-settings', 'message' => 'settings_saved' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public function render_products_page() {
		$products = \DigiDownloads\Product::get_all( array( 'status' => 'all' ) );
		include DIGIDOWNLOADS_PLUGIN_DIR . 'admin/views/products.php';
	}

	public function render_add_product_page() {
		$edit_mode = false;
		$product = null;

		if ( isset( $_GET['edit'] ) ) {
			$edit_mode = true;
			$product_id = absint( $_GET['edit'] );
			$product = \DigiDownloads\Product::get( $product_id );
		}

		include DIGIDOWNLOADS_PLUGIN_DIR . 'admin/views/add-product.php';
	}

	public function render_orders_page() {
		$orders = \DigiDownloads\Order::get_all();
		include DIGIDOWNLOADS_PLUGIN_DIR . 'admin/views/orders.php';
	}

	public function render_settings_page() {
		$settings = get_option( 'digidownloads_settings', array(
			'currency' => 'USD',
			'payment_gateway' => 'stripe',
			'stripe_publishable_key' => '',
			'stripe_secret_key' => '',
			'stripe_webhook_secret' => '',
			'paypal_client_id' => '',
			'paypal_secret' => '',
			'paypal_mode' => 'sandbox',
			'razorpay_key_id' => '',
			'razorpay_key_secret' => '',
			'download_expiry_hours' => 48,
			'max_downloads' => 5,
			'from_email' => get_option( 'admin_email' ),
			'from_name' => get_option( 'blogname' ),
		) );

		include DIGIDOWNLOADS_PLUGIN_DIR . 'admin/views/settings.php';
	}

	public function render_shortcodes_page() {
		$products = \DigiDownloads\Product::get_all( array( 'status' => 'all', 'limit' => 100 ) );
		include DIGIDOWNLOADS_PLUGIN_DIR . 'admin/views/shortcodes.php';
	}

	public function show_admin_notices() {
		// Only show on DigiDownloads pages
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'digidownloads' ) === false ) {
			return;
		}

		// Check if payment gateway is configured
		$settings = get_option( 'digidownloads_settings', array() );
		$gateway = isset( $settings['payment_gateway'] ) ? $settings['payment_gateway'] : 'stripe';
		$is_configured = false;

		if ( $gateway === 'stripe' && ! empty( $settings['stripe_publishable_key'] ) && ! empty( $settings['stripe_secret_key'] ) ) {
			$is_configured = true;
		} elseif ( $gateway === 'paypal' && ! empty( $settings['paypal_client_id'] ) && ! empty( $settings['paypal_secret'] ) ) {
			$is_configured = true;
		} elseif ( $gateway === 'razorpay' && ! empty( $settings['razorpay_key_id'] ) && ! empty( $settings['razorpay_key_secret'] ) ) {
			$is_configured = true;
		}

		if ( ! $is_configured ) {
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'DigiDownloads:', 'digidownloads' ); ?></strong>
					<?php
					echo sprintf(
						/* translators: %s: Settings page link */
						__( 'Payment gateway not configured. Checkouts will not work until you add your API keys in %s.', 'digidownloads' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=digidownloads-settings' ) ) . '">' . esc_html__( 'Settings', 'digidownloads' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
		}
	}
}
