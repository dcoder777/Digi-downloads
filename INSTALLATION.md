# DigiDownloads Installation & Setup Guide

## Quick Start Guide

### 1. Plugin Installation

1. Upload the entire `digidownloads` folder to `/wp-content/plugins/`
2. Activate the plugin through 'Plugins' menu in WordPress
3. You'll see a new "DigiDownloads" menu in your admin sidebar

### 2. Stripe Configuration

**Important**: You must configure Stripe before you can accept payments.

#### Get Stripe API Keys

1. Create a Stripe account at https://stripe.com
2. Go to https://dashboard.stripe.com/apikeys
3. You'll see two keys:
   - **Publishable key** (starts with `pk_test_` or `pk_live_`)
   - **Secret key** (starts with `sk_test_` or `sk_live_`)

#### Configure Plugin Settings

1. Go to **DigiDownloads > Settings** in WordPress admin
2. Enter your Stripe Publishable Key
3. Enter your Stripe Secret Key
4. Save settings

#### Setup Webhook (Required for Payment Confirmation)

1. Go to https://dashboard.stripe.com/webhooks
2. Click "Add endpoint"
3. Enter your webhook URL: `https://yoursite.com/?digidownloads_webhook=stripe`
4. Select events to listen to:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
5. Click "Add endpoint"
6. Copy the "Signing secret" (starts with `whsec_`)
7. Paste it in **DigiDownloads > Settings** under "Stripe Webhook Secret"
8. Save settings

### 3. Create Your First Product

1. Go to **DigiDownloads > Add Product**
2. Fill in the form:
   - **Product Name**: e.g., "WordPress Theme Pro"
   - **Description**: Describe your product (supports HTML)
   - **Price**: Enter price in USD (e.g., 29.99)
   - **Product File**: Upload your digital file (ZIP, PDF, etc.)
   - **Status**: Set to "Active"
3. Click "Add Product"

### 4. Create a Checkout Page

1. Create a new WordPress Page (Pages > Add New)
2. Title it something like "Buy WordPress Theme"
3. Add this shortcode to the page content:
   ```
   [digidownloads_checkout id="1"]
   ```
   (Replace `1` with your product ID from the Products list)
4. Publish the page

### 5. Test Your Setup

1. Visit your checkout page
2. Enter a test email address
3. Use Stripe test card: `4242 4242 4242 4242`
   - Any future expiry date
   - Any 3-digit CVC
   - Any 5-digit ZIP code
4. Complete the purchase
5. Check your email for the download link

## File Structure Reference

```
digidownloads/
├── admin/                      # Admin interface
│   ├── class-admin.php        # Main admin class
│   ├── css/
│   │   └── admin.css          # Admin styles
│   └── views/
│       ├── products.php       # Products list page
│       ├── add-product.php    # Add/edit product form
│       ├── orders.php         # Orders list page
│       └── settings.php       # Settings page
├── db/
│   └── class-database.php     # Database schema and installer
├── gateways/
│   ├── class-gateway.php      # Abstract gateway class
│   └── class-stripe.php       # Stripe implementation
├── includes/                   # Core business logic
│   ├── Product.php            # Product CRUD operations
│   ├── Order.php              # Order management
│   ├── Download.php           # Download token system
│   ├── Email.php              # Email notifications
│   └── Security.php           # Security helpers
├── public/                     # Frontend functionality
│   ├── class-public.php       # Public-facing class
│   ├── css/
│   │   └── public.css         # Frontend styles
│   ├── js/
│   │   └── public.js          # Stripe integration
│   └── views/
│       ├── product.php        # Product display template
│       └── checkout.php       # Checkout form template
├── digidownloads.php          # Main plugin file (bootstrap)
├── uninstall.php              # Cleanup on uninstall
├── index.php                  # Directory protection
└── README.md                  # Documentation
```

## Shortcodes

### Product Display

Display product information with a buy button:

```
[digidownloads_product id="1"]
```

**Attributes:**
- `id` (required): The product ID

### Checkout Form

Display a complete checkout form for a product:

```
[digidownloads_checkout id="1"]
```

**Attributes:**
- `id` (required): The product ID

## Database Tables

### wp_digidownloads_products

Stores all product information.

**Fields:**
- `id`: Primary key
- `name`: Product name
- `description`: Product description (HTML)
- `price`: Product price (decimal)
- `file_path`: Absolute path to uploaded file
- `file_name`: Original filename
- `status`: 'active' or 'inactive'
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

### wp_digidownloads_orders

Stores all order records.

**Fields:**
- `id`: Primary key
- `order_id`: Unique order identifier (e.g., DD-XXXXXXXXXXXX)
- `product_id`: Reference to product
- `buyer_email`: Customer email address
- `amount`: Order amount (decimal)
- `payment_status`: 'pending', 'completed', or 'failed'
- `payment_gateway`: 'stripe'
- `gateway_transaction_id`: Stripe payment intent ID
- `created_at`: Order creation timestamp

### wp_digidownloads_download_tokens

Stores secure download tokens.

**Fields:**
- `id`: Primary key
- `token`: 64-character secure token
- `order_id`: Reference to order
- `product_id`: Reference to product
- `download_count`: Current download count
- `max_downloads`: Maximum allowed downloads
- `expires_at`: Token expiration timestamp
- `created_at`: Token creation timestamp

## Security Features

✅ **Nonce verification** on all admin actions  
✅ **Prepared SQL statements** for all database queries  
✅ **Input sanitization** on all user inputs  
✅ **Output escaping** on all displayed data  
✅ **Capability checks** for admin functions  
✅ **Webhook signature verification** for Stripe  
✅ **Cryptographically secure tokens** (64-byte random)  
✅ **Direct file access prevention** (.htaccess protection)  
✅ **File type validation** on uploads  
✅ **Time-limited downloads**  
✅ **Download count limits**

## Troubleshooting

### Payments Not Completing

1. Check Stripe webhook is configured correctly
2. Verify webhook secret is correct in settings
3. Check WordPress error logs for webhook errors
4. Test webhook in Stripe Dashboard

### Download Links Not Working

1. Verify `.htaccess` file exists in uploads/digidownloads/
2. Check file permissions on uploads directory
3. Ensure download token hasn't expired
4. Check download count hasn't exceeded limit

### Email Not Sending

1. Test WordPress email with a plugin like WP Mail SMTP
2. Check spam folder
3. Verify "From Email" in settings is valid
4. Check server mail logs

## Extending the Plugin

### Add Custom Gateway

```php
// Create new gateway class
class My_Gateway extends \DigiDownloads\Gateways\Gateway {
    protected function init() {
        $this->id = 'mygateway';
        $this->name = 'My Gateway';
    }
    
    public function process_payment( $order_id, $data ) {
        // Implementation
    }
    
    public function handle_webhook() {
        // Implementation
    }
}
```

### Hook into Events

```php
// After product is created
add_action( 'digidownloads_product_created', function( $product_id ) {
    // Your code
}, 10, 1 );

// After order status changes
add_action( 'digidownloads_order_status_updated', function( $order_id, $status ) {
    // Your code
}, 10, 2 );

// Modify allowed file types
add_filter( 'digidownloads_allowed_file_extensions', function( $extensions ) {
    $extensions[] = 'exe'; // Add .exe files (be careful!)
    return $extensions;
} );
```

## Performance Optimization

✅ Custom database tables (no post meta)  
✅ Minimal frontend assets (only load on checkout pages)  
✅ No unnecessary WordPress hooks  
✅ Efficient SQL queries with proper indexes  
✅ No cron jobs (webhook-based)

## Production Checklist

Before going live:

- [ ] Switch to Stripe live keys (not test keys)
- [ ] Update webhook URL to production domain
- [ ] Test full purchase flow with real payment
- [ ] Verify download links work
- [ ] Test email delivery
- [ ] Set appropriate download expiry time
- [ ] Set reasonable download limit
- [ ] Back up database
- [ ] Review file upload limits in PHP settings

## Support

For issues or questions, contact the plugin author or check documentation.
