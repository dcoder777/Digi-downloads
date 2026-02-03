<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Products', 'digidownloads' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=digidownloads-add-product' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'digidownloads' ); ?></a>
	<hr class="wp-header-end">

	<?php if ( isset( $_GET['message'] ) ) : ?>
		<?php if ( $_GET['message'] === 'product_added' ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Product added successfully.', 'digidownloads' ); ?></p>
			</div>
		<?php elseif ( $_GET['message'] === 'product_updated' ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Product updated successfully.', 'digidownloads' ); ?></p>
			</div>
		<?php elseif ( $_GET['message'] === 'product_deleted' ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Product deleted successfully.', 'digidownloads' ); ?></p>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Name', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Price', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'File', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Status', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Created', 'digidownloads' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'digidownloads' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $products ) ) : ?>
				<?php foreach ( $products as $product ) : ?>
					<tr>
						<td><?php echo esc_html( $product->id ); ?></td>
						<td><strong><?php echo esc_html( $product->name ); ?></strong></td>
						<td><?php echo esc_html( \DigiDownloads\Currency::format_price( $product->price ) ); ?></td>
						<td><?php echo $product->file_name ? esc_html( $product->file_name ) : '—'; ?></td>
						<td>
							<span class="status-<?php echo esc_attr( $product->status ); ?>">
								<?php echo esc_html( ucfirst( $product->status ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $product->created_at ) ) ); ?></td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=digidownloads-add-product&edit=' . $product->id ) ); ?>"><?php esc_html_e( 'Edit', 'digidownloads' ); ?></a> |
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=digidownloads&action=delete_product&product_id=' . $product->id ), 'digidownloads_delete_product_' . $product->id, 'digidownloads_delete_nonce' ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this product?', 'digidownloads' ); ?>');" style="color: #b32d2e;"><?php esc_html_e( 'Delete', 'digidownloads' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="7"><?php esc_html_e( 'No products found.', 'digidownloads' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
