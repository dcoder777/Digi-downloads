# DigiDownloads - Quick Reference Guide

## 🚀 Installation (3 Steps)

1. **Upload**: Copy `digidownloads` folder to `/wp-content/plugins/`
2. **Activate**: Go to WordPress Admin → Plugins → Activate "DigiDownloads"
3. **Configure**: Go to DigiDownloads → Settings → Add Stripe keys

## ⚙️ Stripe Setup (5 Minutes)

1. Get keys from https://dashboard.stripe.com/apikeys
2. Add to DigiDownloads → Settings
3. Create webhook at https://dashboard.stripe.com/webhooks
4. Webhook URL: `https://yoursite.com/?digidownloads_webhook=stripe`
5. Copy webhook secret to settings

## 📦 Create Product (1 Minute)

1. DigiDownloads → Add Product
2. Fill name, price, description
3. Upload file
4. Set status to "Active"
5. Save

## 🛒 Create Checkout Page (30 Seconds)

1. Pages → Add New
2. Add shortcode: `[digidownloads_checkout id="1"]`
3. Publish
4. Done!

## 📊 File Structure Overview

```
digidownloads/
├── digidownloads.php        # Main file
├── admin/                   # Admin interface
├── public/                  # Frontend
├── includes/                # Core logic
├── gateways/                # Stripe
├── db/                      # Database
└── README.md               # Documentation
```

## 🔧 Key Classes

| Class | Purpose | Location |
|-------|---------|----------|
| Product | CRUD operations | includes/Product.php |
| Order | Order management | includes/Order.php |
| Download | Token system | includes/Download.php |
| Email | Notifications | includes/Email.php |
| Stripe | Payment gateway | gateways/class-stripe.php |

## 🎯 Database Tables

- `wp_digidownloads_products` - Product data
- `wp_digidownloads_orders` - Order records
- `wp_digidownloads_download_tokens` - Download links

## 🔐 Security Checklist

- ✅ Nonces on all forms
- ✅ Prepared SQL statements
- ✅ Input sanitization
- ✅ Output escaping
- ✅ Direct file access blocked
- ✅ Secure download tokens

## 📧 Email System

- Automatic after payment
- HTML template
- Download link included
- Configurable from name/email

## 🎨 Shortcodes

### Display Product
```
[digidownloads_product id="1"]
```

### Checkout Form
```
[digidownloads_checkout id="1"]
```

## 🔌 Hooks for Developers

### Actions
```php
// After product created
add_action('digidownloads_product_created', function($id) {
    // Your code
});

// After order completed
add_action('digidownloads_order_status_updated', function($order_id, $status) {
    // Your code
}, 10, 2);
```

### Filters
```php
// Modify file types
add_filter('digidownloads_allowed_file_extensions', function($types) {
    $types[] = 'psd';
    return $types;
});
```

## 🧪 Testing Checklist

- [ ] Activate plugin
- [ ] Add Stripe test keys
- [ ] Create test product
- [ ] Create checkout page
- [ ] Test purchase with card: 4242 4242 4242 4242
- [ ] Check email received
- [ ] Test download link
- [ ] Verify file downloads

## ⚡ Performance Tips

- Plugin uses custom DB tables (fast!)
- Assets only load on checkout pages
- No cron jobs
- Efficient queries
- Minimal overhead

## 🐛 Common Issues

### Webhook Not Working
- Check webhook secret is correct
- Verify URL is accessible
- Test webhook in Stripe dashboard

### Email Not Sending
- Test with WP Mail SMTP plugin
- Check spam folder
- Verify from email is valid

### Download Link Expired
- Check expiry hours in settings (default: 48)
- Verify token hasn't exceeded download limit

## 📞 Support Files

- **README.md** - Plugin overview
- **INSTALLATION.md** - Detailed setup guide
- **THEMEFOREST-CHECKLIST.md** - Submission checklist
- **BUILD-COMPLETE.md** - Complete summary

## 🎯 Key Features

✅ No WooCommerce needed
✅ Lightweight & fast
✅ Secure downloads
✅ Time & count limits
✅ Stripe integration
✅ Professional emails
✅ Clean admin UI
✅ Guest checkout
✅ Single-product checkout

## 📝 Admin Menu

- **DigiDownloads** → Products list
- **Add Product** → Create new product
- **Orders** → View all orders
- **Settings** → Configure Stripe & downloads

## 💳 Stripe Test Cards

- **Success**: 4242 4242 4242 4242
- **Decline**: 4000 0000 0000 0002
- **Auth required**: 4000 0025 0000 3155

Use any future date, any CVC, any ZIP.

## 🚦 Go-Live Checklist

- [ ] Switch to live Stripe keys
- [ ] Update webhook to production URL
- [ ] Test real purchase
- [ ] Verify downloads work
- [ ] Monitor first transactions
- [ ] Set up support system

## 📊 Settings Overview

**Stripe Settings**
- Publishable Key
- Secret Key
- Webhook Secret

**Download Settings**
- Expiry hours (default: 48)
- Max downloads (default: 5)

**Email Settings**
- From name
- From email

## 🎓 Learning Resources

All code is:
- Well-commented
- WordPress standards compliant
- Easy to understand
- Ready to extend

Read the code in this order:
1. digidownloads.php (bootstrap)
2. includes/Product.php (CRUD example)
3. gateways/class-stripe.php (payment flow)
4. includes/Download.php (token system)

## ✨ What's Next?

Plugin is complete and ready for:
- Production deployment
- ThemeForest submission
- Client delivery
- Further customization

**Status: PRODUCTION READY** ✅

---

*Built with WordPress best practices for ThemeForest distribution.*
