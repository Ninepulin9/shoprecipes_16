<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardRedeemLog extends Model
{
    use HasFactory;

    protected $table = 'reward_redeem_logs';

    protected $fillable = [
        'user_id',
        'benefit_id',
        'point_used',
        'redeemed_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function benefit()
    {
        return $this->belongsTo(Benefit::class);
    }
}
