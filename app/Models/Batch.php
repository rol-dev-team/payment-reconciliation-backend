<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Cast fields to proper types
     */
    protected $casts = [
        'upload_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}