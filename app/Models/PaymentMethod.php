<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_methods';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Relationships
     */

    // PaymentMethod has many PaymentChannels
    public function paymentChannels()
    {
        return $this->hasMany(PaymentChannel::class, 'payment_method_id');
    }
}