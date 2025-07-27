<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_message',
        'detected_intent',
        'bot_response',
        'suggestions_shown',
        'selected_suggestion',
        'confidence_score',
        'context_data'
    ];

    protected $casts = [
        'suggestions_shown' => 'array',
        'context_data' => 'array',
        'confidence_score' => 'float'
    ];

    public $timestamps = false;

    protected $dates = ['created_at'];

    // Relationships
    public function session()
    {
        return $this->belongsTo(ChatbotSession::class, 'session_id', 'session_id');
    }

    // Scopes
    public function scopeByIntent($query, $intent)
    {
        return $query->where('detected_intent', $intent);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeWithSuggestions($query)
    {
        return $query->whereNotNull('suggestions_shown');
    }
}
