<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'DigiDownloads Settings', 'digidownloads' ); ?></h1>

	<?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'settings_saved' ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved successfully.', 'digidownloads' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=digidownloads-settings&action=save_settings' ) ); ?>">
		<?php wp_nonce_field( 'digidownloads_save_settings', 'digidownloads_settings_nonce' ); ?>

		<h2><?php esc_html_e( 'General Settings', 'digidownloads' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="currency"><?php esc_html_e( 'Currency', 'digidownloads' ); ?></label>
				</th>
				<td>
					<select name="currency" id="currency">
						<option value="USD" <?php selected( $settings['currency'], 'USD' ); ?>>USD - US Dollar ($)</option>
						<option value="EUR" <?php selected( $settings['currency'], 'EUR' ); ?>>EUR - Euro (€)</option>
						<option value="GBP" <?php selected( $settings['currency'], 'GBP' ); ?>>GBP - British Pound (£)</option>
						<option value="AUD" <?php selected( $settings['currency'], 'AUD' ); ?>>AUD - Australian Dollar ($)</option>
						<option value="CAD" <?php selected( $settings['currency'], 'CAD' ); ?>>CAD - Canadian Dollar ($)</option>
						<option value="INR" <?php selected( $settings['currency'], 'INR' ); ?>>INR - Indian Rupee (₹)</option>
						<option value="JPY" <?php selected( $settings['currency'], 'JPY' ); ?>>JPY - Japanese Yen (¥)</option>
						<option value="CNY" <?php selected( $settings['currency'], 'CNY' ); ?>>CNY - Chinese Yuan (¥)</option>
						<option value="BRL" <?php selected( $settings['currency'], 'BRL' ); ?>>BRL - Brazilian Real (R$)</option>
						<option value="MXN" <?php selected( $settings['currency'], 'MXN' ); ?>>MXN - Mexican Peso ($)</option>
						<option value="ZAR" <?php selected( $settings['currency'], 'ZAR' ); ?>>ZAR - South African Rand (R)</option>
						<option value="SGD" <?php selected( $settings['currency'], 'SGD' ); ?>>SGD - Singapore Dollar ($)</option>
						<option value="NZD" <?php selected( $settings['currency'], 'NZD' ); ?>>NZD - New Zealand Dollar ($)</option>
						<option value="CHF" <?php selected( $settings['currency'], 'CHF' ); ?>>CHF - Swiss Franc (CHF)</option>
						<option value="SEK" <?php selected( $settings['currency'], 'SEK' ); ?>>SEK - Swedish Krona (kr)</option>
						<option value="NOK" <?php selected( $settings['currency'], 'NOK' ); ?>>NOK - Norwegian Krone (kr)</option>
						<option value="DKK" <?php selected( $settings['currency'], 'DKK' ); ?>>DKK - Danish Krone (kr)</option>
					</select>
					<p class="description"><?php esc_html_e( 'Select the currency for your products', 'digidownloads' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="payment_gateway"><?php esc_html_e( 'Payment Gateway', 'digidownloads' ); ?></label>
				</th>
				<td>
					<select name="payment_gateway" id="payment_gateway" onchange="toggleGatewaySettings(this.value)">
						<option value="stripe" <?php selected( $settings['payment_gateway'], 'stripe' ); ?>>Stripe</option>
						<option value="paypal" <?php selected( $settings['payment_gateway'], 'paypal' ); ?>>PayPal</option>
						<option value="razorpay" <?php selected( $settings['payment_gateway'], 'razorpay' ); ?>>Razorpay</option>
					</select>
					<p class="description"><?php esc_html_e( 'Select your preferred payment gateway', 'digidownloads' ); ?></p>
				</td>
			</tr>
		</table>

		<h2 class="gateway-settings gateway-stripe"><?php esc_html_e( 'Stripe Settings', 'digidownloads' ); ?></h2>
		<table class="form-table gateway-settings gateway-stripe">
			<tr>
				<th scope="row">
					<label for="stripe_publishable_key"><?php esc_html_e( 'Stripe Publishable Key', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="text" name="stripe_publishable_key" id="stripe_publishable_key" class="regular-text" value="<?php echo esc_attr( $settings['stripe_publishable_key'] ); ?>">
					<p class="description"><?php esc_html_e( 'Your Stripe publishable key (pk_test_... or pk_live_...)', 'digidownloads' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="stripe_secret_key"><?php esc_html_e( 'Stripe Secret Key', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="password" name="stripe_secret_key" id="stripe_secret_key" class="regular-text" value="<?php echo esc_attr( $settings['stripe_secret_key'] ); ?>">
					<p class="description"><?php esc_html_e( 'Your Stripe secret key (sk_test_... or sk_live_...)', 'digidownloads' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="stripe_webhook_secret"><?php esc_html_e( 'Stripe Webhook Secret', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="password" name="stripe_webhook_secret" id="stripe_webhook_secret" class="regular-text" value="<?php echo esc_attr( $settings['stripe_webhook_secret'] ); ?>">
					<p class="description"><?php esc_html_e( 'Your Stripe webhook signing secret (whsec_...)', 'digidownloads' ); ?></p>
					<p class="description"><?php echo sprintf( esc_html__( 'Webhook URL: %s', 'digidownloads' ), '<code>' . esc_url( home_url( '/?digidownloads_webhook=stripe' ) ) . '</code>' ); ?></p>
				</td>
			</tr>
		</table>

		<h2 class="gateway-settings gateway-paypal"><?php esc_html_e( 'PayPal Settings', 'digidownloads' ); ?></h2>
		<table class="form-table gateway-settings gateway-paypal">
			<tr>
				<th scope="row">
					<label for="paypal_client_id"><?php esc_html_e( 'PayPal Client ID', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="text" name="paypal_client_id" id="paypal_client_id" class="regular-text" value="<?php echo esc_attr( $settings['paypal_client_id'] ); ?>">
					<p class="description"><?php esc_html_e( 'Your PayPal REST API Client ID', 'digidownloads' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="paypal_secret"><?php esc_html_e( 'PayPal Secret', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="password" name="paypal_secret" id="paypal_secret" class="regular-text" value="<?php echo esc_attr( $settings['paypal_secret'] ); ?>">
					<p class="description"><?php esc_html_e( 'Your PayPal REST API Secret', 'digidownloads' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="paypal_mode"><?php esc_html_e( 'PayPal Mode', 'digidownloads' ); ?></label>
				</th>
				<td>
					<select name="paypal_mode" id="paypal_mode">
						<option value="sandbox" <?php selected( $settings['paypal_mode'], 'sandbox' ); ?>><?php esc_html_e( 'Sandbox (Test)', 'digidownloads' ); ?></option>
						<option value="live" <?php selected( $settings['paypal_mode'], 'live' ); ?>><?php esc_html_e( 'Live (Production)', 'digidownloads' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Use sandbox mode for testing', 'digidownloads' ); ?></p>
				</td>
			</tr>
		</table>

		<h2 class="gateway-settings gateway-razorpay"><?php esc_html_e( 'Razorpay Settings', 'digidownloads' ); ?></h2>
		<table class="form-table gateway-settings gateway-razorpay">
			<tr>
				<th scope="row">
					<label for="razorpay_key_id"><?php esc_html_e( 'Razorpay Key ID', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="text" name="razorpay_key_id" id="razorpay_key_id" class="regular-text" value="<?php echo esc_attr( $settings['razorpay_key_id'] ); ?>">
					<p class="description"><?php esc_html_e( 'Your Razorpay Key ID', 'digidownloads' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="razorpay_key_secret"><?php esc_html_e( 'Razorpay Key Secret', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="password" name="razorpay_key_secret" id="razorpay_key_secret" class="regular-text" value="<?php echo esc_attr( $settings['razorpay_key_secret'] ); ?>">
					<p class="description"><?php esc_html_e( 'Your Razorpay Key Secret', 'digidownloads' ); ?></p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Download Settings', 'digidownloads' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="download_expiry_hours"><?php esc_html_e( 'Download Link Expiry (Hours)', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="number" name="download_expiry_hours" id="download_expiry_hours" class="small-text" value="<?php echo esc_attr( $settings['download_expiry_hours'] ); ?>" min="1">
					<p class="description"><?php esc_html_e( 'Number of hours before download links expire', 'digidownloads' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="max_downloads"><?php esc_html_e( 'Maximum Downloads', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="number" name="max_downloads" id="max_downloads" class="small-text" value="<?php echo esc_attr( $settings['max_downloads'] ); ?>" min="1">
					<p class="description"><?php esc_html_e( 'Maximum number of times a file can be downloaded per purchase', 'digidownloads' ); ?></p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Email Settings', 'digidownloads' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="from_name"><?php esc_html_e( 'From Name', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="text" name="from_name" id="from_name" class="regular-text" value="<?php echo esc_attr( $settings['from_name'] ); ?>">
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="from_email"><?php esc_html_e( 'From Email', 'digidownloads' ); ?></label>
				</th>
				<td>
					<input type="email" name="from_email" id="from_email" class="regular-text" value="<?php echo esc_attr( $settings['from_email'] ); ?>">
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'digidownloads' ); ?>">
		</p>
	</form>
</div>

<script>
function toggleGatewaySettings(gateway) {
	// Hide all gateway settings (both headings and tables)
	document.querySelectorAll('.gateway-settings').forEach(function(el) {
		el.style.display = 'none';
	});
	
	// Show selected gateway settings
	document.querySelectorAll('.gateway-' + gateway).forEach(function(el) {
		if (el.tagName === 'H2') {
			el.style.display = 'block';
		} else if (el.tagName === 'TABLE') {
			el.style.display = 'table';
		}
	});
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
	var gatewaySelect = document.getElementById('payment_gateway');
	if (gatewaySelect) {
		toggleGatewaySettings(gatewaySelect.value);
	}
});
</script>

<style>
.gateway-settings {
	display: none;
}
</style>
