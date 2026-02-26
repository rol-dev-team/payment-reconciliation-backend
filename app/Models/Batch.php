<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $table = 'batches';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'upload_date',
        'vendor_file_count',
        'billing_file_count',
        'status',
        'started_at',
        'completed_at',
    ];

    /**
     * Cast fields to proper data types
     */
    protected $casts = [
        'upload_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relationship: A Batch has many BillingTransactions.
     */
    public function billingTransactions(): HasMany
    {
        return $this->hasMany(BillingTransaction::class, 'batch_id');
    }

    /**
     * Relationship: A Batch has many VendorTransactions.
     */
    public function vendorTransactions(): HasMany
    {
        return $this->hasMany(VendorTransaction::class, 'batch_id');
    }
}