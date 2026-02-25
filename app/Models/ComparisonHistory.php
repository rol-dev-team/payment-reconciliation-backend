<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComparisonHistory extends Model
{
    use HasFactory;

    protected $table = 'comparison_histories';

    /**
     * The attributes that are mass assignable.
     * Currently, there are no custom fields, just timestamps.
     */
    protected $fillable = [
        // Add fields here if you add more columns in the future
    ];

    /**
     * Relationships
     * Add any future relationships here.
     */
}