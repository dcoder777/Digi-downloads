<?php
namespace DigiDownloads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product {

	public static function create( $data ) {
		global $wpdb;

		$defaults = array(
			'name' => '',
			'description' => '',
			'price' => 0.00,
			'file_path' => null,
			'file_name' => null,
			'status' => 'active',
		);

		$data = wp_parse_args( $data, $defaults );

		// Validate
		if ( empty( $data['name'] ) ) {
			return new \WP_Error( 'invalid_name', __( 'Product name is required.', 'digidownloads' ) );
		}

		$table = $wpdb->prefix . 'digidownloads_products';

		$inserted = $wpdb->insert(
			$table,
			array(
				'name' => sanitize_text_field( $data['name'] ),
				'description' => wp_kses_post( $data['description'] ),
				'price' => floatval( $data['price'] ),
				'file_path' => ! empty( $data['file_path'] ) ? sanitize_text_field( $data['file_path'] ) : null,
				'file_name' => ! empty( $data['file_name'] ) ? sanitize_file_name( $data['file_name'] ) : null,
				'status' => in_array( $data['status'], array( 'active', 'inactive' ) ) ? $data['status'] : 'active',
			),
			array( '%s', '%s', '%f', '%s', '%s', '%s' )
		);

		if ( $inserted === false ) {
			return new \WP_Error( 'db_error', __( 'Failed to create product.', 'digidownloads' ) );
		}

		do_action( 'digidownloads_product_created', $wpdb->insert_id );

		return $wpdb->insert_id;
	}

	public static function update( $id, $data ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return new \WP_Error( 'invalid_id', __( 'Invalid product ID.', 'digidownloads' ) );
		}

		$table = $wpdb->prefix . 'digidownloads_products';

		$update_data = array();
		$update_format = array();

		if ( isset( $data['name'] ) ) {
			$update_data['name'] = sanitize_text_field( $data['name'] );
			$update_format[] = '%s';
		}

		if ( isset( $data['description'] ) ) {
			$update_data['description'] = wp_kses_post( $data['description'] );
			$update_format[] = '%s';
		}

		if ( isset( $data['price'] ) ) {
			$update_data['price'] = floatval( $data['price'] );
			$update_format[] = '%f';
		}

		if ( isset( $data['file_path'] ) ) {
			$update_data['file_path'] = ! empty( $data['file_path'] ) ? sanitize_text_field( $data['file_path'] ) : null;
			$update_format[] = '%s';
		}

		if ( isset( $data['file_name'] ) ) {
			$update_data['file_name'] = ! empty( $data['file_name'] ) ? sanitize_file_name( $data['file_name'] ) : null;
			$update_format[] = '%s';
		}

		if ( isset( $data['status'] ) ) {
			$update_data['status'] = in_array( $data['status'], array( 'active', 'inactive' ) ) ? $data['status'] : 'active';
			$update_format[] = '%s';
		}

		if ( empty( $update_data ) ) {
			return new \WP_Error( 'no_data', __( 'No data to update.', 'digidownloads' ) );
		}

		$updated = $wpdb->update(
			$table,
			$update_data,
			array( 'id' => $id ),
			$update_format,
			array( '%d' )
		);

		if ( $updated === false ) {
			return new \WP_Error( 'db_error', __( 'Failed to update product.', 'digidownloads' ) );
		}

		do_action( 'digidownloads_product_updated', $id );

		return true;
	}

	public static function get( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return null;
		}

		$table = $wpdb->prefix . 'digidownloads_products';
		$product = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );

		return $product;
	}

	public static function get_all( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status' => 'active',
			'orderby' => 'created_at',
			'order' => 'DESC',
			'limit' => 50,
			'offset' => 0,
		);

		$args = wp_parse_args( $args, $defaults );
		$table = $wpdb->prefix . 'digidownloads_products';

		$where = '';
		if ( ! empty( $args['status'] ) && $args['status'] !== 'all' ) {
			$where = $wpdb->prepare( ' WHERE status = %s', $args['status'] );
		}

		$orderby = in_array( $args['orderby'], array( 'id', 'name', 'price', 'created_at' ) ) ? $args['orderby'] : 'created_at';
		$order = $args['order'] === 'ASC' ? 'ASC' : 'DESC';
		$limit = absint( $args['limit'] );
		$offset = absint( $args['offset'] );

		$sql = "SELECT * FROM $table $where ORDER BY $orderby $order LIMIT $limit OFFSET $offset";
		$products = $wpdb->get_results( $sql );

		return $products;
	}

	public static function delete( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return new \WP_Error( 'invalid_id', __( 'Invalid product ID.', 'digidownloads' ) );
		}

		$product = self::get( $id );
		if ( ! $product ) {
			return new \WP_Error( 'not_found', __( 'Product not found.', 'digidownloads' ) );
		}

		// Delete file
		if ( ! empty( $product->file_path ) && file_exists( $product->file_path ) ) {
			wp_delete_file( $product->file_path );
		}

		$table = $wpdb->prefix . 'digidownloads_products';
		$deleted = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );

		if ( $deleted === false ) {
			return new \WP_Error( 'db_error', __( 'Failed to delete product.', 'digidownloads' ) );
		}

		do_action( 'digidownloads_product_deleted', $id );

		return true;
	}

	public static function count( $status = 'all' ) {
		global $wpdb;

		$table = $wpdb->prefix . 'digidownloads_products';

		if ( $status === 'all' ) {
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		} else {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", $status ) );
		}

		return absint( $count );
	}
}
