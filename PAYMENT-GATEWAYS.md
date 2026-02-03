# Payment Gateways & Multi-Currency Support

DigiDownloads supports multiple payment gateways and currencies to help you sell digital products globally.

## Supported Currencies

The plugin supports 17 major currencies:

- **USD** - US Dollar ($)
- **EUR** - Euro (€)
- **GBP** - British Pound (£)
- **AUD** - Australian Dollar (A$)
- **CAD** - Canadian Dollar (C$)
- **INR** - Indian Rupee (₹)
- **JPY** - Japanese Yen (¥)
- **CNY** - Chinese Yuan (¥)
- **BRL** - Brazilian Real (R$)
- **MXN** - Mexican Peso ($)
- **ZAR** - South African Rand (R)
- **SGD** - Singapore Dollar (S$)
- **NZD** - New Zealand Dollar (NZ$)
- **CHF** - Swiss Franc (CHF)
- **SEK** - Swedish Krona (kr)
- **NOK** - Norwegian Krone (kr)
- **DKK** - Danish Krone (kr)

To change your currency:
1. Go to **DigiDownloads > Settings**
2. Select your desired currency from the **Currency** dropdown
3. Click **Save Settings**

All product prices will be displayed in your selected currency.

## Supported Payment Gateways

### 1. Stripe (Default)

**Best for:** Global payments, supports 135+ currencies

**Setup:**
1. Go to [Stripe Dashboard](https://dashboard.stripe.com/)
2. Create an account or sign in
3. Get your API keys from **Developers > API Keys**
4. In WordPress, go to **DigiDownloads > Settings**
5. Select **Stripe** as Payment Gateway
6. Enter your **Publishable Key** and **Secret Key**
7. Set **Stripe Mode** to:
   - **Test Mode** - for testing with test cards
   - **Live Mode** - for real transactions

**Test Cards:**
- Success: `4242 4242 4242 4242`
- Decline: `4000 0000 0000 0002`
- Use any future expiry date, any CVC, and any postal code.

**Webhook URL:** `https://yoursite.com/wp-json/digidownloads/v1/webhook/stripe`

**Features:**
- Inline card payment (customers stay on your site)
- Strong Customer Authentication (SCA) compliant
- Automatic email receipts
- Supports all major credit/debit cards

---

### 2. PayPal

**Best for:** Global payments, trusted brand, no card required

**Setup:**
1. Go to [PayPal Developer Dashboard](https://developer.paypal.com/)
2. Create an account or sign in
3. Go to **My Apps & Credentials**
4. Create a **REST API app**
5. Get your **Client ID** and **Secret**
6. In WordPress, go to **DigiDownloads > Settings**
7. Select **PayPal** as Payment Gateway
8. Enter your **Client ID** and **Secret**
9. Set **PayPal Mode** to:
   - **Sandbox** - for testing (use sandbox.paypal.com)
   - **Live** - for real transactions

**Webhook URL:** `https://yoursite.com/wp-json/digidownloads/v1/webhook/paypal`

**Features:**
- Redirect to PayPal for payment
- Customers can pay with PayPal balance or card
- No card details on your site (PCI compliance easier)
- Widely trusted payment method

**Test Accounts:**
Create test buyer and seller accounts at [PayPal Sandbox](https://www.sandbox.paypal.com/)

---

### 3. Razorpay

**Best for:** India-focused businesses, supports INR, UPI, cards, wallets

**Setup:**
1. Go to [Razorpay Dashboard](https://dashboard.razorpay.com/)
2. Create an account or sign in
3. Go to **Settings > API Keys**
4. Generate **Key ID** and **Key Secret**
5. In WordPress, go to **DigiDownloads > Settings**
6. Select **Razorpay** as Payment Gateway
7. Enter your **Key ID** and **Key Secret**
8. Set **Razorpay Mode** to:
   - **Test Mode** - for testing
   - **Live Mode** - for real transactions

**Webhook URL:** `https://yoursite.com/wp-json/digidownloads/v1/webhook/razorpay`

**Features:**
- Modal checkout (popup on your site)
- Supports UPI, cards, wallets, net banking
- Popular in India
- Instant payment confirmation

**Test Cards:**
- Success: `4111 1111 1111 1111`
- Any future expiry, any CVV, any name

---

## How Payments Work

### Stripe Flow:
1. Customer enters email
2. Customer enters card details (inline on your site)
3. Payment processed immediately
4. Download link sent to email

### PayPal Flow:
1. Customer enters email
2. Customer clicks "Proceed to PayPal"
3. Redirected to PayPal to complete payment
4. Redirected back to your site
5. Download link sent to email

### Razorpay Flow:
1. Customer enters email
2. Customer clicks "Proceed to Payment"
3. Razorpay modal opens (popup)
4. Customer selects payment method and pays
5. Modal closes on success
6. Download link sent to email

---

## Webhook Configuration (Important!)

Webhooks ensure your site is notified when payments are completed. This is crucial for automatic order fulfillment.

### Stripe Webhooks:
1. Go to **Stripe Dashboard > Developers > Webhooks**
2. Click **Add endpoint**
3. URL: `https://yoursite.com/wp-json/digidownloads/v1/webhook/stripe`
4. Events to send: `payment_intent.succeeded`
5. Copy the **Signing Secret** and add it to plugin settings

### PayPal Webhooks:
1. Go to **PayPal Developer > My Apps & Credentials**
2. Select your app
3. Scroll to **Webhooks**
4. Click **Add Webhook**
5. URL: `https://yoursite.com/wp-json/digidownloads/v1/webhook/paypal`
6. Events: `CHECKOUT.ORDER.APPROVED`, `PAYMENT.CAPTURE.COMPLETED`

### Razorpay Webhooks:
1. Go to **Razorpay Dashboard > Settings > Webhooks**
2. Add webhook URL: `https://yoursite.com/wp-json/digidownloads/v1/webhook/razorpay`
3. Select events: `payment.authorized`, `payment.captured`
4. Copy the **Webhook Secret** and add it to plugin settings

---

## Currency and Gateway Compatibility

| Gateway | Supported Currencies | Notes |
|---------|---------------------|-------|
| Stripe | All 17 currencies | Most flexible, global reach |
| PayPal | All 17 currencies | Check PayPal's supported currencies for your country |
| Razorpay | INR primarily | Best for Indian market, some support for USD |

**Important:** Make sure your gateway account supports your selected currency. Some gateways may require additional setup for certain currencies.

---

## Switching Between Gateways

You can change payment gateways at any time:

1. Go to **DigiDownloads > Settings**
2. Select a different **Payment Gateway**
3. Configure the API keys for the new gateway
4. Save settings

**Note:** Existing orders will retain their original payment gateway information. Only new orders will use the new gateway.

---

## Security Best Practices

1. **Always use HTTPS** - Required for all gateways
2. **Test Mode First** - Always test with test/sandbox mode before going live
3. **Secure API Keys** - Never share your secret keys
4. **Regular Backups** - Backup your site regularly
5. **Update Plugin** - Keep DigiDownloads updated for security patches
6. **PCI Compliance:**
   - Stripe: Built-in, no card data touches your server
   - PayPal: Redirect flow, no card data on your site
   - Razorpay: Modal checkout, PCI compliant

---

## Troubleshooting

### Payment not completing:
- Check API keys are correct
- Verify gateway mode (test/live) matches your keys
- Check webhook is configured
- Look at browser console for JavaScript errors

### Currency not displaying correctly:
- Clear WordPress cache
- Check currency is selected in settings
- Ensure theme doesn't override styles

### Gateway-specific issues:
- **Stripe:** Check Stripe Dashboard logs
- **PayPal:** Check PayPal Activity for transaction
- **Razorpay:** Check Razorpay Dashboard payment logs

### Webhooks not working:
- Test webhook URL is accessible (should return 200 OK)
- Check webhook signing secrets are configured
- Verify events are selected in gateway dashboard

---

## Developer Information

The payment system is built with extensibility in mind:

### Add Custom Gateway:

```php
// Extend the abstract Gateway class
class MyGateway extends \DigiDownloads\Gateways\Gateway {
    
    public function create_payment( $data ) {
        // Implement payment creation
    }
    
    public function handle_webhook( $request ) {
        // Implement webhook handling
    }
}

// Register your gateway
add_filter( 'digidownloads_gateways', function( $gateways ) {
    $gateways['my_gateway'] = new MyGateway();
    return $gateways;
});
```

### Currency Helpers:

```php
// Get current currency
$currency = \DigiDownloads\Currency::get_currency();

// Get currency symbol
$symbol = \DigiDownloads\Currency::get_symbol();

// Format price
$formatted = \DigiDownloads\Currency::format_price( 99.99 );
```

---

## Support

For issues or questions:
- Check [documentation](README.md)
- Review [shortcodes guide](admin/views/shortcodes.php)
- Contact plugin support

**Gateway Support:**
- [Stripe Support](https://support.stripe.com/)
- [PayPal Support](https://www.paypal.com/support)
- [Razorpay Support](https://razorpay.com/support/)
