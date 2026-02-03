# Payment Gateway Setup Required

## ⚠️ Important: Payment Gateway Configuration

**Yes, payment gateway API keys are required** for the "Buy Now" and "Complete Purchase" buttons to work.

Without configuring a payment gateway, customers will see an error message: "Payment gateway not configured. Please contact the site administrator."

## How It Works Now

### Products List (`[digidownloads_products]`)
1. **Buy Now button** - Expands an inline checkout form
2. Customer enters email
3. Enters payment details (Stripe card, or redirects for PayPal/Razorpay)
4. Completes purchase
5. Receives download link via email

### Single Checkout (`[digidownloads_checkout id="X"]`)
- Shows full checkout page for one product
- Same payment flow

## Quick Setup (Choose One Gateway)

### Option 1: Stripe (Fastest - 2 minutes)
1. Go to [Stripe Dashboard](https://dashboard.stripe.com/)
2. Get **Publishable Key** and **Secret Key** from Developers → API Keys
3. In WordPress: **DigiDownloads → Settings**
4. Select **Stripe** as gateway
5. Paste both keys
6. Choose **Test Mode** (for testing with card 4242 4242 4242 4242)
7. Click **Save**
8. ✅ Ready to test!

### Option 2: PayPal
1. Go to [PayPal Developer](https://developer.paypal.com/)
2. Create REST API app
3. Get **Client ID** and **Secret**
4. In WordPress: **DigiDownloads → Settings**
5. Select **PayPal**
6. Paste credentials
7. Choose **Sandbox** mode for testing
8. Click **Save**

### Option 3: Razorpay (Best for India)
1. Go to [Razorpay Dashboard](https://dashboard.razorpay.com/)
2. Get **Key ID** and **Key Secret**
3. In WordPress: **DigiDownloads → Settings**
4. Select **Razorpay**
5. Paste keys
6. Choose **Test Mode**
7. Click **Save**

## Testing Without Going Live

All gateways support test mode:
- **Stripe Test Card**: 4242 4242 4242 4242 (any future date, any CVC)
- **PayPal**: Use sandbox account
- **Razorpay**: Use test mode cards

## Admin Notice

You'll see a warning at the top of DigiDownloads pages until you configure a payment gateway:

> **DigiDownloads:** Payment gateway not configured. Checkouts will not work until you add your API keys in Settings.

## What Happens Without Configuration

- Products display correctly ✅
- Customers can click "Buy Now" ✅
- Checkout form appears ✅
- **But payment will fail** with error message ❌

## After Configuration

1. Test with test credentials first
2. Verify email delivery works
3. Check download links work
4. Then switch to live mode with real credentials

## Need Help?

See detailed setup guides:
- [QUICK-START.md](QUICK-START.md) - 5-minute setup guide
- [PAYMENT-GATEWAYS.md](PAYMENT-GATEWAYS.md) - Complete gateway documentation
- [README.md](README.md) - Full plugin documentation

## Summary

**Can buttons work without API keys?** No - payment processing requires gateway configuration.

**Minimum to get started:** 5 minutes with Stripe in test mode.

**Best practice:** Configure one gateway in test mode → test thoroughly → switch to live mode.
