<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Google\Cloud\Dialogflow\V2\Client\IntentsClient;
use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{
    private $projectId = 'websitebot-etqi'; // Ganti dengan ID Project Anda
    private $credentialsPath;
    private $lastDetectedIntent = null;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/google/dialogflow-credentials.json');
    }

    public function index()
    {
        return view('chatbot');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        try {
            if (!class_exists(SessionsClient::class)) {
                Log::error('SessionsClient class not found. Pastikan library google/cloud-dialogflow sudah di-install.');
                return response()->json(['message' => 'Library Dialogflow tidak ditemukan. Silakan install dengan composer.'], 500);
            }

            $message = $request->input('message');
            $sessionId = $request->input('session_id', session()->getId());
            $conversationHistory = $request->input('conversation_history', []);

            $response = $this->detectIntent($message);

            // Ambil intent yang terdeteksi untuk generate suggestions
            $detectedIntent = $this->getLastDetectedIntent();

            // Generate suggestions menggunakan analytics
            $suggestions = $this->generateSmartSuggestions($detectedIntent, $message, $sessionId, $conversationHistory);

            // Record interaction untuk analytics
            \App\Services\ChatbotAnalyticsService::recordInteraction([
                'session_id' => $sessionId,
                'user_message' => $message,
                'detected_intent' => $detectedIntent,
                'bot_response' => $response,
                'suggestions' => $suggestions,
                'confidence' => 0.8, // Bisa diambil dari Dialogflow response
                'context' => [
                    'conversation_history' => $conversationHistory,
                    'timestamp' => now()->toISOString()
                ]
            ]);

            return response()->json([
                'message' => $response,
                'suggestions' => $suggestions,
                'detected_intent' => $detectedIntent,
                'session_id' => $sessionId
            ]);
        } catch (\Exception $e) {
            Log::error("Exception: " . $e->getMessage());
            Log::error("File: " . $e->getFile());
            Log::error("Line: " . $e->getLine());
            return response()->json(['message' => 'Maaf, terjadi kesalahan di server.'], 500);
        }
    }

    public function detectIntent(string $text)
    {
        $sessionId = session()->getId();

        $sessionsClient = new SessionsClient([
            'credentials' => $this->credentialsPath
        ]);

        $session = $sessionsClient->sessionName($this->projectId, $sessionId);

        // Buat TextInput
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode('id');

        // Bungkus dalam QueryInput
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        // Buat request DetectIntentRequest
        $detectIntentRequest = new DetectIntentRequest();
        $detectIntentRequest->setSession($session);
        $detectIntentRequest->setQueryInput($queryInput);

        // Kirim ke Dialogflow
        $response = $sessionsClient->detectIntent($detectIntentRequest);

        // Ambil response
        $queryResult = $response->getQueryResult();
        $fulfillmentText = $queryResult->getFulfillmentText();

        // Simpan intent yang terdeteksi
        $this->lastDetectedIntent = $queryResult->getIntent()->getDisplayName();

        return $fulfillmentText;
    }

    private function getLastDetectedIntent()
    {
        return $this->lastDetectedIntent;
    }

    /**
     * Get all intents from Dialogflow dynamically
     */
    public function getAllIntents()
    {
        $cacheKey = 'dialogflow_intents_' . $this->projectId;

        return Cache::remember($cacheKey, 3600, function () {
            try {
                $intentsClient = new IntentsClient([
                    'credentials' => $this->credentialsPath
                ]);

                $parent = $intentsClient->projectAgentName($this->projectId);
                $intents = [];

                foreach ($intentsClient->listIntents($parent) as $intent) {
                    $intentName = $intent->getDisplayName();
                    $trainingPhrases = [];

                    // Ambil training phrases
                    foreach ($intent->getTrainingPhrases() as $phrase) {
                        foreach ($phrase->getParts() as $part) {
                            $trainingPhrases[] = $part->getText();
                        }
                    }

                    $intents[$intentName] = [
                        'name' => $intentName,
                        'training_phrases' => $trainingPhrases,
                        'keywords' => $this->extractKeywords($trainingPhrases)
                    ];
                }

                return $intents;
            } catch (\Exception $e) {
                Log::error('Error fetching intents: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Extract keywords from training phrases
     */
    private function extractKeywords($trainingPhrases)
    {
        $keywords = [];
        $stopWords = ['apa', 'adalah', 'yang', 'dan', 'atau', 'untuk', 'dari', 'ke', 'di', 'pada', 'dengan', 'ini', 'itu', 'saya', 'kamu', 'mereka', 'kita'];

        foreach ($trainingPhrases as $phrase) {
            $words = preg_split('/\s+/', strtolower($phrase));
            foreach ($words as $word) {
                $word = preg_replace('/[^\w]/', '', $word);
                if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                    $keywords[] = $word;
                }
            }
        }

        return array_unique($keywords);
    }

    /**
     * Generate suggestions dynamically based on intent relationships
     */
    private function generateDynamicSuggestions($detectedIntent, $userMessage)
    {
        $allIntents = $this->getAllIntents();

        if (empty($allIntents)) {
            return $this->getFallbackSuggestions($userMessage);
        }

        // Jika intent terdeteksi, cari intent yang related
        if ($detectedIntent && isset($allIntents[$detectedIntent])) {
            $suggestions = $this->findRelatedIntents($detectedIntent, $allIntents);
            if (!empty($suggestions)) {
                return array_slice($suggestions, 0, 4);
            }
        }

        // Jika tidak ada intent yang terdeteksi atau tidak ada related intents
        // Cari berdasarkan similarity dengan user message
        $suggestions = $this->findSimilarIntents($userMessage, $allIntents);

        return array_slice($suggestions, 0, 4);
    }

    /**
     * Find related intents based on keywords and semantic similarity
     */
    private function findRelatedIntents($currentIntent, $allIntents)
    {
        $suggestions = [];
        $currentKeywords = $allIntents[$currentIntent]['keywords'] ?? [];

        // Define intent relationship rules
        $relationshipRules = $this->getIntentRelationshipRules();

        // Check if there are predefined relationships
        if (isset($relationshipRules[$currentIntent])) {
            foreach ($relationshipRules[$currentIntent] as $relatedIntent) {
                if (isset($allIntents[$relatedIntent])) {
                    $suggestions[] = $this->intentToUserFriendlyText($relatedIntent);
                }
            }
        }

        // If no predefined relationships, find by keyword similarity
        if (empty($suggestions)) {
            foreach ($allIntents as $intentName => $intentData) {
                if ($intentName === $currentIntent) continue;

                $similarity = $this->calculateKeywordSimilarity($currentKeywords, $intentData['keywords']);
                if ($similarity > 0.2) { // Threshold untuk similarity
                    $suggestions[] = $this->intentToUserFriendlyText($intentName);
                }
            }
        }

        return array_unique($suggestions);
    }

    /**
     * Find similar intents based on user message
     */
    private function findSimilarIntents($userMessage, $allIntents)
    {
        $suggestions = [];
        $userKeywords = $this->extractKeywords([strtolower($userMessage)]);

        $scores = [];
        foreach ($allIntents as $intentName => $intentData) {
            $score = $this->calculateKeywordSimilarity($userKeywords, $intentData['keywords']);
            if ($score > 0.1) {
                $scores[$intentName] = $score;
            }
        }

        // Sort by score descending
        arsort($scores);

        foreach (array_keys($scores) as $intentName) {
            $suggestions[] = $this->intentToUserFriendlyText($intentName);
        }

        return $suggestions;
    }

    /**
     * Calculate similarity between two keyword arrays
     */
    private function calculateKeywordSimilarity($keywords1, $keywords2)
    {
        if (empty($keywords1) || empty($keywords2)) {
            return 0;
        }

        $intersection = array_intersect($keywords1, $keywords2);
        $union = array_unique(array_merge($keywords1, $keywords2));

        return count($intersection) / count($union);
    }

    /**
     * Convert intent name to user-friendly text
     */
    private function intentToUserFriendlyText($intentName)
    {
        // Mapping intent names to user-friendly suggestions
        $mapping = [
            'Default Welcome Intent' => 'Mulai percakapan',
            'Default Fallback Intent' => 'Bantuan umum',
        ];

        if (isset($mapping[$intentName])) {
            return $mapping[$intentName];
        }

        // Auto-generate user-friendly text from intent name
        $text = str_replace(['_', '.'], ' ', $intentName);
        $text = ucwords(strtolower($text));

        // Replace common patterns
        $replacements = [
            'Bi ' => 'BI ',
            'Genbi' => 'GenBI',
            'Cara ' => 'Bagaimana cara ',
            'Syarat' => 'Apa syarat',
            'Pengertian' => 'Apa itu',
        ];

        foreach ($replacements as $search => $replace) {
            $text = str_replace($search, $replace, $text);
        }

        return $text;
    }

    /**
     * Define relationship rules between intents
     */
    private function getIntentRelationshipRules()
    {
        return [
            // Beasiswa related
            'beasiswa_bi_pengertian' => ['beasiswa_bi_syarat', 'beasiswa_bi_cara_daftar', 'genbi_pengertian'],
            'beasiswa_bi_syarat' => ['beasiswa_bi_cara_daftar', 'beasiswa_bi_pengertian'],
            'beasiswa_bi_cara_daftar' => ['beasiswa_bi_syarat', 'genbi_cara_menjadi_anggota'],

            // GenBI related
            'genbi_pengertian' => ['genbi_tujuan', 'genbi_cara_menjadi_anggota', 'beasiswa_bi_pengertian'],
            'genbi_tujuan' => ['genbi_pengertian', 'genbi_sejarah', 'genbi_kewajiban_anggota'],
            'genbi_cara_menjadi_anggota' => ['genbi_kewajiban_anggota', 'beasiswa_bi_cara_daftar'],
            'genbi_kewajiban_anggota' => ['genbi_cara_menjadi_anggota', 'genbi_pengertian'],
            'genbi_sejarah' => ['genbi_pengertian', 'genbi_tujuan'],

            // General
            'FAQ_General' => ['genbi_pengertian', 'beasiswa_bi_pengertian'],
            'Default Welcome Intent' => ['genbi_pengertian', 'beasiswa_bi_pengertian', 'FAQ_General'],
        ];
    }

    /**
     * Fallback suggestions when dynamic generation fails
     */
    private function getFallbackSuggestions($userMessage)
    {
        $message = strtolower($userMessage);

        if (strpos($message, 'beasiswa') !== false) {
            return ['Syarat beasiswa', 'Cara mendaftar', 'Timeline pendaftaran', 'Tips lolos seleksi'];
        }

        if (strpos($message, 'genbi') !== false) {
            return ['Apa itu GenBI', 'Program GenBI', 'Cara bergabung', 'Kegiatan GenBI'];
        }

        if (strpos($message, 'daftar') !== false) {
            return ['Syarat pendaftaran', 'Link pendaftaran', 'Dokumen dibutuhkan', 'Kontak admin'];
        }

        return ['Info GenBI', 'Info Beasiswa', 'Bantuan umum', 'FAQ'];
    }

    /**
     * API endpoint to refresh intent cache
     */
    public function refreshIntents()
    {
        $cacheKey = 'dialogflow_intents_' . $this->projectId;
        Cache::forget($cacheKey);

        $intents = $this->getAllIntents();

        return response()->json([
            'status' => 'success',
            'message' => 'Intent cache refreshed',
            'intents_count' => count($intents)
        ]);
    }

    /**
     * Generate smart suggestions using multiple strategies
     */
    private function generateSmartSuggestions($detectedIntent, $userMessage, $sessionId, $conversationHistory)
    {
        // Strategy 1: Analytics-based suggestions
        $analyticsSuggestions = \App\Services\ChatbotAnalyticsService::getAnalyticsBasedSuggestions($detectedIntent, $conversationHistory);

        // Strategy 2: ML-optimized suggestions
        $mlSuggestions = \App\Services\ChatbotAnalyticsService::getMLOptimizedSuggestions($detectedIntent);

        // Strategy 3: User behavior optimization
        $behaviorSuggestions = \App\Services\ChatbotAnalyticsService::optimizeSuggestionsForUser($sessionId, $conversationHistory);

        // Strategy 4: Dynamic Dialogflow-based suggestions
        $dynamicSuggestions = $this->generateDynamicSuggestions($detectedIntent, $userMessage);

        // Combine all strategies
        $allSuggestions = array_unique(array_merge(
            $analyticsSuggestions,
            $mlSuggestions,
            $behaviorSuggestions,
            $dynamicSuggestions
        ));

        // If we have analytics data, use it for ranking
        if (!empty($analyticsSuggestions)) {
            $rankedSuggestions = \App\Services\IntentRelationshipService::rankIntentSuggestions(
                $allSuggestions,
                $conversationHistory
            );
        } else {
            // Fallback to dynamic suggestions
            $rankedSuggestions = $allSuggestions;
        }

        return array_slice($rankedSuggestions, 0, 4);
    }

    /**
     * Track suggestion interaction
     */
    public function trackInteraction(Request $request)
    {
        $request->validate([
            'suggestion' => 'required|string',
            'current_intent' => 'nullable|string',
            'session_id' => 'required|string'
        ]);

        try {
            \App\Services\ChatbotAnalyticsService::recordSuggestionClick(
                $request->input('session_id'),
                $request->input('suggestion'),
                $request->input('current_intent', 'unknown')
            );

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error tracking interaction: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Get fresh suggestions for current context
     */
    public function getFreshSuggestions(Request $request)
    {
        $request->validate([
            'intent' => 'required|string',
            'session_id' => 'required|string'
        ]);

        try {
            $conversationHistory = $request->input('conversation_history', []);
            $suggestions = $this->generateSmartSuggestions(
                $request->input('intent'),
                '',
                $request->input('session_id'),
                $conversationHistory
            );

            return response()->json([
                'suggestions' => $suggestions,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting fresh suggestions: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get suggestions'], 500);
        }
    }

    /**
     * Get analytics dashboard (admin only)
     */
    public function getAnalyticsDashboard(Request $request)
    {
        try {
            $days = $request->input('days', 7);
            $dashboardData = \App\Services\ChatbotAnalyticsService::getDashboardData($days);

            return response()->json($dashboardData);
        } catch (\Exception $e) {
            Log::error('Error getting analytics dashboard: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get analytics'], 500);
        }
    }

    /**
     * Export analytics data (admin only)
     */
    public function exportAnalytics(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'format' => 'in:json,csv'
        ]);

        try {
            $data = \App\Services\ChatbotAnalyticsService::exportAnalyticsData(
                $request->input('start_date'),
                $request->input('end_date'),
                $request->input('format', 'json')
            );

            $filename = 'chatbot_analytics_' . date('Y-m-d') . '.' . $request->input('format', 'json');

            return response($data)
                ->header('Content-Type', $request->input('format') === 'csv' ? 'text/csv' : 'application/json')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            Log::error('Error exporting analytics: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export data'], 500);
        }
    }
}
