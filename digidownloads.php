<?php
/**
 * Plugin Name: DigiDownloads
 * Plugin URI: https://digidownloads.com
 * Description: Lightweight WordPress plugin for selling digital downloads.
 * Version: 1.0.1
 * Author: DigiDownloads Team
 * Author URI: https://digidownloads.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: digidownloads
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'DIGIDOWNLOADS_VERSION', '1.0.1' );
define( 'DIGIDOWNLOADS_PLUGIN_FILE', __FILE__ );
define( 'DIGIDOWNLOADS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DIGIDOWNLOADS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DIGIDOWNLOADS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader
spl_autoload_register( function ( $class ) {
	$prefix = 'DigiDownloads\\';
	$base_dir = DIGIDOWNLOADS_PLUGIN_DIR . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

/**
 * Main plugin class
 */
final class DigiDownloads_Plugin {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_hooks();
	}

	private function init_hooks() {
		register_activation_hook( DIGIDOWNLOADS_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( DIGIDOWNLOADS_PLUGIN_FILE, array( $this, 'deactivate' ) );
		
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function activate() {
		require_once DIGIDOWNLOADS_PLUGIN_DIR . 'db/class-database.php';
		DigiDownloads\DB\Database::install();
		
		// Create uploads directory
		$upload_dir = wp_upload_dir();
		$digidownloads_dir = $upload_dir['basedir'] . '/digidownloads';
		if ( ! file_exists( $digidownloads_dir ) ) {
			wp_mkdir_p( $digidownloads_dir );
			// Prevent direct access
			file_put_contents( $digidownloads_dir . '/.htaccess', 'deny from all' );
			file_put_contents( $digidownloads_dir . '/index.php', '<?php // Silence is golden' );
		}
		
		flush_rewrite_rules();
	}

	public function deactivate() {
		flush_rewrite_rules();
	}

	public function init() {
		// Load text domain
		load_plugin_textdomain( 'digidownloads', false, dirname( DIGIDOWNLOADS_PLUGIN_BASENAME ) . '/languages' );

		// Initialize core components
		if ( is_admin() && ! wp_doing_ajax() ) {
			$this->init_admin();
		}

		// Always load public-facing functionality (needed for AJAX)
		if ( ! is_admin() || wp_doing_ajax() ) {
			$this->init_public();
		}

		// Initialize gateways
		$this->init_gateways();
	}

	private function init_admin() {
		require_once DIGIDOWNLOADS_PLUGIN_DIR . 'admin/class-admin.php';
		new DigiDownloads\Admin\Admin();
	}

	private function init_public() {
		require_once DIGIDOWNLOADS_PLUGIN_DIR . 'public/class-public.php';
		new DigiDownloads\PublicFacing\PublicFacing();
	}

	private function init_gateways() {
		require_once DIGIDOWNLOADS_PLUGIN_DIR . 'gateways/class-gateway.php';
		require_once DIGIDOWNLOADS_PLUGIN_DIR . 'gateways/class-stripe.php';
		require_once DIGIDOWNLOADS_PLUGIN_DIR . 'gateways/class-paypal.php';
		require_once DIGIDOWNLOADS_PLUGIN_DIR . 'gateways/class-razorpay.php';
		
		do_action( 'digidownloads_gateways_loaded' );
	}
}

// Initialize the plugin
function digidownloads() {
	return DigiDownloads_Plugin::instance();
}

digidownloads();
