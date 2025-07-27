<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotIntentUsage extends Model
{
    use HasFactory;

    protected $table = 'chatbot_intent_usage';

    protected $fillable = [
        'intent_name',
        'usage_count',
        'avg_confidence',
        'date'
    ];

    protected $casts = [
        'avg_confidence' => 'float',
        'date' => 'date'
    ];

    public static function recordUsage($intentName, $confidence = 0)
    {
        $today = now()->toDateString();

        $record = self::firstOrNew([
            'intent_name' => $intentName,
            'date' => $today
        ]);

        if ($record->exists) {
            // Update existing record
            $record->usage_count++;
            $record->avg_confidence = (($record->avg_confidence * ($record->usage_count - 1)) + $confidence) / $record->usage_count;
        } else {
            // Create new record
            $record->usage_count = 1;
            $record->avg_confidence = $confidence;
        }

        $record->save();
        return $record;
    }

    public static function getPopularIntents($days = 7, $limit = 10)
    {
        return self::where('date', '>=', now()->subDays($days))
            ->groupBy('intent_name')
            ->selectRaw('intent_name, SUM(usage_count) as total_usage, AVG(avg_confidence) as avg_conf')
            ->orderBy('total_usage', 'desc')
            ->limit($limit)
            ->get();
    }
}
