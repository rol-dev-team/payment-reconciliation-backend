<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// This class is required for return type hinting
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingTransaction extends Model
{
    use HasFactory;

    protected $table = 'billing_transactions';

    /**
     * The attributes that are mass assignable.
     * 'batch_id' has been added according to the new migration.
     */
    protected $fillable = [
        'billing_system_id',
        'row_index',
        'batch_id',
        'trx_id',
        'entity',
        'customer_id',
        'sender_no',
        'amount',
        'trx_date',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'trx_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship: A BillingTransaction belongs to a BillingSystem.
     */
    public function billingSystem(): BelongsTo
    {
        return $this->belongsTo(BillingSystem::class, 'billing_system_id');
    }

    /**
     * Relationship: A BillingTransaction belongs to a Batch.
     * This is connected to the batch_id field from the new migration.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }
}