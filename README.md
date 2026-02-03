# DigiDownloads

A lightweight, performance-focused WordPress plugin for selling digital products without WooCommerce.

## Features

- **Product Management**: Create and manage digital products with file uploads
- **Secure Checkout**: Single-page checkout with guest checkout support
- **Multiple Payment Gateways**: Stripe, PayPal, and Razorpay support
- **Multi-Currency**: Support for 17 major global currencies
- **Secure Downloads**: Time-limited and download-count-limited secure file delivery
- **Email Notifications**: Automatic email delivery with download links
- **Custom Database Tables**: No post meta bloat, optimized for performance
- **WordPress Standards**: Follows WordPress coding standards and best practices
- **Shortcode Documentation**: Built-in guide for easy implementation

## Installation

1. Upload the `digidownloads` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to DigiDownloads > Settings to configure your payment gateway
4. Start adding products!

## Configuration

### Payment Gateway Setup

DigiDownloads supports three popular payment gateways:

#### Stripe (Default)
1. Get your Stripe API keys from [Stripe Dashboard](https://dashboard.stripe.com/apikeys)
2. Go to **DigiDownloads > Settings**
3. Select **Stripe** as Payment Gateway
4. Enter your Stripe Publishable Key and Secret Key
5. Set mode to Test or Live
6. Set up webhook in Stripe: `https://yoursite.com/wp-json/digidownloads/v1/webhook/stripe`

#### PayPal
1. Get your credentials from [PayPal Developer](https://developer.paypal.com/)
2. Go to **DigiDownloads > Settings**
3. Select **PayPal** as Payment Gateway
4. Enter your Client ID and Secret
5. Set mode to Sandbox or Live
6. Set up webhook: `https://yoursite.com/wp-json/digidownloads/v1/webhook/paypal`

#### Razorpay
1. Get your keys from [Razorpay Dashboard](https://dashboard.razorpay.com/)
2. Go to **DigiDownloads > Settings**
3. Select **Razorpay** as Payment Gateway
4. Enter your Key ID and Key Secret
5. Set mode to Test or Live
6. Set up webhook: `https://yoursite.com/wp-json/digidownloads/v1/webhook/razorpay`

**For detailed setup instructions, see [PAYMENT-GATEWAYS.md](PAYMENT-GATEWAYS.md)**

### Currency Settings

Choose from 17 supported currencies:
- USD, EUR, GBP, AUD, CAD, INR, JPY, CNY, BRL, MXN, ZAR, SGD, NZD, CHF, SEK, NOK, DKK

Go to **DigiDownloads > Settings** and select your preferred currency from the dropdown.

### Download Settings

- **Download Link Expiry**: Set how many hours download links remain valid (default: 48 hours)
- **Maximum Downloads**: Set maximum number of downloads per purchase (default: 5)

### Email Settings

- Configure the "From" name and email for purchase confirmation emails

## Usage

### Creating Products

1. Go to **DigiDownloads > Add Product**
2. Enter product name, description, and price
3. Upload the digital file
4. Set status to Active
5. Click **Add Product**

### Using Shortcodes

DigiDownloads provides an easy-to-use shortcodes interface:

1. Go to **DigiDownloads > Shortcodes** in your WordPress admin
2. Find your product in the list
3. Click any shortcode field to copy it
4. Paste the shortcode in any page or post

#### Available Shortcodes:

**Products List** - Display all products in a grid:
```
[digidownloads_products]
```
Options:
- `columns="3"` - Number of columns (1-4, default: 3)
- `limit="9"` - Maximum products to show (default: all)

Example: `[digidownloads_products columns="4" limit="8"]`

**Checkout Form** - Full checkout page with payment:
```
[digidownloads_checkout id="1"]
```

**Product Display** - Show product info with buy button:
```
[digidownloads_product id="1"]
```

Replace `1` with your actual product ID (shown in the Shortcodes page).

The Shortcodes page includes:
- List of all your products
- One-click copy for all shortcodes
- Usage instructions and examples
- Quick setup reminders

## Database Schema

The plugin creates three custom tables:

- `wp_digidownloads_products` - Stores product information
- `wp_digidownloads_orders` - Stores order records
- `wp_digidownloads_download_tokens` - Stores secure download tokens

## Security

- All admin actions protected with WordPress nonces
- SQL queries use prepared statements
- File uploads restricted to specific types
- Download tokens are cryptographically secure
- Webhook signatures verified
- Direct file access prevented

## Hooks & Filters

### Actions

- `digidownloads_product_created` - Fires after product is created
- `digidownloads_product_updated` - Fires after product is updated
- `digidownloads_product_deleted` - Fires after product is deleted
- `digidownloads_order_created` - Fires after order is created
- `digidownloads_order_status_updated` - Fires when order status changes
- `digidownloads_download_token_generated` - Fires when download token is created
- `digidownloads_email_sent` - Fires after email is sent

### Filters

- `digidownloads_allowed_file_extensions` - Modify allowed file types for uploads

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Stripe account for payment processing

## File Structure

```
digidownloads/
├── admin/              # Admin interface
│   ├── css/
│   ├── views/
│   └── class-admin.php
├── db/                 # Database layer
│   └── class-database.php
├── gateways/           # Payment gateways
│   ├── class-gateway.php
│   └── class-stripe.php
├── includes/           # Core classes
│   ├── Product.php
│   ├── Order.php
│   ├── Download.php
│   ├── Email.php
│   └── Security.php
├── public/             # Public-facing functionality
│   ├── css/
│   ├── js/
│   ├── views/
│   └── class-public.php
└── digidownloads.php   # Main plugin file
```

## Support

For support and feature requests, please contact the plugin author.

## License

GPL-2.0+

## Changelog

### 1.0.0
- Initial release
- Product management
- Stripe integration
- Secure downloads
- Email notifications
