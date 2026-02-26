<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// --- ADD THESE THREE IMPORTS ---
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = ['payment_channel_id', 'wallet_number', 'status'];

    /**
     * Cast status to boolean so 1/0 becomes true/false in your code.
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Professional Accessor
     * Access via: $wallet->status_text
     */
    protected function statusText(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['status'] ? 'Active' : 'Inactive',
        );
    }

    /**
     * Relationship to PaymentChannel
     */
    public function paymentChannel(): BelongsTo
    {
        return $this->belongsTo(PaymentChannel::class, 'payment_channel_id');
    }
}