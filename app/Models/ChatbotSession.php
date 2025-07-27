<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'message_count',
        'suggestion_clicks',
        'intent_flow',
        'session_duration',
        'goal_achieved',
        'final_intent',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'intent_flow' => 'array',
        'goal_achieved' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    // Relationships
    public function interactions()
    {
        return $this->hasMany(ChatbotInteraction::class, 'session_id', 'session_id');
    }

    // Methods
    public function addIntentToFlow($intent)
    {
        $flow = $this->intent_flow ?? [];
        $flow[] = [
            'intent' => $intent,
            'timestamp' => now()->toISOString()
        ];

        $this->intent_flow = $flow;
        $this->final_intent = $intent;
        $this->save();
    }

    public function incrementMessageCount()
    {
        $this->increment('message_count');
    }

    public function incrementSuggestionClicks()
    {
        $this->increment('suggestion_clicks');
    }

    public function endSession()
    {
        $this->ended_at = now();

        if ($this->started_at) {
            $this->session_duration = $this->started_at->diffInSeconds($this->ended_at);
        }

        $this->save();
    }

    // Analytics methods
    public static function getActiveSessionsCount()
    {
        return self::whereNull('ended_at')
            ->where('started_at', '>=', now()->subHours(2))
            ->count();
    }

    public static function getAverageSessionDuration($days = 7)
    {
        return self::whereNotNull('session_duration')
            ->where('created_at', '>=', now()->subDays($days))
            ->avg('session_duration');
    }

    public static function getConversionRate($days = 7)
    {
        $total = self::where('created_at', '>=', now()->subDays($days))->count();
        $converted = self::where('created_at', '>=', now()->subDays($days))
            ->where('goal_achieved', true)
            ->count();

        return $total > 0 ? ($converted / $total) * 100 : 0;
    }
}
