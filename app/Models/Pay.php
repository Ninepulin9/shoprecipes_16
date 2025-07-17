<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pay extends Model
{
    use HasFactory;

    protected $table = 'pays';

    protected $fillable = [
        'payment_number',
        'table_id',
        'user_id',
        'total',
    ];

    // Relationship กับ User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship กับ Table
    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    // Relationship กับ PayGroup
    public function payGroups()
    {
        return $this->hasMany(PayGroup::class, 'pay_id');
    }
}