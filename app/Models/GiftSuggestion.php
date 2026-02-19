<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftSuggestion extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'gift_request_id',
        'title',
        'category',
        'price_band',
        'rationale',
        'personalization_tip',
        'is_favorite',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function giftRequest(): BelongsTo
    {
        return $this->belongsTo(GiftRequest::class, 'gift_request_id');
    }
}
