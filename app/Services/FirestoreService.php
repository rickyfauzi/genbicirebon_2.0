<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\FieldValue;
use Illuminate\Support\Facades\Log;

class FirestoreService
{
    protected $db;
    protected $isConnected = false;

    public function __construct()
    {
        try {
            $credentialsPath = storage_path('app/credentials/firestore-credentials2.json');
            $projectId = env('FIREBASE_PROJECT_ID');

            if (!file_exists($credentialsPath)) {
                throw new \Exception("❌ Firebase credentials file not found: {$credentialsPath}");
            }
            if (empty($projectId)) {
                throw new \Exception("❌ FIREBASE_PROJECT_ID is missing in .env");
            }

            $this->db = new FirestoreClient([
                'keyFilePath' => $credentialsPath,
                'projectId'   => $projectId,
            ]);

            $this->isConnected = true;
            Log::info("✅ Firestore connected (Project: {$projectId})");
        } catch (\Exception $e) {
            Log::critical("❌ Firestore init failed: " . $e->getMessage());
            $this->db = null;
            $this->isConnected = false;
        }
    }

    public function isConnected(): bool
    {
        return $this->isConnected && $this->db !== null;
    }

    /**
     * Test koneksi + tulis dummy data untuk verifikasi
     */
    public function testConnection(): array
    {
        if (!$this->isConnected()) {
            return ["status" => "error", "message" => "Firestore not connected"];
        }

        try {
            $docRef = $this->db->collection("testdata")->add([
                "message"    => "Hello Firestore",
                "created_at" => FieldValue::serverTimestamp(),
            ]);

            $docSnap = $docRef->snapshot();
            if ($docSnap->exists()) {
                return ["status" => "success", "message" => "✅ Firestore connection OK & data test disimpan."];
            } else {
                return ["status" => "error", "message" => "❌ Write attempted but snapshot not found."];
            }
        } catch (\Exception $e) {
            Log::error("❌ Firestore testConnection error: " . $e->getMessage());
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    public function addKnowledgeBase(string $question, string $answer): bool
    {
        if (!$this->isConnected()) return false;

        try {
            $trimmedQuestion = trim($question);

            // Cek duplikasi
            $query = $this->db->collection('knowledge_base')
                ->where('question', '=', $trimmedQuestion)->limit(1);
            if (!$query->documents()->isEmpty()) {
                Log::info("⚠️ KB entry already exists for: {$trimmedQuestion}");
                return false;
            }

            $data = [
                'question'   => $trimmedQuestion,
                'answer'     => trim($answer),
                'keywords'   => $this->extractKeywords($trimmedQuestion),
                'source'     => 'openai_fallback',
                'created_at' => FieldValue::serverTimestamp(),
                'category'   => $this->categorizeQuestion($trimmedQuestion),
            ];

            $docRef = $this->db->collection('knowledge_base')->add($data);
            Log::info("✅ KB entry stored: {$trimmedQuestion} (Doc ID: {$docRef->id()})");
            return true;
        } catch (\Exception $e) {
            Log::error("❌ addKnowledgeBase error: " . $e->getMessage());
            return false;
        }
    }

    public function addChatLog(string $sessionId, string $question, string $answer, string $source, $userId = null): bool
    {
        if (!$this->isConnected()) return false;

        try {
            $docRef = $this->db->collection('chat_logs')->add([
                'session_id' => $sessionId,
                'question'   => $question,
                'answer'     => $answer,
                'source'     => $source,
                'timestamp'  => FieldValue::serverTimestamp(),
                'user_id'    => $userId,
            ]);

            Log::info("💬 Chat log saved: {$docRef->id()}");
            return true;
        } catch (\Exception $e) {
            Log::error("❌ addChatLog error: " . $e->getMessage());
            return false;
        }
    }

    private function extractKeywords(string $text): array
    {
        $text = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text));
        $stopwords = ['di', 'ke', 'dari', 'yang', 'dan', 'atau', 'tapi', 'adalah', 'yaitu', 'dengan', 'ini', 'itu', 'apa', 'siapa', 'kapan', 'dimana', 'bagaimana', 'mengapa', 'tentang'];
        $keywords = array_filter(explode(' ', $text));
        return array_values(array_diff(array_unique($keywords), $stopwords));
    }

    private function categorizeQuestion(string $question): string
    {
        $q = strtolower($question);
        $categories = [
            'beasiswa' => ['beasiswa', 'scholarship', 'bantuan'],
            'kegiatan' => ['kegiatan', 'acara', 'event', 'program'],
            'pendaftaran' => ['daftar', 'registrasi', 'syarat', 'pendaftaran'],
            'informasi_umum' => ['apa', 'siapa', 'dimana', 'kapan', 'bagaimana'],
            'kontak' => ['kontak', 'alamat', 'telepon', 'email'],
            'sejarah' => ['sejarah', 'awal', 'didirikan', 'berdiri'],
        ];
        foreach ($categories as $cat => $words) {
            foreach ($words as $w) {
                if (strpos($q, $w) !== false) return $cat;
            }
        }
        return 'umum';
    }
}
