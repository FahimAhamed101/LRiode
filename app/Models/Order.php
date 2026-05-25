<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $casts = [
        'subtotal' => 'float',
        'discount' => 'float',
        'tax' => 'float',
        'total' => 'float',
    ];
    protected $fillable = 
    [
        'user_id',
        'subtotal',
        'dsicount',
        'tax',
        'total',
        'name',
        'phone',
        'locality',
        'address',
        'city',
        'country',
        'landmark',
        'zip',
        'type',
        'status',
        'is_shipping_different',
        'delivered_date',
        'canceled_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(){
        return $this->hasMany(OrderItem::class);

    }
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'order_id');
    }


}
