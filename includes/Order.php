<?php
namespace DigiDownloads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Order {

	public static function create( $data ) {
		global $wpdb;

		$defaults = array(
			'order_id' => '',
			'product_id' => 0,
			'buyer_email' => '',
			'amount' => 0.00,
			'payment_status' => 'pending',
			'payment_gateway' => 'stripe',
			'gateway_transaction_id' => null,
		);

		$data = wp_parse_args( $data, $defaults );

		// Validate
		if ( empty( $data['order_id'] ) ) {
			$data['order_id'] = 'DD-' . strtoupper( wp_generate_password( 12, false ) );
		}

		if ( ! is_email( $data['buyer_email'] ) ) {
			return new \WP_Error( 'invalid_email', __( 'Invalid email address.', 'digidownloads' ) );
		}

		if ( ! $data['product_id'] ) {
			return new \WP_Error( 'invalid_product', __( 'Invalid product ID.', 'digidownloads' ) );
		}

		$table = $wpdb->prefix . 'digidownloads_orders';

		$inserted = $wpdb->insert(
			$table,
			array(
				'order_id' => sanitize_text_field( $data['order_id'] ),
				'product_id' => absint( $data['product_id'] ),
				'buyer_email' => sanitize_email( $data['buyer_email'] ),
				'amount' => floatval( $data['amount'] ),
				'payment_status' => sanitize_text_field( $data['payment_status'] ),
				'payment_gateway' => sanitize_text_field( $data['payment_gateway'] ),
				'gateway_transaction_id' => sanitize_text_field( $data['gateway_transaction_id'] ),
			),
			array( '%s', '%d', '%s', '%f', '%s', '%s', '%s' )
		);

		if ( $inserted === false ) {
			return new \WP_Error( 'db_error', __( 'Failed to create order.', 'digidownloads' ) );
		}

		$order_db_id = $wpdb->insert_id;

		do_action( 'digidownloads_order_created', $order_db_id, $data['order_id'] );

		return $order_db_id;
	}

	public static function update_status( $order_id, $status, $transaction_id = null ) {
		global $wpdb;

		if ( empty( $order_id ) ) {
			return new \WP_Error( 'invalid_order', __( 'Invalid order ID.', 'digidownloads' ) );
		}

		$table = $wpdb->prefix . 'digidownloads_orders';

		$update_data = array(
			'payment_status' => sanitize_text_field( $status ),
		);

		$update_format = array( '%s' );

		if ( ! empty( $transaction_id ) ) {
			$update_data['gateway_transaction_id'] = sanitize_text_field( $transaction_id );
			$update_format[] = '%s';
		}

		$updated = $wpdb->update(
			$table,
			$update_data,
			array( 'order_id' => $order_id ),
			$update_format,
			array( '%s' )
		);

		if ( $updated === false ) {
			return new \WP_Error( 'db_error', __( 'Failed to update order status.', 'digidownloads' ) );
		}

		do_action( 'digidownloads_order_status_updated', $order_id, $status );

		return true;
	}

	public static function get_by_order_id( $order_id ) {
		global $wpdb;

		if ( empty( $order_id ) ) {
			return null;
		}

		$table = $wpdb->prefix . 'digidownloads_orders';
		$order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE order_id = %s", $order_id ) );

		return $order;
	}

	public static function get( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return null;
		}

		$table = $wpdb->prefix . 'digidownloads_orders';
		$order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );

		return $order;
	}

	public static function get_all( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'payment_status' => '',
			'buyer_email' => '',
			'orderby' => 'created_at',
			'order' => 'DESC',
			'limit' => 50,
			'offset' => 0,
		);

		$args = wp_parse_args( $args, $defaults );
		$table = $wpdb->prefix . 'digidownloads_orders';

		$where_clauses = array();

		if ( ! empty( $args['payment_status'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'payment_status = %s', $args['payment_status'] );
		}

		if ( ! empty( $args['buyer_email'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'buyer_email = %s', $args['buyer_email'] );
		}

		$where = '';
		if ( ! empty( $where_clauses ) ) {
			$where = ' WHERE ' . implode( ' AND ', $where_clauses );
		}

		$orderby = in_array( $args['orderby'], array( 'id', 'amount', 'created_at' ) ) ? $args['orderby'] : 'created_at';
		$order = $args['order'] === 'ASC' ? 'ASC' : 'DESC';
		$limit = absint( $args['limit'] );
		$offset = absint( $args['offset'] );

		$sql = "SELECT * FROM $table $where ORDER BY $orderby $order LIMIT $limit OFFSET $offset";
		$orders = $wpdb->get_results( $sql );

		return $orders;
	}

	public static function count( $status = '' ) {
		global $wpdb;

		$table = $wpdb->prefix . 'digidownloads_orders';

		if ( empty( $status ) ) {
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		} else {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE payment_status = %s", $status ) );
		}

		return absint( $count );
	}
}
