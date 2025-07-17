<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'usage_limit',
        'used_count',
        'expired_at'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'expired_at' => 'datetime'
    ];

    // Constants สำหรับประเภทคูปอง
    const TYPE_PERCENT = 'percent';
    const TYPE_FIXED = 'fixed';
    const TYPE_POINT = 'point';

    /**
     * ตรวจสอบว่าคูปอง
     */
    public function isValid()
    {
        $now = now();
        
        // ตรวจสอบวันหมดอายุ
        if ($this->expired_at && $this->expired_at < $now) {
            return false;
        }
        
        // ตรวจสอบจำนวนการใช้งาน
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }
        
        return true;
    }

    /**
     * ตรวจสอบว่าคูปองใช้ได้กับยอดสั่งซื้อนี้หรือไม่
     */
    public function canUseWithAmount($amount)
    {
        return $this->isValid() && $amount > 0;
    }

    /**
     * คำนวณส่วนลดจากคูปอง (ไม่รวม Point)
     */
    public function calculateDiscount($orderAmount)
    {
        if (!$this->isValid()) {
            return 0;
        }

        switch ($this->discount_type) {
            case self::TYPE_PERCENT:
                return min(($orderAmount * $this->discount_value) / 100, $orderAmount);
            case self::TYPE_FIXED:
                return min($this->discount_value, $orderAmount);
            case self::TYPE_POINT:
                return 0; // Point coupon ไม่ลดราคา
            default:
                return 0;
        }
    }

    /**
     * ได้ Point
     */
    public function getBonusPoints()
    {
        if (!$this->isValid() || $this->discount_type !== self::TYPE_POINT) {
            return 0;
        }

        return $this->discount_value;
    }

    /**
     * ตรวจสอบว่าเป็นคูปอง Point หรือไม่
     */
    public function isPointCoupon()
    {
        return $this->discount_type === self::TYPE_POINT;
    }

  
    public function isDiscountCoupon()
    {
        return in_array($this->discount_type, [self::TYPE_PERCENT, self::TYPE_FIXED]);
    }

   
    public function incrementUsage()
    {
        $this->increment('used_count');
    }

    
    public function decrementUsage()
    {
        if ($this->used_count > 0) {
            $this->decrement('used_count');
        }
    }

    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expired_at')
              ->orWhere('expired_at', '>', now());
        })->where(function($q) {
            $q->whereNull('usage_limit')
              ->orWhereColumn('used_count', '<', 'usage_limit');
        });
    }

    
    public function scopePointType($query)
    {
        return $query->where('discount_type', self::TYPE_POINT);
    }

    
    public function scopeDiscountType($query)
    {
        return $query->whereIn('discount_type', [self::TYPE_PERCENT, self::TYPE_FIXED]);
    }
}