<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date', 'end_date',
        'vendor_file_count', 'billing_file_count',
        'status', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function vendorTransactions(): HasMany
    {
        return $this->hasMany(VendorTransaction::class, 'batch_id');
    }

    public function billingTransactions(): HasMany
    {
        return $this->hasMany(BillingTransaction::class, 'batch_id');
    }
}