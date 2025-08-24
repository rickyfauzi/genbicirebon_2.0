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
            $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));
            $projectId       = env('FIREBASE_PROJECT_ID');

            if (!file_exists($credentialsPath)) {
                throw new \Exception("❌ Firebase credentials file not found: {$credentialsPath}");
            }

            if (empty($projectId)) {
                throw new \Exception("❌ FIREBASE_PROJECT_ID missing in .env");
            }

            $this->db = new FirestoreClient([
                'keyFilePath' => $credentialsPath,
                'projectId'   => $projectId,
            ]);

            $this->isConnected = true;
            Log::info("✅ Firestore connected: {$projectId}");
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

            return [
                "status"  => "success",
                "message" => "✅ Firestore connection OK & test data saved (Doc ID: {$docRef->id()})"
            ];
        } catch (\Exception $e) {
            Log::error("❌ Firestore testConnection error: " . $e->getMessage());
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    /**
     * Cari jawaban dari knowledge base Firestore
     */
    public function searchKnowledgeBase(string $question): ?string
    {
        if (!$this->isConnected()) return null;

        try {
            $trimmedQuestion = trim(strtolower($question));

            $query = $this->db->collection('knowledge_base')
                ->where('question', '=', $trimmedQuestion)
                ->limit(1)
                ->documents();

            if ($query->isEmpty()) {
                // 🔍 Coba cari berdasarkan keywords
                $keywords = $this->extractKeywords($trimmedQuestion);
                if (!empty($keywords)) {
                    $query = $this->db->collection('knowledge_base')
                        ->where('keywords', 'array-contains-any', $keywords)
                        ->limit(1)
                        ->documents();
                }
            }

            foreach ($query as $doc) {
                if ($doc->exists()) {
                    Log::info("✅ Knowledge base hit: {$doc->id()}");
                    return $doc->data()['answer'] ?? null;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error("❌ searchKnowledgeBase error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tambahkan entry baru ke knowledge base
     */
    public function addKnowledgeBase(string $question, string $answer): bool
    {
        if (!$this->isConnected()) return false;

        try {
            $trimmedQuestion = trim(strtolower($question));

            // Cek duplikasi
            $query = $this->db->collection('knowledge_base')
                ->where('question', '=', $trimmedQuestion)
                ->limit(1)
                ->documents();

            if (!$query->isEmpty()) {
                Log::info("⚠️ KB already exists: {$trimmedQuestion}");
                return false;
            }

            $docRef = $this->db->collection('knowledge_base')->add([
                'question'   => $trimmedQuestion,
                'answer'     => trim($answer),
                'keywords'   => $this->extractKeywords($trimmedQuestion),
                'source'     => 'openai_fallback',
                'created_at' => FieldValue::serverTimestamp(),
                'category'   => $this->categorizeQuestion($trimmedQuestion),
            ]);

            Log::info("✅ KB entry stored: {$trimmedQuestion} (Doc: {$docRef->id()})");
            return true;
        } catch (\Exception $e) {
            Log::error("❌ addKnowledgeBase error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Simpan log percakapan
     */
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

    /**
     * Ekstrak kata kunci dari pertanyaan
     */
    private function extractKeywords(string $text): array
    {
        $text = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text));
        $stopwords = [
            'di',
            'ke',
            'dari',
            'yang',
            'dan',
            'atau',
            'tapi',
            'adalah',
            'yaitu',
            'dengan',
            'ini',
            'itu',
            'apa',
            'siapa',
            'kapan',
            'dimana',
            'bagaimana',
            'mengapa',
            'tentang'
        ];
        $keywords = array_filter(explode(' ', $text));
        return array_values(array_diff(array_unique($keywords), $stopwords));
    }

    /**
     * Kategorisasi pertanyaan
     */
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
