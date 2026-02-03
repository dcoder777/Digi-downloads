<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Orders', 'digidownloads' ); ?></h1>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Order ID', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Product', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Buyer Email', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Amount', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Status', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Gateway', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Date', 'digidownloads' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $orders ) ) : ?>
				<?php foreach ( $orders as $order ) : ?>
					<?php $product = \DigiDownloads\Product::get( $order->product_id ); ?>
					<tr>
						<td><strong><?php echo esc_html( $order->order_id ); ?></strong></td>
						<td><?php echo $product ? esc_html( $product->name ) : esc_html__( 'N/A', 'digidownloads' ); ?></td>
						<td><?php echo esc_html( $order->buyer_email ); ?></td>
						<td><?php echo esc_html( \DigiDownloads\Currency::format_price( $order->amount ) ); ?></td>
						<td>
							<span class="status-<?php echo esc_attr( $order->payment_status ); ?>">
								<?php echo esc_html( ucfirst( $order->payment_status ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( ucfirst( $order->payment_gateway ) ); ?></td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $order->created_at ) ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="7"><?php esc_html_e( 'No orders found.', 'digidownloads' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
