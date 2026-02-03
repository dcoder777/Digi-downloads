<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="digidownloads-product digidownloads-product-item" data-product-id="<?php echo esc_attr( $product->id ); ?>">
	<div class="product-header">
		<h2 class="product-title"><?php echo esc_html( $product->name ); ?></h2>
		<div class="product-price">
			<?php echo esc_html( \DigiDownloads\Currency::format_price( $product->price ) ); ?>
		</div>
	</div>

	<div class="product-description">
		<?php echo wp_kses_post( $product->description ); ?>
	</div>

	<div class="product-actions">
		<button type="button" class="button digidownloads-button digidownloads-buy-now" data-product-id="<?php echo esc_attr( $product->id ); ?>">
			<?php esc_html_e( 'Buy Now', 'digidownloads' ); ?>
		</button>
	</div>
	
	<!-- Checkout form container (hidden by default) -->
	<div class="product-checkout-container" style="display: none; margin-top: 30px; padding-top: 30px; border-top: 2px solid #0073aa;">
		<?php 
		$settings = get_option( 'digidownloads_settings', array() );
		include DIGIDOWNLOADS_PLUGIN_DIR . 'public/views/checkout-inline.php';
		?>
	</div>
</div>
