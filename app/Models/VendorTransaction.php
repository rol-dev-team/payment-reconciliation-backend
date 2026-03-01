<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id','wallet_id','row_index','trx_id','sender_no','amount','trx_date'
    ];

    protected $casts = [
        'trx_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class,'batch_id');
    }
}