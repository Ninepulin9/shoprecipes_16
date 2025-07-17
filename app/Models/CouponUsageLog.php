<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsageLog extends Model
{
    use HasFactory;

    protected $table = 'coupon_usage_logs';

    protected $fillable = [
        'user_id',
        'coupon_id',
        'coupon_code',
        'discount_amount',
        'used_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}