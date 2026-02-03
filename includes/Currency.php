<?php
namespace DigiDownloads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Currency {

	private static $currency_symbols = array(
		'USD' => '$',
		'EUR' => '€',
		'GBP' => '£',
		'AUD' => '$',
		'CAD' => '$',
		'INR' => '₹',
		'JPY' => '¥',
		'CNY' => '¥',
		'BRL' => 'R$',
		'MXN' => '$',
		'ZAR' => 'R',
		'SGD' => '$',
		'NZD' => '$',
		'CHF' => 'CHF',
		'SEK' => 'kr',
		'NOK' => 'kr',
		'DKK' => 'kr',
	);

	public static function get_currency() {
		$settings = get_option( 'digidownloads_settings', array( 'currency' => 'USD' ) );
		return ! empty( $settings['currency'] ) ? $settings['currency'] : 'USD';
	}

	public static function get_symbol( $currency = null ) {
		if ( ! $currency ) {
			$currency = self::get_currency();
		}

		return isset( self::$currency_symbols[ $currency ] ) ? self::$currency_symbols[ $currency ] : $currency;
	}

	public static function format_price( $price, $currency = null ) {
		if ( ! $currency ) {
			$currency = self::get_currency();
		}

		$symbol = self::get_symbol( $currency );
		$formatted_price = number_format( floatval( $price ), 2 );

		// Symbol position based on currency
		$symbol_after = array( 'SEK', 'NOK', 'DKK' );
		
		if ( in_array( $currency, $symbol_after ) ) {
			return $formatted_price . ' ' . $symbol;
		}

		return $symbol . $formatted_price;
	}

	public static function get_all_currencies() {
		return array(
			'USD' => 'US Dollar ($)',
			'EUR' => 'Euro (€)',
			'GBP' => 'British Pound (£)',
			'AUD' => 'Australian Dollar ($)',
			'CAD' => 'Canadian Dollar ($)',
			'INR' => 'Indian Rupee (₹)',
			'JPY' => 'Japanese Yen (¥)',
			'CNY' => 'Chinese Yuan (¥)',
			'BRL' => 'Brazilian Real (R$)',
			'MXN' => 'Mexican Peso ($)',
			'ZAR' => 'South African Rand (R)',
			'SGD' => 'Singapore Dollar ($)',
			'NZD' => 'New Zealand Dollar ($)',
			'CHF' => 'Swiss Franc (CHF)',
			'SEK' => 'Swedish Krona (kr)',
			'NOK' => 'Norwegian Krone (kr)',
			'DKK' => 'Danish Krone (kr)',
		);
	}
}
