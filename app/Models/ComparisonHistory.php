<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComparisonHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * * Explicitly defined because it doesn't follow standard pluralization.
     */
    protected $table = 'comparisons_history';

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

    /**
     * History record belongs to a Batch.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    /**
     * History record belongs to a Billing System.
     */
    public function billingSystem(): BelongsTo
    {
        return $this->belongsTo(BillingSystem::class, 'billing_system_id');
    }

    /**
     * History record belongs to a Payment Channel.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(PaymentChannel::class, 'channel_id');
    }

    /**
     * Note: If 'wallets' table uses an integer PK but this history table 
     * stores it as a string (per your ERD), ensure the types match 
     * or handle the string conversion here.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}