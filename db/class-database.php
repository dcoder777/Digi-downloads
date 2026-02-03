<?php
namespace DigiDownloads\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Database {

	public static function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Products table
		$products_table = $wpdb->prefix . 'digidownloads_products';
		$products_sql = "CREATE TABLE $products_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			description longtext,
			price decimal(10,2) NOT NULL DEFAULT '0.00',
			file_path varchar(500) DEFAULT NULL,
			file_name varchar(255) DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status)
		) $charset_collate;";

		// Orders table
		$orders_table = $wpdb->prefix . 'digidownloads_orders';
		$orders_sql = "CREATE TABLE $orders_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			order_id varchar(100) NOT NULL,
			product_id bigint(20) unsigned NOT NULL,
			buyer_email varchar(255) NOT NULL,
			amount decimal(10,2) NOT NULL DEFAULT '0.00',
			payment_status varchar(50) NOT NULL DEFAULT 'pending',
			payment_gateway varchar(50) NOT NULL DEFAULT 'stripe',
			gateway_transaction_id varchar(255) DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY order_id (order_id),
			KEY product_id (product_id),
			KEY buyer_email (buyer_email),
			KEY payment_status (payment_status)
		) $charset_collate;";

		// Download tokens table
		$tokens_table = $wpdb->prefix . 'digidownloads_download_tokens';
		$tokens_sql = "CREATE TABLE $tokens_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			token varchar(64) NOT NULL,
			order_id varchar(100) NOT NULL,
			product_id bigint(20) unsigned NOT NULL,
			download_count int(11) NOT NULL DEFAULT 0,
			max_downloads int(11) NOT NULL DEFAULT 5,
			expires_at datetime NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY token (token),
			KEY order_id (order_id),
			KEY expires_at (expires_at)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $products_sql );
		dbDelta( $orders_sql );
		dbDelta( $tokens_sql );

		update_option( 'digidownloads_db_version', DIGIDOWNLOADS_VERSION );
	}

	public static function get_products_table() {
		global $wpdb;
		return $wpdb->prefix . 'digidownloads_products';
	}

	public static function get_orders_table() {
		global $wpdb;
		return $wpdb->prefix . 'digidownloads_orders';
	}

	public static function get_tokens_table() {
		global $wpdb;
		return $wpdb->prefix . 'digidownloads_download_tokens';
	}
}
