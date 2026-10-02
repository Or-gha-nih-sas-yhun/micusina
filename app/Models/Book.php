<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'guest',
        'date',
        'time',
        'reservation_price',
        'deposit_amount',
        'payment_method',
        'gcash_reference',
        'gcash_transaction_reference',
        'paymongo_checkout_id',
        'paymongo_checkout_url',
        'paymongo_payment_id',
        'payment_status',
        'paid_at',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $hidden = [
        'paymongo_checkout_url',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
