# DigiDownloads Quick Start Guide

Get your digital products store up and running in 5 minutes!

## Step 1: Install & Activate (1 minute)

1. Upload `digidownloads` folder to `/wp-content/plugins/`
2. Go to **Plugins** in WordPress admin
3. Click **Activate** on DigiDownloads

## Step 2: Configure Payment Gateway (2 minutes)

### Option A: Stripe (Recommended for Global)

1. Go to [Stripe Dashboard](https://dashboard.stripe.com/)
2. Sign up or log in
3. Go to **Developers > API Keys**
4. Copy your **Publishable key** and **Secret key**
5. In WordPress, go to **DigiDownloads > Settings**
6. Select **Stripe** as Payment Gateway
7. Paste your keys
8. Select **Test Mode** (for testing) or **Live Mode** (for real payments)
9. Click **Save Settings**

### Option B: PayPal (Trusted Brand)

1. Go to [PayPal Developer](https://developer.paypal.com/)
2. Sign in with your PayPal account
3. Go to **My Apps & Credentials**
4. Create a REST API app
5. Copy **Client ID** and **Secret**
6. In WordPress, go to **DigiDownloads > Settings**
7. Select **PayPal** as Payment Gateway
8. Paste your credentials
9. Select **Sandbox** (testing) or **Live** (real payments)
10. Click **Save Settings**

### Option C: Razorpay (Best for India)

1. Go to [Razorpay Dashboard](https://dashboard.razorpay.com/)
2. Sign up or log in
3. Go to **Settings > API Keys**
4. Generate and copy **Key ID** and **Key Secret**
5. In WordPress, go to **DigiDownloads > Settings**
6. Select **Razorpay** as Payment Gateway
7. Paste your keys
8. Select **Test Mode** or **Live Mode**
9. Click **Save Settings**

## Step 3: Choose Currency (30 seconds)

1. In **DigiDownloads > Settings**
2. Find the **Currency** dropdown
3. Select your preferred currency (USD, EUR, GBP, INR, etc.)
4. Click **Save Settings**

## Step 4: Create Your First Product (1 minute)

1. Go to **DigiDownloads > Add Product**
2. Enter:
   - **Product Name**: e.g., "My Awesome eBook"
   - **Description**: Brief description of your product
   - **Price**: e.g., 19.99 (just the number, no currency symbol)
3. Click **Choose File** to upload your digital product (PDF, ZIP, etc.)
4. Set **Status** to **Active**
5. Click **Add Product**

## Step 5: Add Checkout to Your Site (30 seconds)

### Easy Method (Recommended):

1. Go to **DigiDownloads > Shortcodes**
2. Find your product in the list
3. Click the **Checkout Shortcode** field to copy it
4. Go to **Pages > Add New** in WordPress
5. Title: "Buy My eBook"
6. Paste the shortcode in the content area
7. Click **Publish**

### Manual Method:

Create a new page and add:
```
[digidownloads_checkout id="1"]
```
(Replace `1` with your product ID from the Products page)

## You're Done! 🎉

Test your setup:
1. Visit your checkout page
2. Enter an email address
3. Use test payment details:
   - **Stripe Test Card**: 4242 4242 4242 4242
   - **PayPal**: Use sandbox account
   - **Razorpay Test Card**: 4111 1111 1111 1111
4. Complete the test purchase
5. Check your email for the download link!

## Next Steps

### Customize Email Settings

Go to **DigiDownloads > Settings** and configure:
- **From Name**: Your store/company name
- **From Email**: Your support email
- **Email Subject**: Customize the email subject line

### Adjust Download Settings

- **Download Link Expiry**: How long links stay valid (default: 48 hours)
- **Maximum Downloads**: How many times a customer can download (default: 5)

### Add More Products

Repeat Step 4 for each digital product you want to sell!

### Display Products

Use the product shortcode to show product info with a buy button:
```
[digidownloads_product id="1"]
```

## Common Issues & Solutions

### "Payment not processing"
- ✅ Check your API keys are correct
- ✅ Verify you're using the right mode (Test vs Live)
- ✅ Ensure your site has HTTPS enabled

### "Download link not received"
- ✅ Check spam/junk folder
- ✅ Verify email settings in DigiDownloads > Settings
- ✅ Check if WordPress can send emails (test with a simple form)

### "Currency not showing correctly"
- ✅ Refresh the page after changing currency
- ✅ Clear any caching plugins
- ✅ Check currency is saved in settings

## Getting to Live Mode

Once you've tested and everything works:

1. **Get Live API Keys** from your payment gateway
2. Go to **DigiDownloads > Settings**
3. Switch mode from **Test** to **Live**
4. Enter your **Live API keys**
5. Click **Save Settings**
6. **Set up webhooks** (see PAYMENT-GATEWAYS.md for details)

## Need Help?

- 📖 Read full documentation: [README.md](README.md)
- 💳 Payment gateway details: [PAYMENT-GATEWAYS.md](PAYMENT-GATEWAYS.md)
- 📝 Shortcode reference: **DigiDownloads > Shortcodes** (in WordPress admin)

## Pro Tips

1. **Test thoroughly** before going live with real payments
2. **Use HTTPS** - required by all payment gateways
3. **Set up webhooks** for reliable payment notifications
4. **Backup regularly** - keep your products and orders safe
5. **Monitor orders** in **DigiDownloads > Orders**
6. **Check payment logs** in your gateway's dashboard if issues occur

---

**Happy Selling! 🚀**

For the most reliable experience, we recommend:
- **Stripe** for global customers (most countries)
- **PayPal** for brand recognition and trust
- **Razorpay** for Indian market (supports UPI, wallets, etc.)
