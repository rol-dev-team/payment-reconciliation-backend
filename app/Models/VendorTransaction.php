<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorTransaction extends Model
{
    use HasFactory;

    protected $table = 'vendor_transactions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'batch_id',
        'trx_id',
        'sender_no',
        'trx_date',
        'amount',
        'wallet_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'trx_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    // VendorTransaction belongs to a Wallet
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}