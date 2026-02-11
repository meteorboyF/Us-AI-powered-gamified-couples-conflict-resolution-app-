<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'couple_id',
        'type',
        'messages', // Cast to array
        'is_active',
    ];

    protected $casts = [
        'messages' => 'array',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * Helper to append a message to the history.
     */
    public function addMessage(string $role, string $content)
    {
        $messages = $this->messages ?? [];
        $messages[] = ['role' => $role, 'content' => $content];
        $this->messages = $messages;
        $this->save();
    }
}
