<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingFile extends Model
{
    protected $fillable = [
        'batch_id',
        'billing_system_id',
        'original_filename',
        'stored_path',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function billingSystem(): BelongsTo
    {
        return $this->belongsTo(BillingSystem::class);
    }
}