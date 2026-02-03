<?php
namespace DigiDownloads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email {

	private $settings;

	public function __construct() {
		$this->settings = get_option( 'digidownloads_settings', array(
			'from_email' => get_option( 'admin_email' ),
			'from_name' => get_option( 'blogname' ),
		) );

		add_filter( 'wp_mail_from', array( $this, 'mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'mail_from_name' ) );
	}

	public function mail_from( $email ) {
		if ( ! empty( $this->settings['from_email'] ) ) {
			return $this->settings['from_email'];
		}
		return $email;
	}

	public function mail_from_name( $name ) {
		if ( ! empty( $this->settings['from_name'] ) ) {
			return $this->settings['from_name'];
		}
		return $name;
	}

	public function send_download_email( $to, $order_id, $token ) {
		error_log('Email: Sending download email to ' . $to . ' for order ' . $order_id);
		
		$order = Order::get_by_order_id( $order_id );
		if ( ! $order ) {
			error_log('Email: Order not found: ' . $order_id);
			return false;
		}

		$product = Product::get( $order->product_id );
		if ( ! $product ) {
			error_log('Email: Product not found: ' . $order->product_id);
			return false;
		}

		$download_url = Download::get_download_url( $token );
		error_log('Email: Download URL: ' . $download_url);

		$subject = sprintf(
			/* translators: %s: Product name */
			__( 'Your download is ready: %s', 'digidownloads' ),
			$product->name
		);

		$settings = get_option( 'digidownloads_settings', array(
			'download_expiry_hours' => 48,
			'max_downloads' => 5,
		) );

		$message = $this->get_email_template( array(
			'product_name' => $product->name,
			'order_id' => $order_id,
			'download_url' => $download_url,
			'expiry_hours' => $settings['download_expiry_hours'],
			'max_downloads' => $settings['max_downloads'],
		) );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$sent = wp_mail( $to, $subject, $message, $headers );

		if ( $sent ) {
			error_log('Email: Successfully sent to ' . $to);
		} else {
			error_log('Email: Failed to send to ' . $to);
		}

		do_action( 'digidownloads_email_sent', $to, $order_id, $sent );

		return $sent;
	}

	private function get_email_template( $data ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html( $data['product_name'] ); ?></title>
		</head>
		<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: Arial, sans-serif;">
			<table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 0;">
				<tr>
					<td align="center">
						<table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
							<tr>
								<td style="padding: 40px;">
									<h1 style="margin: 0 0 20px 0; color: #333; font-size: 28px;">
										<?php esc_html_e( 'Thank you for your purchase!', 'digidownloads' ); ?>
									</h1>
									
									<p style="margin: 0 0 20px 0; color: #555; font-size: 16px; line-height: 1.6;">
										<?php
										echo sprintf(
											/* translators: %s: Product name */
											esc_html__( 'Your purchase of %s is complete.', 'digidownloads' ),
											'<strong>' . esc_html( $data['product_name'] ) . '</strong>'
										);
										?>
									</p>

									<p style="margin: 0 0 20px 0; color: #555; font-size: 16px; line-height: 1.6;">
										<strong><?php esc_html_e( 'Order ID:', 'digidownloads' ); ?></strong> <?php echo esc_html( $data['order_id'] ); ?>
									</p>

									<div style="background-color: #f9f9f9; border-left: 4px solid #0073aa; padding: 20px; margin: 30px 0;">
										<p style="margin: 0 0 15px 0; color: #333; font-size: 16px; font-weight: 600;">
											<?php esc_html_e( 'Download Your Product', 'digidownloads' ); ?>
										</p>
										<p style="margin: 0 0 20px 0; color: #555; font-size: 14px;">
											<?php esc_html_e( 'Click the button below to download your digital product:', 'digidownloads' ); ?>
										</p>
										<table cellpadding="0" cellspacing="0" style="margin: 0;">
											<tr>
												<td align="center" style="background-color: #0073aa; border-radius: 4px;">
													<a href="<?php echo esc_url( $data['download_url'] ); ?>" style="display: inline-block; padding: 15px 40px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600;">
														<?php esc_html_e( 'Download Now', 'digidownloads' ); ?>
													</a>
												</td>
											</tr>
										</table>
									</div>

									<div style="background-color: #fff9e6; border: 1px solid #ffcc00; border-radius: 4px; padding: 15px; margin: 20px 0;">
										<p style="margin: 0 0 10px 0; color: #333; font-size: 14px; font-weight: 600;">
											<?php esc_html_e( 'Important Information:', 'digidownloads' ); ?>
										</p>
										<ul style="margin: 0; padding-left: 20px; color: #555; font-size: 14px;">
											<li style="margin-bottom: 5px;">
												<?php
												echo sprintf(
													/* translators: %d: Number of hours */
													esc_html__( 'This download link will expire in %d hours', 'digidownloads' ),
													absint( $data['expiry_hours'] )
												);
												?>
											</li>
											<li style="margin-bottom: 5px;">
												<?php
												echo sprintf(
													/* translators: %d: Maximum download count */
													esc_html__( 'You can download this file up to %d times', 'digidownloads' ),
													absint( $data['max_downloads'] )
												);
												?>
											</li>
											<li>
												<?php esc_html_e( 'Save the file to your computer immediately after downloading', 'digidownloads' ); ?>
											</li>
										</ul>
									</div>

									<p style="margin: 30px 0 0 0; color: #777; font-size: 14px; line-height: 1.6;">
										<?php esc_html_e( 'If you have any questions or issues with your download, please contact our support team.', 'digidownloads' ); ?>
									</p>

									<hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">

									<p style="margin: 0; color: #999; font-size: 12px; text-align: center;">
										<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function test_email( $to ) {
		$subject = __( 'DigiDownloads Test Email', 'digidownloads' );
		$message = '<p>' . __( 'This is a test email from DigiDownloads. If you received this, your email settings are working correctly.', 'digidownloads' ) . '</p>';
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $to, $subject, $message, $headers );
	}
}
