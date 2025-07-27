<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotSuggestionAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'suggestion_text',
        'context_intent',
        'shown_count',
        'clicked_count',
        'click_rate',
        'date'
    ];

    protected $casts = [
        'click_rate' => 'float',
        'date' => 'date'
    ];

    public static function recordSuggestionShown($suggestionText, $contextIntent)
    {
        $today = now()->toDateString();

        $record = self::firstOrNew([
            'suggestion_text' => $suggestionText,
            'context_intent' => $contextIntent,
            'date' => $today
        ]);

        $record->shown_count++;
        $record->calculateClickRate();
        $record->save();

        return $record;
    }

    public static function recordSuggestionClicked($suggestionText, $contextIntent)
    {
        $today = now()->toDateString();

        $record = self::firstOrNew([
            'suggestion_text' => $suggestionText,
            'context_intent' => $contextIntent,
            'date' => $today
        ]);

        $record->clicked_count++;
        $record->calculateClickRate();
        $record->save();

        return $record;
    }

    protected function calculateClickRate()
    {
        if ($this->shown_count > 0) {
            $this->click_rate = ($this->clicked_count / $this->shown_count) * 100;
        } else {
            $this->click_rate = 0;
        }
    }

    public static function getBestPerformingSuggestions($days = 7, $minShown = 5)
    {
        return self::where('date', '>=', now()->subDays($days))
            ->where('shown_count', '>=', $minShown)
            ->orderBy('click_rate', 'desc')
            ->limit(20)
            ->get();
    }
}
