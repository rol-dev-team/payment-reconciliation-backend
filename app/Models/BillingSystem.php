<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingSystem extends Model
{
    use HasFactory;

    protected $table = 'billing_systems';

    protected $fillable = [
        'billing_name',
    ];

    /**
     * A BillingSystem has many Comparisons.
     * Use professional type hinting.
     */
    public function comparisons(): HasMany
    {
        return $this->hasMany(Comparison::class, 'billing_system_id');
    }
}