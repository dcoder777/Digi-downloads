<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gateway = isset( $settings['payment_gateway'] ) ? $settings['payment_gateway'] : 'stripe';
$stripe_pk = ! empty( $settings['stripe_publishable_key'] ) ? $settings['stripe_publishable_key'] : '';
?>

<div class="digidownloads-checkout">
	<div class="checkout-product-info">
		<h2><?php echo esc_html( $product->name ); ?></h2>
		<div class="checkout-price">
			<?php echo esc_html( \DigiDownloads\Currency::format_price( $product->price ) ); ?>
		</div>
	</div>

	<form id="digidownloads-checkout-form" class="checkout-form">
		<div class="form-field">
			<label for="buyer_email"><?php esc_html_e( 'Email Address', 'digidownloads' ); ?> <span class="required">*</span></label>
			<input type="email" name="buyer_email" id="buyer_email" required>
			<p class="field-description"><?php esc_html_e( 'Your download link will be sent to this email', 'digidownloads' ); ?></p>
		</div>

		<?php if ( $gateway === 'stripe' ) : ?>
		<div id="card-element" class="form-field"></div>
		<?php endif; ?>
		
		<div id="card-errors" role="alert" class="payment-errors"></div>

		<button type="submit" id="submit-payment" class="button digidownloads-button">
			<?php 
			if ( $gateway === 'paypal' ) {
				esc_html_e( 'Proceed to PayPal', 'digidownloads' );
			} elseif ( $gateway === 'razorpay' ) {
				esc_html_e( 'Proceed to Payment', 'digidownloads' );
			} else {
				esc_html_e( 'Complete Purchase', 'digidownloads' );
			}
			?>
		</button>

		<div id="payment-processing" class="payment-processing" style="display: none;">
			<?php esc_html_e( 'Processing payment...', 'digidownloads' ); ?>
		</div>
	</form>

	<div id="payment-success" class="payment-result success" style="display: none;">
		<h3><?php esc_html_e( 'Payment Successful!', 'digidownloads' ); ?></h3>
		<p><?php esc_html_e( 'Your purchase is complete. Check your email for the download link.', 'digidownloads' ); ?></p>
	</div>

	<div id="payment-error" class="payment-result error" style="display: none;">
		<h3><?php esc_html_e( 'Payment Failed', 'digidownloads' ); ?></h3>
		<p id="error-message"></p>
	</div>
</div>

<?php if ( $gateway === 'stripe' && ! empty( $stripe_pk ) ) : ?>
<script src="https://js.stripe.com/v3/"></script>
<script>
	var digidownloadsStripeKey = '<?php echo esc_js( $stripe_pk ); ?>';
	var digidownloadsProductId = <?php echo absint( $product->id ); ?>;
</script>
<?php endif; ?>
