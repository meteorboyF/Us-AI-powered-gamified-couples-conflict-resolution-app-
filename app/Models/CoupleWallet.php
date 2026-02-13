<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoupleWallet extends Model
{
    protected $fillable = [
        'couple_id',
        'love_seeds_balance',
    ];

    protected $casts = [
        'love_seeds_balance' => 'integer',
    ];

    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }
}
