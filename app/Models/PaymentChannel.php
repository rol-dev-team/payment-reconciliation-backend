<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentChannel extends Model
{
    use HasFactory;

    protected $table = 'payment_channels';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payment_method_id',
        'channel_name',
        'status',
    ];

    /**
     * Relationships
     */

    // PaymentChannel belongs to a PaymentMethod
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    // PaymentChannel has many Wallets
    public function wallets()
    {
        return $this->hasMany(Wallet::class, 'payment_channel_id');
    }
}