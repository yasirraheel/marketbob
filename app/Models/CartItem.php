<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    public function scopeForCurrentSession($query)
    {
        if (authUser()) {
            $query->where('user_id', authUser()->id)
                ->whereNull('session_id');
        } else {
            $query->where('session_id', session()->get('session_id'))
                ->whereNull('user_id');
        }
    }

    protected $fillable = [
        'session_id',
        'user_id',
        'item_id',
        'validity_period',
        'quantity',
        'support_period_id',
    ];

    public function getTotalAmount()
    {
        $quantity = $this->quantity;
        $validityPrices = @json_decode($this->item->validity_prices ?? '{}', true) ?? [];
        $amount = $validityPrices[$this->validity_period] ?? 0;

        $total = ($amount * $quantity);

        return $total;
    }

    public function getTotalAmountWithSupport()
    {
        $quantity = $this->quantity;
        $validityPrices = @json_decode($this->item->validity_prices ?? '{}', true) ?? [];
        $amount = $validityPrices[$this->validity_period] ?? 0;

        $supportPeriod = $this->supportPeriod;
        if ($supportPeriod) {
            $supportPeriodAmount = ($amount * $supportPeriod->percentage) / 100;
            $amount = ($amount + $supportPeriodAmount);
        }

        $total = ($amount * $quantity);

        return $total;
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function supportPeriod()
    {
        return $this->belongsTo(SupportPeriod::class);
    }
}