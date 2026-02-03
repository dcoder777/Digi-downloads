<?php
namespace DigiDownloads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Download {

	public static function generate_token( $order_id, $product_id, $expiry_hours = 48, $max_downloads = 5 ) {
		global $wpdb;

		$token = bin2hex( random_bytes( 32 ) );
		$expires_at = gmdate( 'Y-m-d H:i:s', time() + ( $expiry_hours * HOUR_IN_SECONDS ) );

		$table = $wpdb->prefix . 'digidownloads_download_tokens';

		$inserted = $wpdb->insert(
			$table,
			array(
				'token' => $token,
				'order_id' => sanitize_text_field( $order_id ),
				'product_id' => absint( $product_id ),
				'download_count' => 0,
				'max_downloads' => absint( $max_downloads ),
				'expires_at' => $expires_at,
			),
			array( '%s', '%s', '%d', '%d', '%d', '%s' )
		);

		if ( $inserted === false ) {
			return new \WP_Error( 'db_error', __( 'Failed to generate download token.', 'digidownloads' ) );
		}

		do_action( 'digidownloads_download_token_generated', $token, $order_id );

		return $token;
	}

	public static function verify_token( $token ) {
		global $wpdb;

		if ( empty( $token ) ) {
			return new \WP_Error( 'invalid_token', __( 'Invalid download token.', 'digidownloads' ) );
		}

		$table = $wpdb->prefix . 'digidownloads_download_tokens';
		$token_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE token = %s", $token ) );

		if ( ! $token_data ) {
			return new \WP_Error( 'token_not_found', __( 'Download token not found.', 'digidownloads' ) );
		}

		// Check expiry
		if ( strtotime( $token_data->expires_at ) < time() ) {
			return new \WP_Error( 'token_expired', __( 'Download link has expired.', 'digidownloads' ) );
		}

		// Check download count
		if ( $token_data->download_count >= $token_data->max_downloads ) {
			return new \WP_Error( 'max_downloads_reached', __( 'Maximum download limit reached.', 'digidownloads' ) );
		}

		return $token_data;
	}

	public static function increment_download_count( $token ) {
		global $wpdb;

		$table = $wpdb->prefix . 'digidownloads_download_tokens';

		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE $table SET download_count = download_count + 1 WHERE token = %s",
				$token
			)
		);

		do_action( 'digidownloads_download_counted', $token );

		return $updated !== false;
	}

	public static function get_download_url( $token ) {
		return add_query_arg( array( 'dd_download' => $token ), home_url( '/' ) );
	}

	public static function serve_file( $token ) {
		$token_data = self::verify_token( $token );

		if ( is_wp_error( $token_data ) ) {
			wp_die( esc_html( $token_data->get_error_message() ), esc_html__( 'Download Error', 'digidownloads' ), array( 'response' => 403 ) );
		}

		$product = Product::get( $token_data->product_id );

		if ( ! $product || empty( $product->file_path ) || ! file_exists( $product->file_path ) ) {
			wp_die( esc_html__( 'File not found.', 'digidownloads' ), esc_html__( 'Download Error', 'digidownloads' ), array( 'response' => 404 ) );
		}

		// Increment download count
		self::increment_download_count( $token );

		// Serve file
		$file_path = $product->file_path;
		$file_name = $product->file_name;

		// Clear any output buffers
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		// Set headers
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Expires: 0' );

		// Read file
		readfile( $file_path );

		do_action( 'digidownloads_file_served', $token, $product->id );

		exit;
	}

	public static function get_tokens_by_order( $order_id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'digidownloads_download_tokens';
		$tokens = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE order_id = %s ORDER BY created_at DESC", $order_id ) );

		return $tokens;
	}

	public static function delete_expired_tokens() {
		global $wpdb;

		$table = $wpdb->prefix . 'digidownloads_download_tokens';
		$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE expires_at < %s", gmdate( 'Y-m-d H:i:s' ) ) );

		return $deleted !== false ? $deleted : 0;
	}
}
