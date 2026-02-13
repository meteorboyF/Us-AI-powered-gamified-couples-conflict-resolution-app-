<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiBridgeSuggestion extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_SENT = 'sent';

    public const STATUS_DISCARDED = 'discarded';

    protected $fillable = [
        'couple_id',
        'user_id',
        'source_context',
        'suggested_message',
        'status',
        'approved_at',
        'sent_at',
    ];

    protected $casts = [
        'source_context' => 'array',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
