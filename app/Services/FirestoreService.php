<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FirestoreService
{
    protected $db;
    protected $isConnected = false;

    public function __construct()
    {
        try {
            $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));
            $projectId = env('FIREBASE_PROJECT_ID');

            if (!file_exists($credentialsPath)) {
                throw new \Exception("Firebase credentials file not found at: {$credentialsPath}");
            }

            if (empty($projectId)) {
                throw new \Exception("FIREBASE_PROJECT_ID environment variable not set");
            }

            $this->db = new FirestoreClient([
                'keyFilePath' => $credentialsPath,
                'projectId' => $projectId,
            ]);

            $this->isConnected = true;
            Log::info("✅ Firestore connection established successfully");
        } catch (\Exception $e) {
            Log::critical("❌ Firestore connection failed: " . $e->getMessage());
            $this->db = null;
            $this->isConnected = false;
        }
    }

    /**
     * Check if Firestore is connected
     */
    public function isConnected(): bool
    {
        return $this->isConnected && $this->db !== null;
    }

    /**
     * Enhanced chat log with category tracking.
     */
    public function addChatLog(string $sessionId, string $question, string $answer, string $source, $userId = null, string $category = 'umum'): bool
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot save chat log: Firestore not connected");
            return false;
        }

        try {
            $data = [
                'session_id' => $sessionId,
                'question' => $question,
                'answer' => $answer,
                'source' => $source, // "dialogflow" | "firestore" | "openai" | "openai_fail"
                'category' => $category, // Add category for better analysis
                'timestamp' => new Timestamp(new \DateTime()),
                'user_id' => $userId,
            ];

            $docRef = $this->db->collection('chat_logs')->add($data);
            Log::info("Chat log saved with category '{$category}' and ID: " . $docRef->id());
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to save chat log: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search knowledge base using semantic and text similarity.
     */
    public function searchKnowledgeBase(string $query, float $semanticThreshold = 0.8, float $textThreshold = 60.0): ?string
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot search knowledge base: Firestore not connected");
            return null;
        }

        try {
            Log::info("🔍 Searching knowledge base for: '{$query}' with semantic threshold {$semanticThreshold} and text threshold {$textThreshold}%");

            $collection = $this->db->collection('knowledge_base');
            $documents = $collection->documents();

            $bestMatch = null;
            $highestSimilarity = 0.0;
            $totalDocs = 0;

            $queryEmbedding = $this->getEmbedding(trim($query));
            $useSemantic = !empty($queryEmbedding);

            foreach ($documents as $doc) {
                if (!$doc->exists()) continue;

                $totalDocs++;
                $data = $doc->data();
                $question = $data['question'] ?? '';

                if (empty($question)) continue;

                $similarity = 0.0;

                if ($useSemantic && isset($data['embedding']) && is_array($data['embedding'])) {
                    $docEmbedding = $data['embedding'];
                    if (count($docEmbedding) === count($queryEmbedding)) {
                        $similarity = $this->cosineSimilarity($queryEmbedding, $docEmbedding);
                        Log::info("📍 Semantic similarity: {$similarity} with '{$question}'");
                    }
                }

                if ($similarity < $semanticThreshold) {
                    similar_text(strtolower(trim($query)), strtolower(trim($question)), $percent);
                    $similarity = $percent / 100;
                    Log::info("📍 Text similarity: {$similarity} with '{$question}'");
                }

                if ($similarity > $highestSimilarity && $similarity >= min($semanticThreshold, $textThreshold / 100)) {
                    $highestSimilarity = $similarity;
                    $bestMatch = $data['answer'] ?? null;
                }
            }

            Log::info("Knowledge base search completed. Total docs: {$totalDocs}, Best match similarity: {$highestSimilarity}");

            if ($bestMatch) {
                Log::info("✅ Knowledge base match found with {$highestSimilarity} similarity");
                return $bestMatch;
            } else {
                Log::info("❌ No knowledge base match found");
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Knowledge base search error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add a new entry to the knowledge base with a specified category.
     */
    public function addKnowledgeBase(string $question, string $answer, string $category): bool
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot add to knowledge base: Firestore not connected");
            return false;
        }

        try {
            $existingAnswer = $this->searchKnowledgeBase($question, 0.95, 95.0);
            if ($existingAnswer) {
                Log::info("Knowledge base entry already exists for similar question: '{$question}'");
                return false;
            }

            $embedding = $this->getEmbedding(trim($question));

            $data = [
                'question' => trim($question),
                'answer' => trim($answer),
                'source' => 'openai',
                'created_at' => new Timestamp(new \DateTime()),
                'category' => $category, // Use category from controller
            ];

            if (!empty($embedding)) {
                $data['embedding'] = $embedding;
            }

            $docRef = $this->db->collection('knowledge_base')->add($data);
            Log::info("✅ Knowledge base entry added with ID: " . $docRef->id());
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to add knowledge base entry: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update system metrics with source and category breakdown.
     */
    public function updateSystemMetrics(string $source, string $category): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $today = date('Y-m-d');
            $docRef = $this->db->collection('system_metrics')->document($today);
            $doc = $docRef->snapshot();

            $data = $doc->exists() ? $doc->data() : [
                'date' => $today,
                'total_queries' => 0,
                'category_queries' => [],
                'source_hits' => [],
                'created_at' => new Timestamp(new \DateTime()),
            ];

            // Update counts
            $data['total_queries'] = ($data['total_queries'] ?? 0) + 1;
            $data['category_queries'][$category] = ($data['category_queries'][$category] ?? 0) + 1;
            $data['source_hits'][$source]['total'] = ($data['source_hits'][$source]['total'] ?? 0) + 1;
            $data['source_hits'][$source]['categories'][$category] = ($data['source_hits'][$source]['categories'][$category] ?? 0) + 1;

            $docRef->set($data);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update system metrics: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent conversation history for a session.
     */
    public function getConversationHistory(string $sessionId, int $limit = 3): array
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot get conversation history: Firestore not connected");
            return [];
        }

        try {
            $query = $this->db->collection('chat_logs')
                ->where('session_id', '=', $sessionId)
                ->orderBy('timestamp', 'DESC')
                ->limit($limit);

            $documents = $query->documents();
            $history = [];
            foreach ($documents as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $history[] = [
                        'user' => $data['question'] ?? '',
                        'assistant' => $data['answer'] ?? '',
                    ];
                }
            }

            // Reverse to get chronological order (oldest first) for context
            return array_reverse($history);
        } catch (\Exception $e) {
            Log::error("Failed to get conversation history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get aggregated performance analytics for a given number of days.
     */
    public function getPerformanceAnalytics(int $days = 7): array
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot get performance analytics: Firestore not connected");
            return [];
        }

        try {
            $endDate = new \DateTime('today');
            $startDate = (new \DateTime('today'))->sub(new \DateInterval('P' . ($days - 1) . 'D'));

            $query = $this->db->collection('system_metrics')
                ->where('date', '>=', $startDate->format('Y-m-d'))
                ->where('date', '<=', $endDate->format('Y-m-d'));

            $documents = $query->documents();

            $analytics = [
                'total_queries' => 0,
                'source_hits' => [],
                'category_queries' => [],
                'daily_data' => [],
            ];

            foreach ($documents as $doc) {
                if (!$doc->exists()) continue;

                $data = $doc->data();
                $date = $data['date'];

                $analytics['total_queries'] += $data['total_queries'] ?? 0;

                foreach ($data['source_hits'] ?? [] as $source => $hits) {
                    $analytics['source_hits'][$source] = ($analytics['source_hits'][$source] ?? 0) + ($hits['total'] ?? 0);
                }

                foreach ($data['category_queries'] ?? [] as $category => $count) {
                    $analytics['category_queries'][$category] = ($analytics['category_queries'][$category] ?? 0) + $count;
                }

                $analytics['daily_data'][$date] = [
                    'total_queries' => $data['total_queries'] ?? 0,
                    'source_hits' => $data['source_hits'] ?? [],
                ];
            }

            ksort($analytics['daily_data']);

            return $analytics;
        } catch (\Exception $e) {
            Log::error("Failed to get performance analytics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Save error log to Firestore.
     */
    public function addErrorLog(string $errorMessage, string $userMessage = '', array $context = []): bool
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot save error log: Firestore not connected");
            return false;
        }

        try {
            $data = [
                'error_message' => $errorMessage,
                'user_message' => $userMessage,
                'timestamp' => new Timestamp(new \DateTime()),
                'context' => $context,
                'user_agent' => request()->header('User-Agent', ''),
                'ip_address' => request()->ip(),
            ];

            $docRef = $this->db->collection('error_logs')->add($data);
            Log::info("Error log saved with ID: " . $docRef->id());
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to save error log: " . $e->getMessage());
            return false;
        }
    }

    // --- Helper and Unchanged Methods ---

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($a); $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        return ($normA == 0 || $normB == 0) ? 0.0 : $dot / (sqrt($normA) * sqrt($normB));
    }

    private function getEmbedding(string $text): array
    {
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey) || empty($text)) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ]);

            if ($response->successful()) {
                return $response->json()['data'][0]['embedding'];
            }

            Log::error('OpenAI Embedding HTTP Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('OpenAI Embedding Exception: ' . $e->getMessage());
            return [];
        }
    }
}
