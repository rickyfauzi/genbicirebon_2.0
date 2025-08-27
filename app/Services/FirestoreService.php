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
            Log::info("Firestore connection established successfully");
        } catch (\Exception $e) {
            Log::critical("Firestore connection failed: " . $e->getMessage());
            $this->db = null;
            $this->isConnected = false;
        }
    }

    /**
     * Hapus seluruh data di knowledge_base
     */
    public function resetKnowledgeBase(): bool
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot reset knowledge base: Firestore not connected");
            return false;
        }

        try {
            $collection = $this->db->collection('knowledge_base');
            $documents = $collection->documents();

            $deleted = 0;
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $document->reference()->delete();
                    $deleted++;
                }
            }

            Log::info("Knowledge base berhasil direset. Total dokumen dihapus: {$deleted}");
            return true;
        } catch (\Exception $e) {
            Log::error("Gagal reset knowledge base: " . $e->getMessage());
            return false;
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
     * Get conversation history for a session
     */
    public function getChatHistory(string $sessionId, int $limit = 3): array
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot get chat history: Firestore not connected");
            return [];
        }

        try {
            $collection = $this->db->collection('chat_logs');
            $query = $collection
                ->where('session_id', '=', $sessionId)
                ->orderBy('timestamp', 'DESC')
                ->limit($limit);

            $documents = $query->documents();
            $history = [];

            foreach ($documents as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $history[] = [
                        'user_message' => $data['question'] ?? '',
                        'bot_response' => $data['answer'] ?? '',
                        'timestamp' => $data['timestamp'] ?? null,
                        'source' => $data['source'] ?? ''
                    ];
                }
            }

            // Return in chronological order (oldest first)
            return array_reverse($history);
        } catch (\Exception $e) {
            Log::error("Failed to get chat history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search knowledge base with advanced scoring and relevance
     */
    public function searchKnowledgeBaseWithScoring(string $query, array $keywords = [], array $contextKeywords = []): ?array
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot search knowledge base: Firestore not connected");
            return null;
        }

        try {
            Log::info("Searching knowledge base with scoring for: '{$query}'");

            $collection = $this->db->collection('knowledge_base');
            $documents = $collection->documents();

            $bestMatch = null;
            $highestScore = 0.0;
            $totalDocs = 0;

            $queryEmbedding = $this->getEmbedding(trim($query));
            $useSemantic = !empty($queryEmbedding);

            foreach ($documents as $doc) {
                if (!$doc->exists()) continue;

                $totalDocs++;
                $data = $doc->data();
                $question = $data['question'] ?? '';
                $answer = $data['answer'] ?? '';

                if (empty($question)) continue;

                $score = $this->calculateRelevanceScore(
                    $query,
                    $question,
                    $answer,
                    $keywords,
                    $contextKeywords,
                    $queryEmbedding,
                    $data['embedding'] ?? null
                );

                Log::info("Relevance score: {$score} for question: '{$question}'");

                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestMatch = [
                        'answer' => $answer,
                        'confidence' => $score,
                        'matched_question' => $question
                    ];
                }
            }

            Log::info("Knowledge base search completed. Total docs: {$totalDocs}, Best score: {$highestScore}");

            return $bestMatch;
        } catch (\Exception $e) {
            Log::error("Knowledge base search with scoring error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate comprehensive relevance score
     */
    private function calculateRelevanceScore(
        string $query,
        string $question,
        string $answer,
        array $keywords = [],
        array $contextKeywords = [],
        array $queryEmbedding = null,
        array $docEmbedding = null
    ): float {
        $score = 0.0;
        $weights = [
            'semantic' => 0.4,
            'text_similarity' => 0.25,
            'keyword_match' => 0.2,
            'context_match' => 0.1,
            'length_relevance' => 0.05
        ];

        // 1. Semantic similarity (if embeddings available)
        if (!empty($queryEmbedding) && !empty($docEmbedding) && count($queryEmbedding) === count($docEmbedding)) {
            $semanticScore = $this->cosineSimilarity($queryEmbedding, $docEmbedding);
            $score += $semanticScore * $weights['semantic'];
        } else {
            // Fallback: use text similarity for semantic weight too
            similar_text(strtolower(trim($query)), strtolower(trim($question)), $percent);
            $score += ($percent / 100) * $weights['semantic'];
        }

        // 2. Text similarity
        similar_text(strtolower(trim($query)), strtolower(trim($question)), $percent);
        $score += ($percent / 100) * $weights['text_similarity'];

        // 3. Keyword matching
        if (!empty($keywords)) {
            $questionWords = $this->extractWords($question);
            $keywordMatches = count(array_intersect($keywords, $questionWords));
            $keywordScore = min($keywordMatches / count($keywords), 1.0);
            $score += $keywordScore * $weights['keyword_match'];
        }

        // 4. Context relevance
        if (!empty($contextKeywords)) {
            $allDocWords = $this->extractWords($question . ' ' . $answer);
            $contextMatches = count(array_intersect($contextKeywords, $allDocWords));
            $contextScore = min($contextMatches / max(count($contextKeywords), 1), 1.0);
            $score += $contextScore * $weights['context_match'];
        }

        // 5. Length relevance (prefer comprehensive but not too long answers)
        $answerLength = strlen($answer);
        $lengthScore = 1.0;
        if ($answerLength < 20) {
            $lengthScore = 0.5; // Too short
        } elseif ($answerLength > 1000) {
            $lengthScore = 0.7; // Too long
        }
        $score += $lengthScore * $weights['length_relevance'];

        return min($score, 1.0);
    }

    /**
     * Extract words for matching
     */
    private function extractWords(string $text): array
    {
        $words = str_word_count(strtolower($text), 1, 'àáâãäåæçèéêëìíîïñòóôõöøùúûüý');
        return array_filter($words, function ($word) {
            return strlen($word) > 2;
        });
    }

    /**
     * Add knowledge base entry with metadata
     */
    public function addKnowledgeBaseWithMetadata(string $question, string $answer, array $metadata = []): bool
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot add to knowledge base: Firestore not connected");
            return false;
        }

        try {
            // Check for existing similar entries
            $existingEntry = $this->searchKnowledgeBase($question, 0.95, 95.0);
            if ($existingEntry) {
                Log::info("Knowledge base entry already exists for similar question: '{$question}'");
                return false;
            }

            $embedding = $this->getEmbedding(trim($question));

            $data = [
                'question' => trim($question),
                'answer' => trim($answer),
                'source' => $metadata['source'] ?? 'openai',
                'created_at' => new Timestamp(new \DateTime()),
                'category' => $this->categorizeQuestion($question),
                'confidence' => $metadata['confidence'] ?? 0.8,
                'context_keywords' => $metadata['context_keywords'] ?? [],
                'last_accessed' => new Timestamp(new \DateTime()),
                'access_count' => 1
            ];

            if (!empty($embedding)) {
                $data['embedding'] = $embedding;
            }

            // Add custom metadata
            foreach ($metadata as $key => $value) {
                if (!in_array($key, ['source', 'confidence', 'context_keywords'])) {
                    $data[$key] = $value;
                }
            }

            $docRef = $this->db->collection('knowledge_base')->add($data);
            Log::info("Knowledge base entry with metadata added with ID: " . $docRef->id());
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to add knowledge base entry with metadata: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save chat log with conversation context
     */
    public function addChatLogWithContext(
        string $sessionId,
        string $question,
        string $answer,
        string $source,
        $userId = null,
        array $conversationHistory = []
    ): bool {
        if (!$this->isConnected()) {
            Log::warning("Cannot save chat log: Firestore not connected");
            return false;
        }

        try {
            $data = [
                'session_id' => $sessionId,
                'question' => $question,
                'answer' => $answer,
                'source' => $source,
                'timestamp' => new Timestamp(new \DateTime()),
                'user_id' => $userId,
                'conversation_context' => $conversationHistory,
                'question_length' => strlen($question),
                'answer_length' => strlen($answer),
                'response_time' => microtime(true), // For potential performance tracking
                'user_agent' => request()->header('User-Agent', ''),
                'ip_address' => request()->ip()
            ];

            $docRef = $this->db->collection('chat_logs')->add($data);
            Log::info("Chat log with context saved with ID: " . $docRef->id());
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to save chat log with context: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Simpan log percakapan user ↔ chatbot (original method kept for backward compatibility)
     */
    public function addChatLog(string $sessionId, string $question, string $answer, string $source, $userId = null): bool
    {
        return $this->addChatLogWithContext($sessionId, $question, $answer, $source, $userId, []);
    }

    /**
     * Cari jawaban di knowledge base berdasarkan kemiripan semantik menggunakan embedding jika tersedia, fallback ke teks similarity.
     */
    public function searchKnowledgeBase(string $query, float $semanticThreshold = 0.8, float $textThreshold = 60.0): ?string
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot search knowledge base: Firestore not connected");
            return null;
        }

        try {
            Log::info("Searching knowledge base for: '{$query}' with semantic threshold {$semanticThreshold} and text threshold {$textThreshold}%");

            $collection = $this->db->collection('knowledge_base');
            $documents = $collection->documents();

            $bestMatch = null;
            $highestSimilarity = 0.0;
            $totalDocs = 0;
            $bestDocRef = null;

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
                        Log::info("Semantic similarity: {$similarity} with '{$question}'");
                    }
                }

                if ($similarity < $semanticThreshold) {
                    // Fallback to text similarity
                    similar_text(strtolower(trim($query)), strtolower(trim($question)), $percent);
                    $similarity = $percent / 100;
                    Log::info("Text similarity: {$similarity} with '{$question}'");
                }

                if ($similarity > $highestSimilarity && $similarity >= min($semanticThreshold, $textThreshold / 100)) {
                    $highestSimilarity = $similarity;
                    $bestMatch = $data['answer'] ?? null;
                    $bestDocRef = $doc->reference();
                }
            }

            // Update access statistics for the matched document
            if ($bestDocRef && $bestMatch) {
                try {
                    $bestDocRef->update([
                        ['path' => 'last_accessed', 'value' => new Timestamp(new \DateTime())],
                        ['path' => 'access_count', 'value' => \Google\Cloud\Firestore\FieldValue::increment(1)]
                    ]);
                } catch (\Exception $e) {
                    Log::warning("Could not update access statistics: " . $e->getMessage());
                }
            }

            Log::info("Knowledge base search completed. Total docs: {$totalDocs}, Best match: {$highestSimilarity}");

            if ($bestMatch) {
                Log::info("Knowledge base match found with {$highestSimilarity} similarity");
                return $bestMatch;
            } else {
                Log::info("No knowledge base match found");
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Knowledge base search error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tambah entri baru ke knowledge base dengan embedding.
     */
    public function addKnowledgeBase(string $question, string $answer): bool
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot add to knowledge base: Firestore not connected");
            return false;
        }

        try {
            // Cek dulu apakah pertanyaan serupa sudah ada untuk menghindari duplikasi
            $existingAnswer = $this->searchKnowledgeBase($question, 0.95, 95.0);
            if ($existingAnswer) {
                Log::info("Knowledge base entry already exists for similar question: '{$question}'");
                return false; // Tidak error, tapi tidak perlu ditambahkan
            }

            $embedding = $this->getEmbedding(trim($question));

            $data = [
                'question' => trim($question),
                'answer' => trim($answer),
                'source' => 'openai',
                'created_at' => new Timestamp(new \DateTime()),
                'category' => $this->categorizeQuestion($question),
                'last_accessed' => new Timestamp(new \DateTime()),
                'access_count' => 0
            ];

            if (!empty($embedding)) {
                $data['embedding'] = $embedding;
            }

            $docRef = $this->db->collection('knowledge_base')->add($data);
            Log::info("Knowledge base entry added with ID: " . $docRef->id());
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to add knowledge base entry: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hitung cosine similarity antara dua vektor.
     */
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

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Dapatkan embedding dari OpenAI.
     */
    private function getEmbedding(string $text): array
    {
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey) || empty($text)) {
            return [];
        }

        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/embeddings', [
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ]);

            if ($response->successful()) {
                return $response->json()['data'][0]['embedding'];
            }

            Log::warning('OpenAI Embedding HTTP Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::warning('OpenAI Embedding Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Simpan log error
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
                'session_id' => session()->getId(),
                'user_id' => auth()->id()
            ];

            $docRef = $this->db->collection('error_logs')->add($data);
            Log::info("Error log saved with ID: " . $docRef->id());
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to save error log: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update system metrics
     */
    public function updateSystemMetrics(string $source): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $today = date('Y-m-d');
            $docRef = $this->db->collection('system_metrics')->document($today);

            $doc = $docRef->snapshot();

            if ($doc->exists()) {
                // Update existing metrics
                $data = $doc->data();
                $data['total_queries'] = ($data['total_queries'] ?? 0) + 1;
                $data['last_updated'] = new Timestamp(new \DateTime());

                switch ($source) {
                    case 'dialogflow':
                        $data['dialogflow_success'] = ($data['dialogflow_success'] ?? 0) + 1;
                        break;
                    case 'firestore':
                        $data['firestore_success'] = ($data['firestore_success'] ?? 0) + 1;
                        break;
                    case 'openai':
                        $data['openai_fallback'] = ($data['openai_fallback'] ?? 0) + 1;
                        break;
                    case 'openai_fail':
                        $data['openai_failures'] = ($data['openai_failures'] ?? 0) + 1;
                        break;
                }

                $docRef->set($data, ['merge' => true]);
            } else {
                // Create new metrics
                $data = [
                    'date' => $today,
                    'total_queries' => 1,
                    'dialogflow_success' => $source === 'dialogflow' ? 1 : 0,
                    'firestore_success' => $source === 'firestore' ? 1 : 0,
                    'openai_fallback' => $source === 'openai' ? 1 : 0,
                    'openai_failures' => $source === 'openai_fail' ? 1 : 0,
                    'created_at' => new Timestamp(new \DateTime()),
                    'last_updated' => new Timestamp(new \DateTime()),
                ];

                $docRef->set($data);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update system metrics: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kategorisasi pertanyaan untuk knowledge base
     */
    private function categorizeQuestion(string $question): string
    {
        $question = strtolower($question);

        // Definisikan kategori berdasarkan kata kunci
        $categories = [
            'beasiswa' => ['beasiswa', 'scholarship', 'bantuan', 'dana', 'bi', 'bank indonesia'],
            'kegiatan' => ['kegiatan', 'acara', 'event', 'program', 'agenda', 'aktivitas'],
            'pendaftaran' => ['daftar', 'registrasi', 'syarat', 'pendaftaran', 'bergabung'],
            'informasi_umum' => ['apa', 'siapa', 'dimana', 'kapan', 'bagaimana', 'genbi'],
            'kontak' => ['kontak', 'alamat', 'telepon', 'email', 'hubungi', 'contact'],
            'sejarah' => ['sejarah', 'awal', 'didirikan', 'berdiri', 'mulai'],
            'benefit' => ['manfaat', 'keuntungan', 'benefit', 'dampak', 'hasil']
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($question, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'umum';
    }

    /**
     * Get enhanced statistics for admin dashboard
     */
    public function getStatistics(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $stats = [
                'total_chat_logs' => 0,
                'total_knowledge_base' => 0,
                'total_error_logs' => 0,
                'today_queries' => 0,
                'success_rate' => 0,
                'top_categories' => [],
                'recent_errors' => []
            ];

            // Count chat logs
            $chatLogs = $this->db->collection('chat_logs')->documents();
            $stats['total_chat_logs'] = iterator_count($chatLogs);

            // Count knowledge base
            $knowledgeBase = $this->db->collection('knowledge_base')->documents();
            $stats['total_knowledge_base'] = iterator_count($knowledgeBase);

            // Count error logs
            $errorLogs = $this->db->collection('error_logs')->documents();
            $stats['total_error_logs'] = iterator_count($errorLogs);

            // Get today's metrics
            $today = date('Y-m-d');
            $todayDoc = $this->db->collection('system_metrics')->document($today)->snapshot();
            if ($todayDoc->exists()) {
                $todayData = $todayDoc->data();
                $stats['today_queries'] = $todayData['total_queries'] ?? 0;

                $successful = ($todayData['dialogflow_success'] ?? 0) + ($todayData['firestore_success'] ?? 0) + ($todayData['openai_fallback'] ?? 0);
                $total = $todayData['total_queries'] ?? 1;
                $stats['success_rate'] = round(($successful / $total) * 100, 2);
            }

            // Get top categories from knowledge base
            $categories = [];
            $kbDocs = $this->db->collection('knowledge_base')->documents();
            foreach ($kbDocs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $category = $data['category'] ?? 'umum';
                    $categories[$category] = ($categories[$category] ?? 0) + 1;
                }
            }
            arsort($categories);
            $stats['top_categories'] = array_slice($categories, 0, 5, true);

            return $stats;
        } catch (\Exception $e) {
            Log::error("Failed to get statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean up old data (maintenance function)
     */
    public function cleanupOldData(int $daysToKeep = 30): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $cutoffDate = new \DateTime();
            $cutoffDate->sub(new \DateInterval("P{$daysToKeep}D"));
            $cutoffTimestamp = new Timestamp($cutoffDate);

            // Clean old chat logs
            $oldChatLogs = $this->db->collection('chat_logs')
                ->where('timestamp', '<', $cutoffTimestamp)
                ->documents();

            $deletedChats = 0;
            foreach ($oldChatLogs as $doc) {
                if ($doc->exists()) {
                    $doc->reference()->delete();
                    $deletedChats++;
                }
            }

            // Clean old error logs
            $oldErrorLogs = $this->db->collection('error_logs')
                ->where('timestamp', '<', $cutoffTimestamp)
                ->documents();

            $deletedErrors = 0;
            foreach ($oldErrorLogs as $doc) {
                if ($doc->exists()) {
                    $doc->reference()->delete();
                    $deletedErrors++;
                }
            }

            Log::info("Cleanup completed. Deleted {$deletedChats} chat logs and {$deletedErrors} error logs older than {$daysToKeep} days");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to cleanup old data: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get popular questions for analytics
     */
    public function getPopularQuestions(int $limit = 10): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $questions = [];
            $docs = $this->db->collection('knowledge_base')
                ->orderBy('access_count', 'DESC')
                ->limit($limit)
                ->documents();

            foreach ($docs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $questions[] = [
                        'question' => $data['question'] ?? '',
                        'category' => $data['category'] ?? 'umum',
                        'access_count' => $data['access_count'] ?? 0,
                        'last_accessed' => $data['last_accessed'] ?? null
                    ];
                }
            }

            return $questions;
        } catch (\Exception $e) {
            Log::error("Failed to get popular questions: " . $e->getMessage());
            return [];
        }
    }
}
