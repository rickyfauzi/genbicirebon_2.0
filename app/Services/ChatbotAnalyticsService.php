<?php

namespace App\Services;

use App\Models\ChatbotInteraction;
use App\Models\ChatbotIntentUsage;
use App\Models\ChatbotSuggestionAnalytics;
use App\Models\ChatbotSession;
use Illuminate\Support\Facades\Log;

class ChatbotAnalyticsService
{
    /**
     * Record a complete chatbot interaction
     */
    public static function recordInteraction($data)
    {
        try {
            // Record the interaction
            $interaction = ChatbotInteraction::create([
                'session_id' => $data['session_id'],
                'user_message' => $data['user_message'],
                'detected_intent' => $data['detected_intent'] ?? null,
                'bot_response' => $data['bot_response'],
                'suggestions_shown' => $data['suggestions'] ?? null,
                'confidence_score' => $data['confidence'] ?? 0,
                'context_data' => $data['context'] ?? null,
                'created_at' => now()
            ]);

            // Record intent usage
            if (!empty($data['detected_intent'])) {
                ChatbotIntentUsage::recordUsage(
                    $data['detected_intent'],
                    $data['confidence'] ?? 0
                );
            }

            // Record suggestions shown
            if (!empty($data['suggestions'])) {
                foreach ($data['suggestions'] as $suggestion) {
                    ChatbotSuggestionAnalytics::recordSuggestionShown(
                        $suggestion,
                        $data['detected_intent'] ?? 'unknown'
                    );
                }
            }

            // Update session
            self::updateSession($data['session_id'], $data['detected_intent'] ?? null);

            return $interaction;
        } catch (\Exception $e) {
            Log::error('Error recording chatbot interaction: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Record suggestion click
     */
    public static function recordSuggestionClick($sessionId, $suggestionText, $contextIntent)
    {
        try {
            // Record suggestion click
            ChatbotSuggestionAnalytics::recordSuggestionClicked($suggestionText, $contextIntent);

            // Update session
            $session = ChatbotSession::where('session_id', $sessionId)->first();
            if ($session) {
                $session->incrementSuggestionClicks();
            }
        } catch (\Exception $e) {
            Log::error('Error recording suggestion click: ' . $e->getMessage());
        }
    }

    /**
     * Update or create session
     */
    private static function updateSession($sessionId, $intent = null)
    {
        $session = ChatbotSession::firstOrNew(['session_id' => $sessionId]);

        if (!$session->exists) {
            $session->started_at = now();
            $session->message_count = 1;
        } else {
            $session->incrementMessageCount();
        }

        if ($intent) {
            $session->addIntentToFlow($intent);
        }

        $session->save();
        return $session;
    }

    /**
     * Get dynamic suggestions based on analytics
     */
    public static function getAnalyticsBasedSuggestions($currentIntent, $sessionHistory = [])
    {
        try {
            // Get popular next intents based on historical data
            $popularNext = self::getPopularNextIntents($currentIntent);

            // Get high-performing suggestions
            $performingSuggestions = self::getHighPerformingSuggestions($currentIntent);

            // Combine and rank suggestions
            $suggestions = array_merge($popularNext, $performingSuggestions);

            // Remove duplicates and limit
            $uniqueSuggestions = array_unique($suggestions);

            return array_slice($uniqueSuggestions, 0, 4);
        } catch (\Exception $e) {
            Log::error('Error getting analytics-based suggestions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get popular next intents after current intent
     */
    private static function getPopularNextIntents($currentIntent, $days = 30)
    {
        // This would require more complex query to find intent sequences
        // For now, return based on overall popularity
        $popular = ChatbotIntentUsage::getPopularIntents($days, 10);

        return $popular->filter(function ($item) use ($currentIntent) {
            return $item->intent_name !== $currentIntent;
        })->pluck('intent_name')->toArray();
    }

    /**
     * Get high-performing suggestions for given intent
     */
    private static function getHighPerformingSuggestions($contextIntent, $days = 30)
    {
        $suggestions = ChatbotSuggestionAnalytics::where('context_intent', $contextIntent)
            ->where('date', '>=', now()->subDays($days))
            ->where('click_rate', '>', 10) // Only suggestions with >10% click rate
            ->orderBy('click_rate', 'desc')
            ->limit(10)
            ->pluck('suggestion_text')
            ->toArray();

        return $suggestions;
    }

    /**
     * Generate analytics dashboard data
     */
    public static function getDashboardData($days = 7)
    {
        return [
            'total_interactions' => ChatbotInteraction::where('created_at', '>=', now()->subDays($days))->count(),
            'unique_sessions' => ChatbotSession::where('created_at', '>=', now()->subDays($days))->count(),
            'active_sessions' => ChatbotSession::getActiveSessionsCount(),
            'avg_session_duration' => ChatbotSession::getAverageSessionDuration($days),
            'conversion_rate' => ChatbotSession::getConversionRate($days),
            'popular_intents' => ChatbotIntentUsage::getPopularIntents($days),
            'best_suggestions' => ChatbotSuggestionAnalytics::getBestPerformingSuggestions($days),
            'intent_distribution' => self::getIntentDistribution($days),
            'hourly_usage' => self::getHourlyUsage($days),
        ];
    }

    /**
     * Get intent distribution
     */
    private static function getIntentDistribution($days = 7)
    {
        return ChatbotIntentUsage::where('date', '>=', now()->subDays($days))
            ->groupBy('intent_name')
            ->selectRaw('intent_name, SUM(usage_count) as total')
            ->orderBy('total', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get hourly usage pattern
     */
    private static function getHourlyUsage($days = 7)
    {
        return ChatbotInteraction::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();
    }

    /**
     * Track user journey and optimize suggestions
     */
    public static function optimizeSuggestionsForUser($sessionId, $conversationHistory)
    {
        try {
            $session = ChatbotSession::where('session_id', $sessionId)->first();

            if (!$session) {
                return [];
            }

            // Analyze user's journey patterns
            $intentFlow = $session->intent_flow ?? [];
            $suggestions = [];

            // If user is stuck (repeating similar intents)
            if (self::isUserStuck($intentFlow)) {
                $suggestions = self::getHelpSuggestions();
            }
            // If user is progressing well
            elseif (self::isUserProgressing($intentFlow)) {
                $suggestions = self::getAdvancedSuggestions($intentFlow);
            }
            // Default behavioral suggestions
            else {
                $suggestions = self::getBehavioralSuggestions($intentFlow);
            }

            return $suggestions;
        } catch (\Exception $e) {
            Log::error('Error optimizing suggestions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user seems stuck in conversation
     */
    private static function isUserStuck($intentFlow)
    {
        if (count($intentFlow) < 3) return false;

        $recentIntents = array_slice($intentFlow, -3);
        $intentNames = array_column($recentIntents, 'intent');

        // Check for repetition or fallback patterns
        $fallbackCount = count(array_filter($intentNames, function ($intent) {
            return strpos($intent, 'fallback') !== false || strpos($intent, 'default') !== false;
        }));

        return $fallbackCount >= 2;
    }

    /**
     * Check if user is progressing through logical flow
     */
    private static function isUserProgressing($intentFlow)
    {
        if (count($intentFlow) < 2) return false;

        // Define logical progressions
        $progressions = [
            'beasiswa_bi_pengertian' => ['beasiswa_bi_syarat', 'beasiswa_bi_cara_daftar'],
            'genbi_pengertian' => ['genbi_cara_menjadi_anggota', 'genbi_kewajiban_anggota'],
            'beasiswa_bi_syarat' => ['beasiswa_bi_cara_daftar'],
        ];

        $recent = array_slice($intentFlow, -2);
        if (count($recent) < 2) return false;

        $firstIntent = $recent[0]['intent'];
        $secondIntent = $recent[1]['intent'];

        return isset($progressions[$firstIntent]) &&
            in_array($secondIntent, $progressions[$firstIntent]);
    }

    /**
     * Get help suggestions when user is stuck
     */
    private static function getHelpSuggestions()
    {
        return [
            'Hubungi admin untuk bantuan',
            'Lihat FAQ lengkap',
            'Mulai dari awal',
            'Pertanyaan umum'
        ];
    }

    /**
     * Get advanced suggestions for progressing users
     */
    private static function getAdvancedSuggestions($intentFlow)
    {
        $lastIntent = end($intentFlow)['intent'] ?? '';

        $advancedMap = [
            'beasiswa_bi_cara_daftar' => [
                'Tips sukses wawancara',
                'Dokumen pendukung',
                'Timeline seleksi detail',
                'Pengalaman alumni'
            ],
            'genbi_kewajiban_anggota' => [
                'Program wajib GenBI',
                'Sanksi dan konsekuensi',
                'Sistem poin keaktifan',
                'Achievement requirements'
            ]
        ];

        return $advancedMap[$lastIntent] ?? self::getDefaultAdvancedSuggestions();
    }

    /**
     * Get behavioral suggestions based on patterns
     */
    private static function getBehavioralSuggestions($intentFlow)
    {
        $categories = [];

        foreach ($intentFlow as $flow) {
            $category = self::categorizeIntentForBehavior($flow['intent']);
            $categories[] = $category;
        }

        $dominantCategory = self::getMostFrequentCategory($categories);

        return self::getSuggestionsByBehavioralCategory($dominantCategory);
    }

    /**
     * Categorize intent for behavioral analysis
     */
    private static function categorizeIntentForBehavior($intent)
    {
        if (strpos($intent, 'beasiswa') !== false) return 'scholarship_focused';
        if (strpos($intent, 'genbi') !== false) return 'community_focused';
        if (strpos($intent, 'cara') !== false || strpos($intent, 'bagaimana') !== false) return 'process_focused';
        if (strpos($intent, 'syarat') !== false) return 'requirement_focused';

        return 'general';
    }

    /**
     * Get most frequent category
     */
    private static function getMostFrequentCategory($categories)
    {
        if (empty($categories)) return 'general';

        $counts = array_count_values($categories);
        arsort($counts);

        return array_key_first($counts);
    }

    /**
     * Get suggestions by behavioral category
     */
    private static function getSuggestionsByBehavioralCategory($category)
    {
        $behavioralSuggestions = [
            'scholarship_focused' => [
                'Benefit lengkap beasiswa',
                'Syarat pemeliharaan beasiswa',
                'Besaran beasiswa terbaru',
                'Cara perpanjang beasiswa'
            ],
            'community_focused' => [
                'Event GenBI mendatang',
                'Program sosial GenBI',
                'Networking sesama GenBI',
                'Alumni success stories'
            ],
            'process_focused' => [
                'Step-by-step guide',
                'Checklist pendaftaran',
                'Timeline lengkap',
                'Common mistakes'
            ],
            'requirement_focused' => [
                'Dokumen detail',
                'Kriteria penilaian',
                'Tips penuhi syarat',
                'Alternative requirements'
            ]
        ];

        return $behavioralSuggestions[$category] ?? $this->getDefaultAdvancedSuggestions();
    }

    /**
     * Get default advanced suggestions
     */
    private static function getDefaultAdvancedSuggestions()
    {
        return [
            'Info lebih detail',
            'Tips dan trik',
            'Pengalaman nyata',
            'Pertanyaan lanjutan'
        ];
    }

    /**
     * Machine learning style suggestion optimization
     */
    public static function getMLOptimizedSuggestions($currentIntent, $userProfile = [])
    {
        try {
            // Get base suggestions from analytics
            $baseSuggestions = self::getAnalyticsBasedSuggestions($currentIntent);

            // Apply user profile weighting
            $weightedSuggestions = self::applyUserProfileWeighting($baseSuggestions, $userProfile);

            // Apply time-based optimization
            $timeSuggestions = self::applyTimeBasedOptimization($weightedSuggestions);

            // Apply A/B testing logic (if enabled)
            $finalSuggestions = self::applyABTesting($timeSuggestions);

            return array_slice($finalSuggestions, 0, 4);
        } catch (\Exception $e) {
            Log::error('Error getting ML optimized suggestions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Apply user profile weighting to suggestions
     */
    private static function applyUserProfileWeighting($suggestions, $userProfile)
    {
        if (empty($userProfile)) return $suggestions;

        // Example: boost suggestions based on user interests
        $weighted = [];

        foreach ($suggestions as $suggestion) {
            $weight = 1.0;

            // Boost based on user preferences (example logic)
            if (isset($userProfile['interests'])) {
                foreach ($userProfile['interests'] as $interest) {
                    if (stripos($suggestion, $interest) !== false) {
                        $weight += 0.3;
                    }
                }
            }

            // Boost based on user level (beginner, intermediate, advanced)
            if (isset($userProfile['level'])) {
                if ($userProfile['level'] === 'beginner' && stripos($suggestion, 'dasar') !== false) {
                    $weight += 0.2;
                } elseif ($userProfile['level'] === 'advanced' && stripos($suggestion, 'detail') !== false) {
                    $weight += 0.2;
                }
            }

            $weighted[] = ['suggestion' => $suggestion, 'weight' => $weight];
        }

        // Sort by weight
        usort($weighted, function ($a, $b) {
            return $b['weight'] <=> $a['weight'];
        });

        return array_column($weighted, 'suggestion');
    }

    /**
     * Apply time-based optimization
     */
    private static function applyTimeBasedOptimization($suggestions)
    {
        $hour = (int) now()->format('H');

        // Business hours optimization
        if ($hour >= 9 && $hour <= 17) {
            // During business hours, prioritize contact and admin related suggestions
            $businessPriority = ['admin', 'kontak', 'hubungi'];

            $suggestions = self::reorderByPriority($suggestions, $businessPriority);
        }

        // Evening optimization
        if ($hour >= 18 || $hour <= 6) {
            // Evening/night: prioritize self-service suggestions
            $selfServicePriority = ['faq', 'panduan', 'info', 'cara'];

            $suggestions = self::reorderByPriority($suggestions, $selfServicePriority);
        }

        return $suggestions;
    }

    /**
     * Reorder suggestions by priority keywords
     */
    private static function reorderByPriority($suggestions, $priorityKeywords)
    {
        $prioritized = [];
        $others = [];

        foreach ($suggestions as $suggestion) {
            $isPriority = false;
            foreach ($priorityKeywords as $keyword) {
                if (stripos($suggestion, $keyword) !== false) {
                    $prioritized[] = $suggestion;
                    $isPriority = true;
                    break;
                }
            }

            if (!$isPriority) {
                $others[] = $suggestion;
            }
        }

        return array_merge($prioritized, $others);
    }

    /**
     * Apply A/B testing logic
     */
    private static function applyABTesting($suggestions)
    {
        // Example A/B test: 50% users get different suggestion ordering
        if (rand(1, 100) <= 50) {
            // Group A: Original order
            return $suggestions;
        } else {
            // Group B: Shuffled order
            $shuffled = $suggestions;
            shuffle($shuffled);
            return $shuffled;
        }
    }

    /**
     * Clean old analytics data
     */
    public static function cleanOldData($daysToKeep = 90)
    {
        $cutoffDate = now()->subDays($daysToKeep);

        try {
            ChatbotInteraction::where('created_at', '<', $cutoffDate)->delete();
            ChatbotIntentUsage::where('date', '<', $cutoffDate->toDateString())->delete();
            ChatbotSuggestionAnalytics::where('date', '<', $cutoffDate->toDateString())->delete();
            ChatbotSession::where('created_at', '<', $cutoffDate)->delete();

            Log::info("Cleaned chatbot analytics data older than {$daysToKeep} days");
        } catch (\Exception $e) {
            Log::error('Error cleaning old analytics data: ' . $e->getMessage());
        }
    }

    /**
     * Export analytics data for external analysis
     */
    public static function exportAnalyticsData($startDate, $endDate, $format = 'json')
    {
        try {
            $data = [
                'interactions' => ChatbotInteraction::whereBetween('created_at', [$startDate, $endDate])->get(),
                'intent_usage' => ChatbotIntentUsage::whereBetween('date', [$startDate, $endDate])->get(),
                'suggestions' => ChatbotSuggestionAnalytics::whereBetween('date', [$startDate, $endDate])->get(),
                'sessions' => ChatbotSession::whereBetween('created_at', [$startDate, $endDate])->get(),
            ];

            if ($format === 'csv') {
                return self::convertToCSV($data);
            }

            return json_encode($data, JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('Error exporting analytics data: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Convert data to CSV format
     */
    private static function convertToCSV($data)
    {
        // Simple CSV conversion - can be enhanced
        $csv = '';

        foreach ($data as $table => $records) {
            $csv .= "=== {$table} ===\n";

            if ($records->isNotEmpty()) {
                $headers = array_keys($records->first()->toArray());
                $csv .= implode(',', $headers) . "\n";

                foreach ($records as $record) {
                    $csv .= implode(',', $record->toArray()) . "\n";
                }
            }

            $csv .= "\n";
        }

        return $csv;
    }
}
