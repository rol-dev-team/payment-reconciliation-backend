<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorTransaction extends Model
{
    use HasFactory;

    protected $table = 'vendor_transactions';

    protected $fillable = [
        'batch_id',
        'wallet_id',
        'trx_id',
        'sender_no',
        'trx_date',
        'amount',
    ];

    protected $casts = [
        'trx_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship: VendorTransaction belongs to a Wallet
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    /**
     * Relationship: VendorTransaction belongs to a Batch
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }
}