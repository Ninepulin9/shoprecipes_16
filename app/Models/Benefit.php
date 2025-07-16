<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Benefit extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'name',
        'description',
        'type',
        'point_required',
        'category',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'is_active',
        'start_date',
        'end_date',
        'expired_at',
        'applicable_categories',
        'image'
    ];
}
