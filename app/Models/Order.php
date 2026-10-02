<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The application has always exposed an order by its six-digit primary-key
     * value. Keep that public identifier in one place without changing the
     * database key or introducing a duplicate column.
     */
    protected $appends = ['order_number'];

    protected $fillable = [
        'user_id',
        'checkout_group_id',
        'name',
        'email',
        'phone',
        'address',
        'title',
        'price',
        'quantity',
        'image',
        'delivery_status',
        'payment_method',
        'payment_status',
        'payment_reference',
        'rider_id',
        'confirmed_by',
        'confirmed_at',
    ];

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOrderNumberAttribute(): string
    {
        return str_pad((string) $this->getKey(), 6, '0', STR_PAD_LEFT);
    }
}
