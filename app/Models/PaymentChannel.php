<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// --- IMPORTANT: ADD THESE THREE IMPORTS TO FIX THE ERRORS ---
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentChannel extends Model
{
    use HasFactory;

    protected $fillable = ['payment_method_id', 'channel_name', 'status'];

    /**
     * Professional Status Mapping
     * Access via: $channel->status_label
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => ($attributes['status'] ?? null) == 1 ? 'Active' : 'Inactive',
        );
    }

    /**
     * Relationship to PaymentMethod
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Relationship to Wallets
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }
}