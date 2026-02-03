<?php
namespace DigiDownloads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Security {

	public static function verify_nonce( $nonce, $action ) {
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die( esc_html__( 'Security check failed.', 'digidownloads' ), esc_html__( 'Security Error', 'digidownloads' ), array( 'response' => 403 ) );
		}
	}

	public static function check_admin_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'digidownloads' ), esc_html__( 'Permission Denied', 'digidownloads' ), array( 'response' => 403 ) );
		}
	}

	public static function sanitize_product_data( $data ) {
		$sanitized = array();

		if ( isset( $data['name'] ) ) {
			$sanitized['name'] = sanitize_text_field( $data['name'] );
		}

		if ( isset( $data['description'] ) ) {
			$sanitized['description'] = wp_kses_post( $data['description'] );
		}

		if ( isset( $data['price'] ) ) {
			$sanitized['price'] = floatval( $data['price'] );
		}

		if ( isset( $data['status'] ) ) {
			$sanitized['status'] = in_array( $data['status'], array( 'active', 'inactive' ) ) ? $data['status'] : 'active';
		}

		if ( isset( $data['file_path'] ) ) {
			$sanitized['file_path'] = sanitize_text_field( $data['file_path'] );
		}

		if ( isset( $data['file_name'] ) ) {
			$sanitized['file_name'] = sanitize_file_name( $data['file_name'] );
		}

		return $sanitized;
	}

	public static function validate_email( $email ) {
		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'invalid_email', __( 'Invalid email address.', 'digidownloads' ) );
		}
		return $email;
	}

	public static function validate_product_id( $product_id ) {
		$product_id = absint( $product_id );
		if ( ! $product_id ) {
			return new \WP_Error( 'invalid_product', __( 'Invalid product ID.', 'digidownloads' ) );
		}

		$product = Product::get( $product_id );
		if ( ! $product ) {
			return new \WP_Error( 'product_not_found', __( 'Product not found.', 'digidownloads' ) );
		}

		if ( $product->status !== 'active' ) {
			return new \WP_Error( 'product_inactive', __( 'This product is not available for purchase.', 'digidownloads' ) );
		}

		return $product;
	}

	public static function prevent_direct_access() {
		if ( ! defined( 'ABSPATH' ) ) {
			exit;
		}
	}

	public static function rate_limit_check( $key, $limit = 10, $period = 60 ) {
		$transient_key = 'digidownloads_rate_limit_' . md5( $key );
		$attempts = get_transient( $transient_key );

		if ( false === $attempts ) {
			set_transient( $transient_key, 1, $period );
			return true;
		}

		if ( $attempts >= $limit ) {
			return false;
		}

		set_transient( $transient_key, $attempts + 1, $period );
		return true;
	}

	public static function validate_file_upload( $file ) {
		if ( empty( $file['name'] ) ) {
			return new \WP_Error( 'no_file', __( 'No file was uploaded.', 'digidownloads' ) );
		}

		if ( $file['error'] !== UPLOAD_ERR_OK ) {
			return new \WP_Error( 'upload_error', __( 'File upload failed.', 'digidownloads' ) );
		}

		// Check file size (max 100MB)
		$max_size = 100 * 1024 * 1024;
		if ( $file['size'] > $max_size ) {
			return new \WP_Error( 'file_too_large', __( 'File is too large. Maximum size is 100MB.', 'digidownloads' ) );
		}

		// Validate file extension
		$allowed_extensions = apply_filters( 'digidownloads_allowed_file_extensions', array(
			'zip', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
			'jpg', 'jpeg', 'png', 'gif', 'svg', 'mp3', 'mp4', 'avi',
			'txt', 'csv', 'xml', 'json'
		) );

		$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		
		if ( ! in_array( $file_extension, $allowed_extensions ) ) {
			return new \WP_Error( 'invalid_file_type', __( 'This file type is not allowed.', 'digidownloads' ) );
		}

		return true;
	}
}
