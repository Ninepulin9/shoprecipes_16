<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // เพิ่มบรรทัดนี้

class Coupon extends Model
{
    use SoftDeletes; // เพิ่มบรรทัดนี้
    
    protected $table = 'coupons';
    
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'usage_limit',
        'used_count',
        'expired_at'
    ];
    
    protected $dates = ['deleted_at']; // เพิ่มบรรทัดนี้
    
    protected $casts = [
        'deleted_at' => 'datetime',
        'expired_at' => 'datetime'
    ];
}