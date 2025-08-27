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

            Log::info("✅ Knowledge base berhasil direset. Total dokumen dihapus: {$deleted}");
            return true;
        } catch (\Exception $e) {
            Log::error("❌ Gagal reset knowledge base: " . $e->getMessage());
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
     * Simpan log percakapan user ↔ chatbot
     */
    public function addChatLog(string $sessionId, string $question, string $answer, string $source, $userId = null): bool
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
                'source' => $source, // "dialogflow" | "firestore" | "openai"
                'timestamp' => new Timestamp(new \DateTime()),
                'user_id' => $userId,
            ];

            $docRef = $this->db->collection('chat_logs')->add($data);
            Log::info("Chat log saved with ID: " . $docRef->id());
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to save chat log: " . $e->getMessage());
            return false;
        }
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
                    // Fallback to text similarity
                    similar_text(strtolower(trim($query)), strtolower(trim($question)), $percent);
                    $similarity = $percent / 100;
                    Log::info("📍 Text similarity: {$similarity} with '{$question}'");
                }

                if ($similarity > $highestSimilarity && $similarity >= min($semanticThreshold, $textThreshold / 100)) {
                    $highestSimilarity = $similarity;
                    $bestMatch = $data['answer'] ?? null;
                }
            }

            Log::info("Knowledge base search completed. Total docs: {$totalDocs}, Best match: {$highestSimilarity}");

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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/embeddings', [
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
                    'created_at' => new Timestamp(new \DateTime()),
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
            'beasiswa' => ['beasiswa', 'scholarship', 'bantuan', 'dana'],
            'kegiatan' => ['kegiatan', 'acara', 'event', 'program'],
            'pendaftaran' => ['daftar', 'registrasi', 'syarat', 'pendaftaran'],
            'informasi_umum' => ['apa', 'siapa', 'dimana', 'kapan', 'bagaimana'],
            'kontak' => ['kontak', 'alamat', 'telepon', 'email'],
            'sejarah' => ['sejarah', 'awal', 'didirikan', 'berdiri'],
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
     * Get statistics for admin dashboard
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
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error("Failed to get statistics: " . $e->getMessage());
            return [];
        }
    }
}
