<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="digidownloads-products-list" data-columns="<?php echo esc_attr( $columns ); ?>">
	<?php foreach ( $products as $product ) : ?>
		<div class="digidownloads-product-item" data-product-id="<?php echo esc_attr( $product->id ); ?>">
			<div class="product-item-inner">
				<h3 class="product-item-title"><?php echo esc_html( $product->name ); ?></h3>
				
				<?php if ( ! empty( $product->description ) ) : ?>
					<div class="product-item-description">
						<?php echo wp_kses_post( wpautop( $product->description ) ); ?>
					</div>
				<?php endif; ?>
				
				<div class="product-item-footer">
					<span class="product-item-price">
						<?php echo esc_html( \DigiDownloads\Currency::format_price( $product->price ) ); ?>
					</span>
					<button type="button" class="product-item-button button digidownloads-button digidownloads-buy-now" data-product-id="<?php echo esc_attr( $product->id ); ?>">
						<?php esc_html_e( 'Buy Now', 'digidownloads' ); ?>
					</button>
				</div>
				
				<!-- Checkout form container (hidden by default) -->
				<div class="product-checkout-container" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 2px solid #0073aa;">
					<?php 
					$settings = get_option( 'digidownloads_settings', array() );
					include DIGIDOWNLOADS_PLUGIN_DIR . 'public/views/checkout-inline.php';
					?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
