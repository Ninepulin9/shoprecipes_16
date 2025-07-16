<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'usage_limit',
        'used_count',
        'expired_at'
    ];

    public function isValid()
    {
        return (!$this->expired_at || $this->expired_at > now()) &&
            (!$this->usage_limit || $this->used_count < $this->usage_limit);
    }
}
