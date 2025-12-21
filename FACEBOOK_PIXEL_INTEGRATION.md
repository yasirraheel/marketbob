## Facebook Pixel Integration - Complete Purchase Tracking Guide

### Overview
Facebook Pixel is now fully integrated with your Marketbob TopDeals platform with comprehensive event tracking for the complete purchase lifecycle.

### Key Features

#### 1. **Automatic Purchase Tracking (Most Important)**
- ✅ **Purchases are ONLY tracked when transaction status is PAID (STATUS_PAID = 2)**
- ✅ **Manual Payments**: Purchase event is only tracked AFTER admin approves/marks transaction as paid
- ✅ **Automatic Payments**: Purchase event is tracked immediately when payment is confirmed
- ✅ **No incomplete purchases are tracked as complete**

#### 2. **Event Flow**

```
Customer Places Order
    ↓
Transaction Created (STATUS_UNPAID = 0)
    ↓
├─→ For Manual Payments:
│   └─→ Admin Reviews Payment Proof
│       └─→ Admin Marks as Paid (STATUS_PAID = 2)
│           └─→ SaleCreated Event Fires
│               └─→ TrackFacebookPixelPurchase Listener
│                   └─→ fbPixelTrack('Purchase', {...}) ✓
│
└─→ For Auto Payments (Stripe, PayPal, etc):
    └─→ Payment Gateway Confirms Payment
        └─→ Transaction Marked as PAID (STATUS_PAID = 2)
            └─→ SaleCreated Event Fires
                └─→ TrackFacebookPixelPurchase Listener
                    └─→ fbPixelTrack('Purchase', {...}) ✓
```

### Technical Implementation

#### Files Modified/Created

1. **Migration** - `database/migrations/2025_12_21_000000_add_facebook_pixel_extension.php`
   - Adds facebook_pixel extension to database
   - Stores Pixel ID in extension settings

2. **Helper Functions** - `app/Helpers/General.php`
   - `fbPixelTrack($eventName, $data)` - Basic event tracking
   - `fbPixelEvent($eventName, $data)` - Alias for inline usage
   - `trackFBPixelPurchase($transaction, $sales)` - Purchase tracking
   - `getFBPixelPurchaseData()` - Retrieve purchase data from session

3. **View Template** - `resources/views/themes/basic/components/partials.blade.php`
   - Initializes Facebook Pixel on all pages
   - Auto-tracks PageView events
   - Includes noscript fallback

4. **Event Listener** - `app/Listeners/TrackFacebookPixelPurchase.php`
   - NEW: Listens to SaleCreated event
   - Only tracks when transaction is PAID
   - Prepares purchase data for Facebook Pixel

5. **Extension Controller** - `app/Http/Controllers/Admin/Settings/ExtensionController.php`
   - Fixed: str_replace() argument error

### Database Schema

```sql
extensions table:
- name: "Facebook Pixel"
- alias: "facebook_pixel"
- settings: { "pixel_id": "YOUR_PIXEL_ID" }
- status: 0 (default, disabled)
```

### Setup Instructions

1. **Run Migration on Server**
   ```bash
   php artisan migrate
   ```

2. **Configure in Admin Panel**
   - Go to: Admin Panel → Settings → Extensions
   - Find: "Facebook Pixel"
   - Click: Edit
   - Enable: Toggle switch to ON
   - Paste: Your Facebook Pixel ID
   - Save: Click Save button

3. **Get Your Pixel ID**
   - Login to Facebook Business Manager
   - Go to Events Manager
   - Find your website data source
   - Copy the Pixel ID (usually a 15-17 digit number)

### Purchase Event Data

When a purchase is completed, Facebook Pixel receives:

```javascript
fbq('track', 'Purchase', {
    value: 99.99,                           // Transaction total
    currency: 'USD',                        // From settings
    content_ids: ['item_id_1', ...],        // Product IDs
    content_names: ['Product Name', ...],   // Product names
    content_type: 'product',                // Type of content
    transaction_id: 12345                   // Transaction ID
});
```

### Tracking Guarantee for Manual Payments

**Why Manual Payment Orders Only Track After Admin Approval:**

1. Customer submits payment proof (Bank Transfer, Check, etc.)
2. Transaction status: `STATUS_PENDING` (1)
   - Pixel does NOT track yet
3. Admin reviews and approves the payment
4. Admin marks transaction as: `STATUS_PAID` (2)
   - ProcessPaidTransaction event fires
   - SaleCreated event fires
   - TrackFacebookPixelPurchase listener activates
   - Facebook Pixel NOW tracks the purchase ✓

**This ensures Facebook Ads knows:**
- Which purchases actually completed
- Which are still pending admin review
- No incomplete transactions show as completed purchases

### Custom Event Tracking

You can also track other events in your blade templates:

```blade
<!-- Track when user views product -->
{!! fbPixelEvent('ViewContent', ['content_name' => 'Product Name', 'content_id' => '123']) !!}

<!-- Track when user adds to cart -->
{!! fbPixelEvent('AddToCart', ['value' => 29.99, 'currency' => 'USD']) !!}

<!-- Track when user starts checkout -->
{!! fbPixelEvent('InitiateCheckout', ['value' => 99.99, 'currency' => 'USD']) !!}
```

### Verification in Facebook Ads Manager

After implementing:

1. Go to Facebook Business Manager
2. Open Events Manager
3. Select your website data source
4. Check "Test Events" tab
5. You should see:
   - PageView events on all pages
   - Purchase events only after order completion
   - Other events as configured

### Important Notes

✅ **What's Tracked:**
- PageView on every page
- Purchase only when STATUS_PAID
- Can add custom events as needed

❌ **What's NOT Tracked:**
- Incomplete orders
- Pending manual payment orders (until approved)
- Cancelled transactions
- Refunded purchases

🔒 **Security & Privacy:**
- Pixel ID stored safely in extensions table
- Can be disabled anytime in admin panel
- Session-based temporary data storage
- No personal data sent without consent

### Troubleshooting

**Issue:** Purchases not showing in Facebook Ads Manager
**Solution:** 
- Verify Pixel ID is correct (check in Admin → Settings → Extensions)
- Ensure extension is Enabled (green toggle)
- Check test transactions in Events Manager
- Allow 15-30 minutes for data to appear in Ads Manager

**Issue:** Old orders showing as purchases
**Solution:** 
- This shouldn't happen as pixel initializes on page load only
- Only NEW paid transactions from this point forward are tracked

**Issue:** Manual payment orders tracking before approval
**Solution:**
- Verify transaction status is STATUS_PAID (2) before it tracks
- Check that admin actually marked transaction as paid

### Testing Recommendations

1. Test with automatic payment gateway (Stripe, PayPal, etc.)
   - Should track immediately ✓

2. Test with manual payment (Bank Wire)
   - Verify it does NOT track when status is PENDING
   - Verify it DOES track after admin marks as PAID ✓

3. Test refunded purchase
   - Should still show as purchase in Facebook (refunds tracked separately in Ads Manager)

