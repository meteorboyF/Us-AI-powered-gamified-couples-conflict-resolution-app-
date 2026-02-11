<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemoryReaction extends Model
{
    protected $fillable = [
        'memory_id',
        'user_id',
        'reaction',
    ];

    /**
     * Get the memory this reaction belongs to
     */
    public function memory()
    {
        return $this->belongsTo(Memory::class);
    }

    /**
     * Get the user who reacted
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Available reaction types
     */
    public static function getReactionTypes(): array
    {
        return [
            'heart' => '❤️',
            'smile' => '😊',
            'laugh' => '😂',
            'cry' => '😢',
            'wow' => '😮',
        ];
    }

    /**
     * Get emoji for reaction
     */
    public function getEmoji(): string
    {
        $reactions = self::getReactionTypes();
        return $reactions[$this->reaction] ?? '❤️';
    }
}
