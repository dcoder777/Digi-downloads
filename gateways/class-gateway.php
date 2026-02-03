<?php
namespace DigiDownloads\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Gateway {

	protected $id;
	protected $name;
	protected $settings;

	public function __construct() {
		$this->init();
	}

	abstract protected function init();
	abstract public function process_payment( $order_id, $data );
	abstract public function handle_webhook();

	protected function get_settings() {
		$all_settings = get_option( 'digidownloads_settings', array() );
		return $all_settings;
	}

	protected function log( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			error_log( '[DigiDownloads ' . $this->name . '] ' . $message );
		}
	}
}
