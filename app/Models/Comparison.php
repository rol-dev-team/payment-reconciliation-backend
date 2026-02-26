<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comparison extends Model
{
    use HasFactory;

    protected $table = 'comparisons';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'batch_id',
        'process_no',
        'trx_id',
        'billing_system_id',
        'sender_no',
        'trx_date',
        'entity',
        'customer_id',
        'amount',
        'channel_id',
        'wallet_id',
        'status',
        'is_vendor',
        'is_billing_system',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'trx_date' => 'datetime',
        'amount' => 'decimal:2',
        'is_vendor' => 'boolean',
        'is_billing_system' => 'boolean',
    ];

    /**
     * Relationships
     */

    // Comparison belongs to a Billing System
    public function billingSystem()
    {
        return $this->belongsTo(BillingSystem::class, 'billing_system_id');
    }

    // Comparison belongs to a Payment Channel
    public function channel()
    {
        return $this->belongsTo(PaymentChannel::class, 'channel_id');
    }

    // Comparison belongs to a Wallet
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}