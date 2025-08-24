<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\FieldValue; // Gunakan FieldValue untuk timestamp server
use Illuminate\Support\Facades\Log;

class FirestoreService
{
    protected $db;
    protected $isConnected = false;

    public function __construct()
    {
        try {
            $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));
            $projectId = env('FIREBASE_PROJECT_ID');

            if (!file_exists($credentialsPath) || empty($projectId)) {
                throw new \Exception("Firebase credentials or project ID is not configured properly in .env");
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

    public function isConnected(): bool
    {
        return $this->isConnected && $this->db !== null;
    }

    // =========================================================================
    // FUNGSI INTI YANG DIPERBAIKI
    // =========================================================================

    /**
     * Cari jawaban di knowledge base menggunakan pencarian kata kunci yang efisien.
     * PERUBAHAN BESAR: Menggunakan query Firestore, bukan iterasi manual.
     *
     * @param string $questionText
     * @return string|null
     */
    public function searchKnowledgeBase(string $questionText): ?string
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot search KB: Firestore not connected");
            return null;
        }

        try {
            // 1. Ekstrak kata kunci dari pertanyaan pengguna
            $keywords = $this->extractKeywords($questionText);
            if (empty($keywords)) {
                return null;
            }
            Log::info("🔍 Searching KB with keywords: [" . implode(', ', $keywords) . "]");

            // 2. Gunakan query 'array-contains-any' untuk efisiensi maksimal
            // Firestore membatasi query ini hingga 10 item dalam array.
            $query = $this->db->collection('knowledge_base')
                ->where('keywords', 'array-contains-any', array_slice($keywords, 0, 10));
            $documents = $query->documents();

            $bestMatch = null;
            $highestScore = 0;

            // 3. Lakukan scoring HANYA pada dokumen yang dikembalikan oleh query (jauh lebih sedikit)
            foreach ($documents as $doc) {
                if ($doc->exists()) {
                    $docData = $doc->data();
                    $docKeywords = $docData['keywords'] ?? [];

                    // Skor berdasarkan jumlah kata kunci yang cocok
                    $score = count(array_intersect($keywords, $docKeywords));

                    if ($score > $highestScore) {
                        $highestScore = $score;
                        $bestMatch = $docData;
                    }
                }
            }

            // 4. Tentukan ambang batas kecocokan. Minimal 2 kata kunci cocok lebih baik.
            if ($bestMatch && $highestScore > 1) {
                Log::info("✅ KB Match Found! Score: {$highestScore}. Question: '{$bestMatch['question']}'");
                return $bestMatch['answer'];
            }

            Log::info("❌ No significant KB match found (Highest score: {$highestScore})");
            return null;
        } catch (\Exception $e) {
            Log::error("KB search error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tambah entri baru ke knowledge base, sekarang dengan field 'keywords'.
     * PERUBAHAN: Sekarang menyimpan field 'keywords' untuk pencarian cepat.
     *
     * @param string $question
     * @param string $answer
     * @return bool
     */
    public function addKnowledgeBase(string $question, string $answer): bool
    {
        if (!$this->isConnected()) {
            Log::warning("Cannot add to KB: Firestore not connected");
            return false;
        }

        try {
            $trimmedQuestion = trim($question);

            // Cek duplikasi dengan cara yang lebih efisien (exact match)
            $query = $this->db->collection('knowledge_base')->where('question', '=', $trimmedQuestion)->limit(1);
            if (!$query->documents()->isEmpty()) {
                Log::info("KB entry already exists for exact question: '{$trimmedQuestion}'");
                return false;
            }

            $data = [
                'question' => $trimmedQuestion,
                'answer' => trim($answer),
                'keywords' => $this->extractKeywords($trimmedQuestion), // <- PENTING
                'source' => 'openai_fallback',
                'created_at' => FieldValue::serverTimestamp(),
                'category' => $this->categorizeQuestion($trimmedQuestion),
            ];

            $this->db->collection('knowledge_base')->add($data);
            Log::info("✅ New KB entry added for: '{$trimmedQuestion}'");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to add KB entry: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // FUNGSI HELPER DAN FUNGSI LAIN (TETAP SAMA / SEDIKIT PENYESUAIAN)
    // =========================================================================

    /**
     * FUNGSI BARU: Helper untuk ekstraksi kata kunci.
     * Mengubah teks menjadi array kata kunci bersih.
     *
     * @param string $text
     * @return array
     */
    private function extractKeywords(string $text): array
    {
        $text = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text));

        // Daftar stopwords Bahasa Indonesia yang akan diabaikan
        $stopwords = ['di', 'ke', 'dari', 'yang', 'dan', 'atau', 'tapi', 'adalah', 'yaitu', 'dengan', 'ini', 'itu', 'saya', 'kamu', 'dia', 'apa', 'siapa', 'kapan', 'dimana', 'bagaimana', 'mengapa', 'tolong', 'jelaskan', 'tentang'];

        $keywords = array_filter(explode(' ', $text));
        $keywords = array_diff($keywords, $stopwords);

        return array_values(array_unique($keywords));
    }

    public function addChatLog(string $sessionId, string $question, string $answer, string $source, $userId = null): bool
    {
        if (!$this->isConnected()) return false;
        try {
            $this->db->collection('chat_logs')->add([
                'session_id' => $sessionId,
                'question' => $question,
                'answer' => $answer,
                'source' => $source,
                'timestamp' => FieldValue::serverTimestamp(),
                'user_id' => $userId,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to save chat log: " . $e->getMessage());
            return false;
        }
    }

    public function addErrorLog(string $errorMessage, string $userMessage = '', array $context = []): bool
    {
        if (!$this->isConnected()) return false;
        try {
            $this->db->collection('error_logs')->add([
                'error_message' => $errorMessage,
                'user_message' => $userMessage,
                'timestamp' => FieldValue::serverTimestamp(),
                'context' => $context,
                'user_agent' => request()->header('User-Agent', ''),
                'ip_address' => request()->ip(),
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to save error log: " . $e->getMessage());
            return false;
        }
    }

    public function updateSystemMetrics(string $source): bool
    {
        if (!$this->isConnected()) return false;
        try {
            $today = date('Y-m-d');
            $docRef = $this->db->collection('system_metrics')->document($today);

            // Gunakan transaction untuk update yang aman
            $this->db->runTransaction(function ($transaction) use ($docRef, $source) {
                $snapshot = $transaction->snapshot($docRef);
                $updates = [['path' => 'total_queries', 'op' => 'increment', 'value' => 1]];

                $sourceMap = [
                    'dialogflow' => 'dialogflow_success',
                    'firestore_kb' => 'firestore_success', // ganti nama source agar lebih jelas
                    'openai_fallback' => 'openai_fallback',
                ];

                if (isset($sourceMap[$source])) {
                    $updates[] = ['path' => $sourceMap[$source], 'op' => 'increment', 'value' => 1];
                }

                if ($snapshot->exists()) {
                    $transaction->update($docRef, $updates);
                } else {
                    $transaction->set($docRef, [
                        'date' => $today,
                        'total_queries' => 1,
                        'dialogflow_success' => $source === 'dialogflow' ? 1 : 0,
                        'firestore_success' => $source === 'firestore_kb' ? 1 : 0,
                        'openai_fallback' => $source === 'openai_fallback' ? 1 : 0,
                        'created_at' => FieldValue::serverTimestamp(),
                    ]);
                }
            });
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update system metrics: " . $e->getMessage());
            return false;
        }
    }

    private function categorizeQuestion(string $question): string
    {
        $question = strtolower($question);
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
                if (strpos($question, $keyword) !== false) return $category;
            }
        }
        return 'umum';
    }

    // Fungsi getStatistics Anda sudah bagus dan tidak perlu diubah.
    // public function getStatistics(): array
    // { /* ... kode Anda di sini ... */
    // }
}
