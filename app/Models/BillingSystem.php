<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingSystem extends Model
{
    use HasFactory;

    protected $table = 'billing_systems';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'billing_name',
    ];

    /**
     * Relationships
     */

    // A BillingSystem has many Comparisons
    public function comparisons()
    {
        return $this->hasMany(Comparison::class, 'billing_system_id');
    }
}
