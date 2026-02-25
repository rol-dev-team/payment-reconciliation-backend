<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $table = 'wallets';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payment_channel_id',
        'wallet_number',
        'status',
    ];

    /**
     * Relationships
     */

    // Wallet belongs to a PaymentChannel
    public function paymentChannel()
    {
        return $this->belongsTo(PaymentChannel::class, 'payment_channel_id');
    }

    // Wallet has many Comparisons
    public function comparisons()
    {
        return $this->hasMany(Comparison::class, 'wallet_id');
    }

    // Wallet has many VendorTransactions
    public function vendorTransactions()
    {
        return $this->hasMany(VendorTransaction::class, 'wallet_id');
    }
}