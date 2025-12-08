<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'item_id',
        'validity_period',
        'price',
        'quantity',
        'total',
        'support',
    ];

    protected $with = [
        'item',
    ];

    protected $casts = [
        'support' => 'object',
    ];

    public function getTotalAmount()
    {
        $quantity = $this->quantity;
        if ($this->isLicenseTypeRegular()) {
            $amount = $this->item->price->regular;
        } else {
            $amount = $this->item->price->extended;
        }

        $total = ($amount * $quantity);

        if ($this->support) {
            $total += $this->support->total;
        }

        return $total;
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}