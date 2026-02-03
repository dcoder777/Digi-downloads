<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Shortcodes', 'digidownloads' ); ?></h1>
	
	<div class="shortcode-info-box" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin: 20px 0;">
		<h2><?php esc_html_e( 'How to Use Shortcodes', 'digidownloads' ); ?></h2>
		<p><?php esc_html_e( 'Copy and paste these shortcodes into any WordPress page or post to display your products.', 'digidownloads' ); ?></p>
	</div>

	<div class="shortcode-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin: 20px 0;">
		<h2><?php esc_html_e( '1. Product Checkout (Recommended)', 'digidownloads' ); ?></h2>
		<p><?php esc_html_e( 'Display a complete checkout form with product info, email field, and payment form.', 'digidownloads' ); ?></p>
		
		<div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa; margin: 15px 0;">
			<strong><?php esc_html_e( 'Shortcode Format:', 'digidownloads' ); ?></strong>
			<code style="display: block; margin: 10px 0; padding: 10px; background: #fff; border: 1px solid #ddd;">[digidownloads_checkout id="PRODUCT_ID"]</code>
			
			<p style="margin-top: 15px;"><strong><?php esc_html_e( 'Example:', 'digidownloads' ); ?></strong></p>
			<code style="display: block; margin: 10px 0; padding: 10px; background: #fff; border: 1px solid #ddd;">[digidownloads_checkout id="1"]</code>
		</div>

		<?php if ( ! empty( $products ) ) : ?>
			<h3><?php esc_html_e( 'Your Products - Copy Shortcode:', 'digidownloads' ); ?></h3>
			<table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'digidownloads' ); ?></th>
						<th><?php esc_html_e( 'Product Name', 'digidownloads' ); ?></th>
						<th><?php esc_html_e( 'Checkout Shortcode', 'digidownloads' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $products as $product ) : ?>
						<tr>
							<td><?php echo esc_html( $product->id ); ?></td>
							<td><strong><?php echo esc_html( $product->name ); ?></strong></td>
							<td>
								<input type="text" readonly value='[digidownloads_checkout id="<?php echo esc_attr( $product->id ); ?>"]' 
									   onclick="this.select();" 
									   style="width: 100%; padding: 5px; font-family: monospace; background: #f0f0f0;">
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="notice notice-info inline" style="margin: 15px 0;">
				<p><?php esc_html_e( 'No products found. Create a product first!', 'digidownloads' ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<div class="shortcode-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin: 20px 0;">
		<h2><?php esc_html_e( '2. Product Display (Info Only)', 'digidownloads' ); ?></h2>
		<p><?php esc_html_e( 'Display product information with a "Buy Now" button that links to your checkout page.', 'digidownloads' ); ?></p>
		
		<div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa; margin: 15px 0;">
			<strong><?php esc_html_e( 'Shortcode Format:', 'digidownloads' ); ?></strong>
			<code style="display: block; margin: 10px 0; padding: 10px; background: #fff; border: 1px solid #ddd;">[digidownloads_product id="PRODUCT_ID"]</code>
			
			<p style="margin-top: 15px;"><strong><?php esc_html_e( 'Example:', 'digidownloads' ); ?></strong></p>
			<code style="display: block; margin: 10px 0; padding: 10px; background: #fff; border: 1px solid #ddd;">[digidownloads_product id="1"]</code>
		</div>

		<?php if ( ! empty( $products ) ) : ?>
			<h3><?php esc_html_e( 'Your Products - Copy Shortcode:', 'digidownloads' ); ?></h3>
			<table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'digidownloads' ); ?></th>
						<th><?php esc_html_e( 'Product Name', 'digidownloads' ); ?></th>
						<th><?php esc_html_e( 'Product Display Shortcode', 'digidownloads' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $products as $product ) : ?>
						<tr>
							<td><?php echo esc_html( $product->id ); ?></td>
							<td><strong><?php echo esc_html( $product->name ); ?></strong></td>
							<td>
								<input type="text" readonly value='[digidownloads_product id="<?php echo esc_attr( $product->id ); ?>"]' 
									   onclick="this.select();" 
									   style="width: 100%; padding: 5px; font-family: monospace; background: #f0f0f0;">
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<div class="shortcode-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin: 20px 0;">
		<h2><?php esc_html_e( '3. Products List (All Products)', 'digidownloads' ); ?></h2>
		<p><?php esc_html_e( 'Display a grid of all active products on one page.', 'digidownloads' ); ?></p>
		
		<div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa; margin: 15px 0;">
			<strong><?php esc_html_e( 'Basic Usage:', 'digidownloads' ); ?></strong>
			<code style="display: block; margin: 10px 0; padding: 10px; background: #fff; border: 1px solid #ddd;">[digidownloads_products]</code>
			
			<p style="margin-top: 15px;"><strong><?php esc_html_e( 'With Options:', 'digidownloads' ); ?></strong></p>
			<code style="display: block; margin: 10px 0; padding: 10px; background: #fff; border: 1px solid #ddd;">[digidownloads_products columns="3" limit="9"]</code>
			
			<p style="margin-top: 15px;"><strong><?php esc_html_e( 'Available Parameters:', 'digidownloads' ); ?></strong></p>
			<ul style="margin-left: 20px; line-height: 1.8;">
				<li><code>columns</code> - <?php esc_html_e( 'Number of columns (1-4, default: 3)', 'digidownloads' ); ?></li>
				<li><code>limit</code> - <?php esc_html_e( 'Maximum number of products to show (default: all)', 'digidownloads' ); ?></li>
			</ul>
			
			<p style="margin-top: 15px;"><strong><?php esc_html_e( 'Examples:', 'digidownloads' ); ?></strong></p>
			<code style="display: block; margin: 5px 0; padding: 10px; background: #fff; border: 1px solid #ddd;">[digidownloads_products columns="2"]</code>
			<code style="display: block; margin: 5px 0; padding: 10px; background: #fff; border: 1px solid #ddd;">[digidownloads_products columns="4" limit="8"]</code>
		</div>
		
		<div style="background: #fff3cd; padding: 10px 15px; margin-top: 15px; border-radius: 4px;">
			<p style="margin: 0;"><strong>💡 Tip:</strong> <?php esc_html_e( 'Best for shop pages or product catalogs. Each product shows name, description, price, and a buy button.', 'digidownloads' ); ?></p>
		</div>
	</div>

	<div class="shortcode-section" style="background: #fff3cd; padding: 20px; border: 1px solid #ffc107; border-radius: 4px; margin: 20px 0;">
		<h2><?php esc_html_e( '📝 Usage Instructions', 'digidownloads' ); ?></h2>
		<ol style="line-height: 2;">
			<li><?php esc_html_e( 'Create a new Page in WordPress (Pages → Add New)', 'digidownloads' ); ?></li>
			<li><?php esc_html_e( 'Copy the shortcode from the table above', 'digidownloads' ); ?></li>
			<li><?php esc_html_e( 'Paste it into your page content', 'digidownloads' ); ?></li>
			<li><?php esc_html_e( 'Publish the page', 'digidownloads' ); ?></li>
			<li><?php esc_html_e( 'Visit the page to see your product!', 'digidownloads' ); ?></li>
		</ol>
	</div>

	<div class="shortcode-section" style="background: #e7f3ff; padding: 20px; border: 1px solid #0073aa; border-radius: 4px; margin: 20px 0;">
		<h2><?php esc_html_e( '⚙️ Before You Start', 'digidownloads' ); ?></h2>
		<ul style="line-height: 2;">
			<li>
				<strong><?php esc_html_e( 'Configure Stripe:', 'digidownloads' ); ?></strong> 
				<?php echo sprintf( 
					/* translators: %s: Settings page link */
					__( 'Add your Stripe API keys in %s', 'digidownloads' ), 
					'<a href="' . esc_url( admin_url( 'admin.php?page=digidownloads-settings' ) ) . '">' . esc_html__( 'Settings', 'digidownloads' ) . '</a>' 
				); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Test Card:', 'digidownloads' ); ?></strong> 
				<?php esc_html_e( 'Use 4242 4242 4242 4242 for testing', 'digidownloads' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Set up Webhook:', 'digidownloads' ); ?></strong> 
				<?php esc_html_e( 'Required for email delivery and downloads', 'digidownloads' ); ?>
			</li>
		</ul>
	</div>
</div>

<style>
.shortcode-section h2 {
	margin-top: 0;
	font-size: 20px;
}
.shortcode-section code {
	font-size: 14px;
	color: #0073aa;
}
.shortcode-section input[type="text"] {
	cursor: pointer;
}
.shortcode-section input[type="text"]:focus {
	background: #fff;
	border-color: #0073aa;
}
</style>
