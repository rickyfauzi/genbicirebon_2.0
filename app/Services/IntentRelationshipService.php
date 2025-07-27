<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class IntentRelationshipService
{
    /**
     * Analyze intent relationships dynamically
     */
    public static function buildDynamicRelationships($allIntents)
    {
        $relationships = [];

        foreach ($allIntents as $intentName => $intentData) {
            $relationships[$intentName] = self::findRelatedIntentsByCategories($intentName, $allIntents);
        }

        return $relationships;
    }

    /**
     * Find related intents by categorizing them
     */
    private static function findRelatedIntentsByCategories($currentIntent, $allIntents)
    {
        $related = [];
        $category = self::categorizeIntent($currentIntent);

        foreach ($allIntents as $intentName => $intentData) {
            if ($intentName === $currentIntent) continue;

            $otherCategory = self::categorizeIntent($intentName);

            // Same category intents are related
            if ($category === $otherCategory) {
                $related[] = $intentName;
            }

            // Cross-category relationships
            if (self::hasCrossCategoryRelation($category, $otherCategory)) {
                $related[] = $intentName;
            }
        }

        return array_slice($related, 0, 4); // Limit to 4 suggestions
    }

    /**
     * Categorize intent based on naming patterns
     */
    private static function categorizeIntent($intentName)
    {
        $intentLower = strtolower($intentName);

        // Define category patterns
        $categories = [
            'beasiswa' => ['beasiswa', 'scholarship', 'bantuan', 'biaya'],
            'genbi' => ['genbi', 'generasi', 'komunitas', 'organisasi'],
            'pendaftaran' => ['daftar', 'register', 'pendaftaran', 'aplikasi'],
            'syarat' => ['syarat', 'requirement', 'persyaratan', 'kriteria'],
            'program' => ['program', 'kegiatan', 'activity', 'event'],
            'informasi' => ['info', 'informasi', 'pengertian', 'definisi', 'apa'],
            'prosedur' => ['cara', 'bagaimana', 'langkah', 'prosedur', 'how'],
            'kontak' => ['kontak', 'hubungi', 'contact', 'admin'],
            'waktu' => ['kapan', 'jadwal', 'timeline', 'deadline', 'waktu'],
            'tempat' => ['dimana', 'lokasi', 'alamat', 'tempat'],
            'faq' => ['faq', 'tanya', 'jawab', 'bantuan', 'help'],
            'sejarah' => ['sejarah', 'history', 'asal', 'background'],
            'tujuan' => ['tujuan', 'visi', 'misi', 'goal', 'objective']
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($intentLower, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'general';
    }

    /**
     * Define cross-category relationships
     */
    private static function hasCrossCategoryRelation($category1, $category2)
    {
        $crossRelations = [
            'beasiswa' => ['genbi', 'pendaftaran', 'syarat'],
            'genbi' => ['beasiswa', 'program', 'informasi'],
            'pendaftaran' => ['beasiswa', 'syarat', 'prosedur'],
            'syarat' => ['beasiswa', 'pendaftaran', 'prosedur'],
            'program' => ['genbi', 'informasi'],
            'informasi' => ['genbi', 'beasiswa', 'program'],
            'prosedur' => ['pendaftaran', 'syarat'],
        ];

        return isset($crossRelations[$category1]) &&
            in_array($category2, $crossRelations[$category1]);
    }

    /**
     * Generate contextual suggestions based on conversation flow
     */
    public static function generateContextualSuggestions($conversationHistory, $allIntents)
    {
        if (empty($conversationHistory)) {
            return self::getWelcomeSuggestions($allIntents);
        }

        $lastIntent = end($conversationHistory);
        $category = self::categorizeIntent($lastIntent);

        return self::getSuggestionsByCategory($category, $allIntents);
    }

    /**
     * Get welcome suggestions
     */
    private static function getWelcomeSuggestions($allIntents)
    {
        $welcomeCategories = ['informasi', 'beasiswa', 'genbi', 'faq'];
        $suggestions = [];

        foreach ($allIntents as $intentName => $intentData) {
            $category = self::categorizeIntent($intentName);
            if (in_array($category, $welcomeCategories)) {
                $suggestions[] = $intentName;
            }
        }

        return array_slice($suggestions, 0, 4);
    }

    /**
     * Get suggestions by category
     */
    private static function getSuggestionsByCategory($category, $allIntents)
    {
        $suggestions = [];

        foreach ($allIntents as $intentName => $intentData) {
            $intentCategory = self::categorizeIntent($intentName);

            // Same category or related category
            if (
                $intentCategory === $category ||
                self::hasCrossCategoryRelation($category, $intentCategory)
            ) {
                $suggestions[] = $intentName;
            }
        }

        return array_slice($suggestions, 0, 4);
    }

    /**
     * Calculate intent popularity for ranking
     */
    public static function calculateIntentPopularity($intentName, $usageStats = [])
    {
        // Base popularity score
        $score = 1.0;

        // Boost popular categories
        $popularCategories = ['beasiswa', 'genbi', 'informasi', 'pendaftaran'];
        $category = self::categorizeIntent($intentName);

        if (in_array($category, $popularCategories)) {
            $score *= 1.5;
        }

        // Use actual usage statistics if available
        if (isset($usageStats[$intentName])) {
            $score *= (1 + $usageStats[$intentName] * 0.1);
        }

        return $score;
    }

    /**
     * Smart intent ranking
     */
    public static function rankIntentSuggestions($suggestions, $context = [], $usageStats = [])
    {
        $ranked = [];

        foreach ($suggestions as $suggestion) {
            $score = self::calculateIntentPopularity($suggestion, $usageStats);

            // Context-based boosting
            if (!empty($context)) {
                $contextCategory = self::categorizeIntent(end($context));
                $suggestionCategory = self::categorizeIntent($suggestion);

                if (self::hasCrossCategoryRelation($contextCategory, $suggestionCategory)) {
                    $score *= 1.3;
                }
            }

            $ranked[] = [
                'intent' => $suggestion,
                'score' => $score
            ];
        }

        // Sort by score descending
        usort($ranked, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_column($ranked, 'intent');
    }
}
