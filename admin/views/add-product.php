<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php echo $edit_mode ? esc_html__( 'Edit Product', 'digidownloads' ) : esc_html__( 'Add New Product', 'digidownloads' ); ?></h1>

	<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin.php?page=digidownloads-add-product&action=' . ( $edit_mode ? 'edit_product' : 'add_product' ) ) ); ?>">
		<?php wp_nonce_field( $edit_mode ? 'digidownloads_edit_product' : 'digidownloads_add_product', $edit_mode ? 'digidownloads_edit_product_nonce' : 'digidownloads_add_product_nonce' ); ?>
		
		<?php if ( $edit_mode && $product ) : ?>
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $product->id ); ?>">
		<?php endif; ?>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="name"><?php esc_html_e( 'Product Name', 'digidownloads' ); ?> <span class="required">*</span></label>
				</th>
				<td>
					<input type="text" name="name" id="name" class="regular-text" value="<?php echo $edit_mode && $product ? esc_attr( $product->name ) : ''; ?>" required>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="description"><?php esc_html_e( 'Description', 'digidownloads' ); ?></label>
				</th>
				<td>
					<?php
					wp_editor(
						$edit_mode && $product ? $product->description : '',
						'description',
						array(
							'textarea_name' => 'description',
							'textarea_rows' => 10,
							'media_buttons' => false,
						)
					);
					?>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="price"><?php esc_html_e( 'Price', 'digidownloads' ); ?> <span class="required">*</span></label>
				</th>
				<td>
					<input type="number" name="price" id="price" step="0.01" min="0" class="regular-text" value="<?php echo $edit_mode && $product ? esc_attr( $product->price ) : '0.00'; ?>" required>
					<p class="description"><?php esc_html_e( 'Enter price in USD', 'digidownloads' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="product_file"><?php esc_html_e( 'Product File', 'digidownloads' ); ?></label>
				</th>
				<td>
					<?php if ( $edit_mode && $product && $product->file_name ) : ?>
						<p><?php esc_html_e( 'Current file:', 'digidownloads' ); ?> <strong><?php echo esc_html( $product->file_name ); ?></strong></p>
						<p class="description"><?php esc_html_e( 'Upload a new file to replace the current one', 'digidownloads' ); ?></p>
					<?php endif; ?>
					<input type="file" name="product_file" id="product_file">
					<p class="description"><?php esc_html_e( 'Upload the digital product file', 'digidownloads' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="status"><?php esc_html_e( 'Status', 'digidownloads' ); ?></label>
				</th>
				<td>
					<select name="status" id="status">
						<option value="active" <?php echo ( $edit_mode && $product && $product->status === 'active' ) ? 'selected' : ''; ?>><?php esc_html_e( 'Active', 'digidownloads' ); ?></option>
						<option value="inactive" <?php echo ( $edit_mode && $product && $product->status === 'inactive' ) ? 'selected' : ''; ?>><?php esc_html_e( 'Inactive', 'digidownloads' ); ?></option>
					</select>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $edit_mode ? esc_attr__( 'Update Product', 'digidownloads' ) : esc_attr__( 'Add Product', 'digidownloads' ); ?>">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=digidownloads' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'digidownloads' ); ?></a>
		</p>
	</form>
</div>
