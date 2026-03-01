<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_system_id','batch_id','row_index','trx_id','entity','customer_id','sender_no','amount','trx_date'
    ];

    protected $casts = [
        'trx_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function billingSystem(): BelongsTo
    {
        return $this->belongsTo(BillingSystem::class,'billing_system_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class,'batch_id');
    }
}