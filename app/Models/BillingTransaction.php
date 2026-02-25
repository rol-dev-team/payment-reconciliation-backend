<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingTransaction extends Model
{
    use HasFactory;

    protected $table = 'billing_transactions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'billing_system_id',
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
     * Relationships
     */

    // BillingTransaction belongs to a BillingSystem
    public function billingSystem()
    {
        return $this->belongsTo(BillingSystem::class, 'billing_system_id');
    }
}