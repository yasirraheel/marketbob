<?php

namespace App\Listeners;

use App\Events\SaleCreated;

class TrackFacebookPixelPurchase
{
    /**
     * Handle the event.
     * Tracks Facebook Pixel Purchase event when a sale is created from a paid transaction
     *
     * IMPORTANT: This ensures:
     * - Purchase is only tracked when transaction status is PAID (STATUS_PAID = 2)
     * - For manual payments: Only tracks after admin approves/marks as paid
     * - For other payments: Tracks immediately when payment is confirmed
     *
     * @param  SaleCreated  $event
     * @return void
     */
    public function handle(SaleCreated $event)
    {
        $sale = $event->sale;
        $transaction = $event->transaction;

        // Only track if the transaction is actually paid
        if (!$transaction->isPaid()) {
            return;
        }

        // Only track purchase events (not support purchases or other types)
        if (!$transaction->isTypePurchase()) {
            return;
        }

        // Get all sales for this transaction to track them together
        $allSales = [];
        foreach ($transaction->trxItems as $trxItem) {
            // Collect all sales created from this transaction
            if ($sale->transaction_id == $transaction->id) {
                $allSales[] = $sale;
            }
        }

        // Track the purchase event for Facebook Pixel
        trackFBPixelPurchase($transaction, [$sale]);
    }
}
