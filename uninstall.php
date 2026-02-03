<?php
/**
 * Uninstall script for DigiDownloads
 * This file is called when the plugin is uninstalled
 */

// Exit if not called from WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete custom tables
$tables = array(
	$wpdb->prefix . 'digidownloads_products',
	$wpdb->prefix . 'digidownloads_orders',
	$wpdb->prefix . 'digidownloads_download_tokens',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS $table" );
}

// Delete options
delete_option( 'digidownloads_settings' );
delete_option( 'digidownloads_db_version' );

// Delete uploaded files (optional - commented out for safety)
// Uncomment if you want to delete all product files on uninstall
/*
$upload_dir = wp_upload_dir();
$digidownloads_dir = $upload_dir['basedir'] . '/digidownloads';
if ( file_exists( $digidownloads_dir ) ) {
	$files = glob( $digidownloads_dir . '/*' );
	foreach ( $files as $file ) {
		if ( is_file( $file ) ) {
			unlink( $file );
		}
	}
	rmdir( $digidownloads_dir );
}
*/

// Clear any cached data
wp_cache_flush();
