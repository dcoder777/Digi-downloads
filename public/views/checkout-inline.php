<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gateway = isset( $settings['payment_gateway'] ) ? $settings['payment_gateway'] : 'stripe';
$stripe_pk = ! empty( $settings['stripe_publishable_key'] ) ? $settings['stripe_publishable_key'] : '';
?>

<form class="digidownloads-checkout-form checkout-form">
	<div class="form-field">
		<label for="buyer_email_<?php echo esc_attr( $product->id ); ?>"><?php esc_html_e( 'Email Address', 'digidownloads' ); ?> <span class="required">*</span></label>
		<input type="email" name="buyer_email" id="buyer_email_<?php echo esc_attr( $product->id ); ?>" class="buyer-email-input" required>
		<p class="field-description"><?php esc_html_e( 'Your download link will be sent to this email', 'digidownloads' ); ?></p>
	</div>

	<?php if ( $gateway === 'stripe' ) : ?>
	<div id="card-element-<?php echo esc_attr( $product->id ); ?>" class="form-field card-element-inline"></div>
	<?php endif; ?>
	
	<div class="card-errors payment-errors" role="alert"></div>

	<input type="hidden" name="product_id" value="<?php echo esc_attr( $product->id ); ?>">

	<button type="submit" class="button digidownloads-button submit-payment">
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

	<div class="payment-processing" style="display: none;">
		<?php esc_html_e( 'Processing payment...', 'digidownloads' ); ?>
	</div>
</form>

<div class="payment-success payment-result success" style="display: none;">
	<h3><?php esc_html_e( 'Payment Successful!', 'digidownloads' ); ?></h3>
	<p><?php esc_html_e( 'Your purchase is complete. Check your email for the download link.', 'digidownloads' ); ?></p>
</div>
